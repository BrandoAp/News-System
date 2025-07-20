<?php
require_once __DIR__ . '/../../db/DatabaseManager.php';
require_once __DIR__ . '/../../db/ConexionDB.php';

class ImagenManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function procesarImagenesNoticia($files, $id_noticia) {
        $directorio = __DIR__ . '/../../public/uploads/noticias/';
        $resultados = [
            'success' => true,
            'message' => '',
            'errors' => []
        ];

        // Crear directorio si no existe
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }

        // Verificar si hay archivos para procesar
        if (empty($files['imagenes']['name'][0])) {
            return $resultados;
        }

        $nombresArchivos = $files['imagenes']['name'];
        $temporalArchivos = $files['imagenes']['tmp_name'];
        $tiposArchivos = $files['imagenes']['type'];
        $erroresArchivos = $files['imagenes']['error'];

        // Extensiones y tipos MIME permitidos
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

        // Verificar si el primer archivo debe ser principal
        $esPrimera = true;
        
        foreach ($nombresArchivos as $indice => $nombreArchivo) {
            // Saltar si no hay archivo
            if (empty($nombreArchivo) || $erroresArchivos[$indice] !== 0) {
                continue;
            }

            // Validar extensión
            $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
            $tipoArchivo = $tiposArchivos[$indice];

            if (!in_array($extension, $extensionesPermitidas) || !in_array($tipoArchivo, $tiposPermitidos)) {
                $resultados['errors'][] = "Tipo de archivo no permitido para: $nombreArchivo";
                continue;
            }

            // Crear nombre único para evitar conflictos
            $nombreArchivoSanitizado = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($nombreArchivo));
            $rutaDestino = $directorio . $nombreArchivoSanitizado;
            
            // Ruta pública relativa desde la raíz del proyecto
            $rutaPublica = '/News-System/public/uploads/noticias/' . $nombreArchivoSanitizado;

            // Intentar mover el archivo
            if (move_uploaded_file($temporalArchivos[$indice], $rutaDestino)) {
                try {
                    // Datos para insertar en la base de datos
                    $datosImagen = [
                        'id_noticia' => $id_noticia,
                        'url_thumbnail' => $rutaPublica,
                        'url_grande' => $rutaPublica,
                        'descripcion' => null,
                        'es_principal' => $esPrimera ? 1 : 0 // Primera imagen es principal
                    ];
                    
                    $this->db->insertSeguro('imagenes', $datosImagen);
                    $esPrimera = false; // Solo la primera imagen es principal
                    
                } catch (Exception $e) {
                    $resultados['errors'][] = "Error al guardar en BD: $nombreArchivo - " . $e->getMessage();
                    // Eliminar archivo si no se pudo guardar en BD
                    if (file_exists($rutaDestino)) {
                        unlink($rutaDestino);
                    }
                }
            } else {
                $resultados['errors'][] = "Error al subir la imagen: $nombreArchivo";
            }
        }

        // Preparar mensaje de resultado
        if (empty($resultados['errors'])) {
            $resultados['message'] = "Todas las imágenes se subieron correctamente.";
        } else {
            $resultados['success'] = false;
            $resultados['message'] = "Algunas imágenes presentaron errores.";
        }

        return $resultados;
    }

    /**
     * Obtiene todas las imágenes de una noticia específica
     */
    public function obtenerImagenesNoticia($id_noticia) {
        try {
            return $this->db->select('imagenes', '*', ['id_noticia' => $id_noticia], 'es_principal DESC, id ASC');
        } catch (Exception $e) {
            error_log("Error al obtener imágenes de noticia: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Elimina una imagen específica
     */
    public function eliminarImagen($id_imagen) {
        try {
            // Obtener datos de la imagen antes de eliminar
            $imagen = $this->db->select('imagenes', '*', ['id' => $id_imagen]);
            
            if (!empty($imagen)) {
                $rutaArchivo = __DIR__ . '/../../public' . str_replace('/News-System/public', '', $imagen[0]['url_grande']);
                
                // Eliminar archivo físico si existe
                if (file_exists($rutaArchivo)) {
                    unlink($rutaArchivo);
                }
                
                // Eliminar registro de la base de datos
                return $this->db->delete('imagenes', ['id' => $id_imagen]);
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error al eliminar imagen: " . $e->getMessage());
            return false;
        }
    }
}