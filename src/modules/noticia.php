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

// --- Lógica de Modo Edición (GET) ---
if (isset($_GET['id'])) {
    $id_noticia = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id_noticia) {
        $datos_noticia = $noticiasController->obtenerNoticiaPorId($id_noticia);
        if ($datos_noticia) {
            $modo_edicion = true;
            $noticia = $datos_noticia;
            
            // Usar el controlador público para obtener imágenes
            $imagenes_noticia = $publicController->obtenerImagenesDeNoticia($id_noticia);
            
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

    if (empty($errores)) {
        try {
            // Obtener IDs de estados
            $id_estado_publicado = $noticiasController->obtenerIdEstadoPorNombre('publicado');
            $id_estado_archivado = 4;

            // Procesar las 4 imágenes específicas DIRECTAMENTE
            $imagenes_procesadas = [];
            
            // Mapear los nombres de los campos a las columnas de la base de datos
            $campos_imagenes = [
                'imagen_grande' => 'url_grande',
                'imagen_thumb_original' => 'url_thumbnail',
                'imagen_thumb1' => 'url_thumbnail_1', 
                'imagen_thumb2' => 'url_thumbnail_2'
            ];
            
            // Validar y preparar cada imagen
            foreach ($campos_imagenes as $campo_form => $columna_db) {
                if (isset($_FILES[$campo_form]) && $_FILES[$campo_form]['error'] === UPLOAD_ERR_OK) {
                    // Validar tipo de archivo
                    $info_archivo = finfo_open(FILEINFO_MIME_TYPE);
                    $tipo_mime = finfo_file($info_archivo, $_FILES[$campo_form]['tmp_name']);
                    finfo_close($info_archivo);
                    
                    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!in_array($tipo_mime, $tipos_permitidos)) {
                        $errores['imagenes'] = "El archivo {$campo_form} no es una imagen válida.";
                        continue;
                    }
                    
                    // Validar tamaño (5MB máximo)
                    if ($_FILES[$campo_form]['size'] > 5 * 1024 * 1024) {
                        $errores['imagenes'] = "La imagen {$campo_form} es demasiado grande (máximo 5MB).";
                        continue;
                    }
                    
                    // Generar nombre único para el archivo
                    $extension = pathinfo($_FILES[$campo_form]['name'], PATHINFO_EXTENSION);
                    $nombre_archivo = uniqid() . '_' . time() . '.' . $extension;
                    
                    // Preparar ruta de destino
                    $ruta_destino = '../uploads/imagenes/' . $nombre_archivo;
                    
                    // Crear directorio si no existe
                    if (!file_exists('../uploads/imagenes/')) {
                        mkdir('../uploads/imagenes/', 0755, true);
                    }
                    
                    // Mover archivo
                    if (move_uploaded_file($_FILES[$campo_form]['tmp_name'], $ruta_destino)) {
                        // Guardar solo el nombre del archivo (no la URL completa)
                        $imagenes_procesadas[$columna_db] = $nombre_archivo;
                        error_log("Imagen procesada: {$campo_form} -> {$columna_db} = {$nombre_archivo}");
                    } else {
                        $errores['imagenes'] = "Error al subir la imagen {$campo_form}.";
                    }
                }
            }

            // Solo proceder si no hay errores con las imágenes
            if (empty($errores)) {
                $datos_noticia = [
                    'titulo' => $titulo,
                    'autor' => $autor,
                    'resumen' => !empty($resumen) ? $resumen : null,
                    'contenido' => $contenido,
                    'id_usuario_creador' => 1,
                    'id_categoria' => $id_categoria,
                    'id_estado' => $publicar_ahora ? $id_estado_publicado : $id_estado_archivado,
                ];

                // Agregar las imágenes directamente a los datos de la noticia
                if (!empty($imagenes_procesadas)) {
                    $datos_noticia = array_merge($datos_noticia, $imagenes_procesadas);
                    error_log("Datos con imágenes: " . print_r($datos_noticia, true));
                }

                if ($id_noticia) {
                    // Actualizar noticia existente - NO usar ImagenManager
                    $resultado = $noticiasController->actualizarNoticia($id_noticia, $datos_noticia);
                    if ($resultado) {
                        $noticia_id = $id_noticia;
                        $mensaje_estado = $publicar_ahora ? 'publicada' : 'guardada como archivada';
                        $_SESSION['mensaje_exito'] = "Noticia actualizada y {$mensaje_estado} con éxito.";
                    } else {
                        throw new Exception('No se pudo actualizar la noticia.');
                    }
                } else {
                    // Crear nueva noticia - NO usar ImagenManager
                    $datos_noticia['publicado_en'] = $publicar_ahora ? date('Y-m-d H:i:s') : null;
                    $noticia_id = $noticiasController->guardarNoticia($datos_noticia);
                    if ($noticia_id) {
                        $mensaje_estado = $publicar_ahora ? 'creada y publicada' : 'creada y archivada';
                        $_SESSION['mensaje_exito'] = "Noticia {$mensaje_estado} con éxito.";
                    } else {
                        throw new Exception('No se pudo crear la noticia.');
                    }
                }

                header('Location: indexnoticia.php');
                exit();
            }

        } catch (Exception $e) {
            error_log("Error en gestionar_noticia.php: " . $e->getMessage());
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
?>