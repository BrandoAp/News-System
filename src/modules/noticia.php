<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- Dependencias ---
require_once '../db/conexionDB.php';
require_once '../db/DatabaseManager.php';
require_once '../src/controllers/NoticiasController.php';
require_once '../src/modules/ImagenManager.php';
require_once '../src/controllers/pagina_publica_controller.php';

$pdo = ConexionDB::obtenerInstancia()->obtenerConexion();
$dbManager = new DatabaseManager($pdo);
$noticiasController = new NoticiasController($pdo);
$imagenManager = new ImagenManager($dbManager);
$publicController = new PaginaPublicaController($pdo);

// --- Inicialización ---
$modo_edicion = false;
$noticia = [
    'id' => null, 
    'titulo' => '', 
    'autor' => '', 
    'resumen' => '', 
    'contenido' => '', 
    'id_categoria' => 1,
    'imagenes' => []
];
$errores = [];

// Obtener categorías para el selector
$categorias = $noticiasController->obtenerCategorias();
$es_editor = false;
if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'editor') {
    $es_editor = true;
}

// --- Lógica de Modo Edición (GET) ---
if (isset($_GET['id'])) {
    $id_noticia = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id_noticia) {
        $datos_noticia = $noticiasController->obtenerNoticiaPorId($id_noticia);
        if ($datos_noticia) {
            $modo_edicion = true;
            $noticia = $datos_noticia;
            
            // CORREGIDO: Usar método correcto del controller
            $imagenes_noticia = $noticiasController->verificarImagenesDeNoticiaReal($id_noticia);
            
        } else {
            $_SESSION['mensaje_error'] = 'La noticia que intentas editar no existe.';
            header('Location: indexnoticia.php');
            exit();
        }
    } else {
        $_SESSION['mensaje_error'] = 'ID de noticia no válido.';
        header('Location: indexnoticia.php');
        exit();
    }
}

// --- Lógica de Procesamiento de Formulario (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y sanear datos
    $id_noticia = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
    $autor = filter_input(INPUT_POST, 'autor', FILTER_SANITIZE_SPECIAL_CHARS);
    $resumen = filter_input(INPUT_POST, 'resumen', FILTER_SANITIZE_SPECIAL_CHARS);
    $contenido = filter_input(INPUT_POST, 'contenido', FILTER_SANITIZE_SPECIAL_CHARS);
    $id_categoria = filter_input(INPUT_POST, 'id_categoria', FILTER_VALIDATE_INT);
    $publicar_ahora = isset($_POST['publicar_ahora']);

    // Validación básica
    if (empty($titulo)) $errores['titulo'] = 'El título es obligatorio.';
    if (empty($autor)) $errores['autor'] = 'El autor es obligatorio.';
    if (empty($contenido)) $errores['contenido'] = 'El contenido es obligatorio.';
    if (!$id_categoria || $id_categoria <= 0) $errores['categoria'] = 'Debes seleccionar una categoría válida.';

    // Validación del resumen
    if (!empty($resumen)) {
        if (strlen($resumen) > 250) {
            $errores['resumen'] = 'El resumen no puede exceder los 250 caracteres.';
        }
        if (strlen($resumen) < 10) {
            $errores['resumen'] = 'El resumen debe tener al menos 10 caracteres.';
        }
    }

    // Validar categoría existente
    if ($id_categoria) {
        $categoria_valida = false;
        foreach ($categorias as $cat) {
            if ($cat['id'] == $id_categoria) {
                $categoria_valida = true;
                break;
            }
        }
        if (!$categoria_valida) {
            $errores['categoria'] = 'La categoría seleccionada no es válida.';
        }
    }

    // Asegurar que $imagenes_noticia esté definida
    if (!isset($imagenes_noticia) || !is_array($imagenes_noticia)) {
        $imagenes_noticia = [];
    }

    // Validar imágenes requeridas
    $errores_imagenes = validarImagenesRequeridas($modo_edicion, $imagenes_noticia);
    $errores = array_merge($errores, $errores_imagenes);

    if (empty($errores)) {
        try {
            // Obtener IDs de estados
            $id_estado_publicado = $noticiasController->obtenerIdEstadoPorNombre('publicado');
            $id_estado_archivado = 4;

            // CORREGIDO: Procesar archivos según estructura real
            $imagenes_procesadas = [];
            
            // Campos de imagen que coinciden exactamente con el formulario Y la tabla real
            $camposImagenes = ['url_grande', 'url_thumbnail', 'url_thumbnail_1', 'url_thumbnail_2'];
            
            foreach ($camposImagenes as $campo) {
                if (isset($_FILES[$campo]) && $_FILES[$campo]['error'] === UPLOAD_ERR_OK) {
                    error_log("Procesando archivo: $campo");
                    
                    $archivo = $_FILES[$campo];
                    $nombre_archivo = $archivo['name'];
                    $archivo_temporal = $archivo['tmp_name'];
                    $tamaño = $archivo['size'];
                    
                    // Validaciones del archivo
                    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
                    
                    if (!in_array($extension, $extensiones_permitidas)) {
                        $errores['general'] = "Formato de imagen no permitido para $campo. Use: " . implode(', ', $extensiones_permitidas);
                        continue;
                    }
                    
                    if ($tamaño > 5 * 1024 * 1024) { // 5MB máximo
                        $errores['general'] = "La imagen $campo es demasiado grande. Máximo 5MB.";
                        continue;
                    }
                    
                    // Crear directorio si no existe
                    $directorio_subida = '../public/uploads/noticias/';
                    if (!is_dir($directorio_subida)) {
                        if (!mkdir($directorio_subida, 0755, true)) {
                            $errores['general'] = "No se pudo crear el directorio de subida.";
                            continue;
                        }
                    }
                    
                    // Generar nombre único para el archivo
                    $nombre_unico = uniqid() . '_' . time() . '.' . $extension;
                    $ruta_destino = $directorio_subida . $nombre_unico;
                    
                    // Mover archivo
                    if (move_uploaded_file($archivo_temporal, $ruta_destino)) {
                        $imagenes_procesadas[$campo] = $nombre_unico;
                        error_log("Archivo $campo subido como: $nombre_unico");
                    } else {
                        $errores['general'] = "Error al subir la imagen $campo.";
                        error_log("Error al mover archivo $campo");
                    }
                } else {
                    error_log("No se subió archivo para $campo o hubo error: " . ($_FILES[$campo]['error'] ?? 'N/A'));
                }
            }

            // Solo proceder si no hay errores con las imágenes
            if (empty($errores)) {
                $datos_noticia = [
                    'titulo' => $titulo,
                    'autor' => $autor,
                    'resumen' => !empty($resumen) ? $resumen : null,
                    'contenido' => $contenido,
                    'id_usuario_creador' => $_SESSION['usuario_id'],
                    'id_categoria' => $id_categoria,
                    'id_estado' => $publicar_ahora ? $id_estado_publicado : $id_estado_archivado,
                    'publicado_en' => $publicar_ahora ? date('Y-m-d H:i:s') : null
                ];

                // CORREGIDO: Agregar las imágenes directamente con nombres correctos
                if (!empty($imagenes_procesadas)) {
                    $datos_noticia = array_merge($datos_noticia, $imagenes_procesadas);
                    error_log("Datos finales con imágenes: " . print_r($datos_noticia, true));
                }

                if ($id_noticia) {
                    // ACTUALIZAR - usar método correcto
                    $resultado = $noticiasController->actualizarNoticiaCompleta($id_noticia, $datos_noticia);
                    if ($resultado) {
                        $mensaje_estado = $publicar_ahora ? 'publicada' : 'guardada como archivada';
                        $_SESSION['mensaje_exito'] = "Noticia actualizada y {$mensaje_estado} con éxito.";
                        header('Location: indexnoticia.php');
                        exit();
                    } else {
                        throw new Exception('No se pudo actualizar la noticia.');
                    }
                } else {
                    // CREAR - usar método correcto
                    $resultado = $noticiasController->guardarNoticiaCompleta($datos_noticia);
                    if ($resultado && $resultado > 0) {
                        $_SESSION['mensaje_exito'] = 'Noticia creada exitosamente.';
                        header("Location: indexnoticia.php");
                        exit();
                    } else {
                        $errores['general'] = 'Error al crear la noticia. Verifique los datos e intente nuevamente.';
                    }
                }
            }

        } catch (Exception $e) {
            error_log("Error en noticia.php: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $errores['general'] = 'Ocurrió un error al guardar la noticia. Por favor, inténtalo nuevamente.';
        }
    }

    // Repoblar formulario si hay errores
    $noticia['titulo'] = $titulo;
    $noticia['autor'] = $autor;
    $noticia['resumen'] = $resumen;
    $noticia['contenido'] = $contenido;
    $noticia['id_categoria'] = $id_categoria;
}

function validarImagenesRequeridas($modo_edicion, $imagenes_noticia) {
    $errores = [];

    // Asegura que $imagenes_noticia sea siempre un array
    if (!is_array($imagenes_noticia)) {
        $imagenes_noticia = [];
    }

    $existe_grande = $modo_edicion && !empty($imagenes_noticia[0]['url_grande']);
    $existe_thumb   = $modo_edicion && !empty($imagenes_noticia[0]['url_thumbnail']);
    $existe_thumb1  = $modo_edicion && !empty($imagenes_noticia[0]['url_thumbnail_1']);
    $existe_thumb2  = $modo_edicion && !empty($imagenes_noticia[0]['url_thumbnail_2']);

    if (
        (empty($_FILES['url_grande']['name']) && !$existe_grande) ||
        (empty($_FILES['url_thumbnail']['name']) && !$existe_thumb) ||
        (empty($_FILES['url_thumbnail_1']['name']) && !$existe_thumb1) ||
        (empty($_FILES['url_thumbnail_2']['name']) && !$existe_thumb2)
    ) {
        $errores['imagenes'] = 'Debes cargar todas las imágenes requeridas para la noticia.';
    }

    return $errores;
}
?>