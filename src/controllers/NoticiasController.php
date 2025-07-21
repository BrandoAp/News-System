<?php
require_once __DIR__ . '../../../db/DatabaseManager.php';
require_once __DIR__ . '../../modules/ImagenManager.php';

class NoticiasController
{
    private $db;
    private $conexion;
    private $imagenManager;

    public function __construct(PDO $pdoConnection)
    {
        $this->conexion = $pdoConnection;
        $this->db = new DatabaseManager($pdoConnection);
        $this->imagenManager = new ImagenManager($this->db);
    }

    /**
     * Obtiene una lista paginada de noticias, permitiendo filtrar por búsqueda, categoría y tipo de búsqueda.
     */
    public function obtenerNoticias(int $pagina = 1, int $porPagina = 3, ?string $busqueda = null, ?int $idCategoria = null, ?string $tipoBusqueda = null): array
    {
        $offset = ($pagina - 1) * $porPagina;
        
        $sql = "
            SELECT 
                n.*, 
                c.nombre as categoria_nombre,
                COALESCE(e.nombre, 'sin_estado') as estado_publicacion,
                (SELECT i.url_grande FROM imagenes i WHERE i.id_noticia = n.id AND i.es_principal = 1 LIMIT 1) as imagen_portada,
                (SELECT COUNT(*) FROM imagenes i WHERE i.id_noticia = n.id) as total_imagenes
            FROM noticias n
            LEFT JOIN estados e ON n.id_estado = e.id
            LEFT JOIN categorias c ON n.id_categoria = c.id
        ";

        $params = [];
        $condiciones = [];

        // Filtro por búsqueda de texto según el tipo
        if ($busqueda && trim($busqueda) !== '') {
            switch ($tipoBusqueda) {
                case 'categoria':
                    $condiciones[] = "c.nombre LIKE :busqueda";
                    break;
                case 'autor':
                    $condiciones[] = "n.autor LIKE :busqueda";
                    break;
                default:
                    // Búsqueda general (título, contenido, autor)
                    $condiciones[] = "(n.titulo LIKE :busqueda OR n.contenido LIKE :busqueda OR n.autor LIKE :busqueda)";
                    break;
            }
            $params[':busqueda'] = '%' . trim($busqueda) . '%';
        }

        // Filtro por categoría (solo si no estamos buscando por categoría en texto)
        if ($idCategoria && $idCategoria > 0 && $tipoBusqueda !== 'categoria') {
            $condiciones[] = "n.id_categoria = :id_categoria";
            $params[':id_categoria'] = $idCategoria;
        }

        // Agregar condiciones WHERE si existen
        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY n.publicado_en DESC LIMIT $porPagina OFFSET $offset";

        try {
            $resultado = $this->db->query($sql, $params);
            
            error_log("SQL ejecutado: " . $sql);
            error_log("Parámetros: " . print_r($params, true));
            error_log("Resultados obtenidos: " . count($resultado));
            
            return $resultado;
        } catch (Exception $e) {
            error_log("Error en obtenerNoticias: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cuenta el total de noticias, permitiendo filtrar por búsqueda, categoría y tipo de búsqueda.
     */
    public function contarNoticias(?string $busqueda = null, ?int $idCategoria = null, ?string $tipoBusqueda = null): int
    {
        $sql = "SELECT COUNT(n.id) as total FROM noticias n LEFT JOIN categorias c ON n.id_categoria = c.id";
        $params = [];
        $condiciones = [];

        // Filtro por búsqueda de texto según el tipo
        if ($busqueda && trim($busqueda) !== '') {
            switch ($tipoBusqueda) {
                case 'categoria':
                    $condiciones[] = "c.nombre LIKE :busqueda";
                    break;
                case 'autor':
                    $condiciones[] = "n.autor LIKE :busqueda";
                    break;
                default:
                    // Búsqueda general (título, contenido, autor)
                    $condiciones[] = "(n.titulo LIKE :busqueda OR n.contenido LIKE :busqueda OR n.autor LIKE :busqueda)";
                    break;
            }
            $params[':busqueda'] = '%' . trim($busqueda) . '%';
        }

        // Filtro por categoría (solo si no estamos buscando por categoría en texto)
        if ($idCategoria && $idCategoria > 0 && $tipoBusqueda !== 'categoria') {
            $condiciones[] = "n.id_categoria = :id_categoria";
            $params[':id_categoria'] = $idCategoria;
        }

        // Agregar condiciones WHERE si existen
        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        try {
            $resultado = $this->db->scalar($sql, $params);
            return (int) $resultado;
        } catch (Exception $e) {
            error_log("Error en contarNoticias: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene todas las categorías activas para el filtro desplegable.
     */
    public function obtenerCategorias(): array
    {
        try {
            return $this->db->select('categorias', 'id, nombre', ['id_estado' => 1]);
        } catch (Exception $e) {
            error_log("Error en obtenerCategorias: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todos los autores únicos de las noticias para sugerencias.
     */
    public function obtenerAutores(): array
    {
        try {
            $sql = "SELECT DISTINCT autor FROM noticias WHERE autor IS NOT NULL AND autor != '' ORDER BY autor ASC";
            return $this->db->query($sql);
        } catch (Exception $e) {
            error_log("Error en obtenerAutores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una noticia específica por su ID junto con sus imágenes.
     */
    public function obtenerNoticiaPorId(int $id)
    {
        try {
            $noticia = $this->db->select('noticias', '*', ['id' => $id]);
            if (empty($noticia)) {
                return false;
            }
            
            $noticia = $noticia[0];
            
            // Obtener imágenes de la noticia usando el ImagenManager
            $noticia['imagenes'] = $this->imagenManager->obtenerImagenesNoticia($id);
            
            return $noticia;
        } catch (Exception $e) {
            error_log("Error en obtenerNoticiaPorId: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Busca el ID de un estado por su nombre.
     */
    public function obtenerIdEstadoPorNombre(string $nombreEstado): int
    {
        try {
            // Mapeo directo según tu BD
            $mapaEstados = [
                'activo' => 1,
                'inactivo' => 2,  
                'publicado' => 3,
                'archivado' => 4
            ];
            
            if (isset($mapaEstados[$nombreEstado])) {
                error_log("Estado '$nombreEstado' mapeado a ID: " . $mapaEstados[$nombreEstado]);
                return $mapaEstados[$nombreEstado];
            }
            
            // Fallback: buscar en BD
            $resultado = $this->db->select('estados', 'id', ['nombre' => $nombreEstado]);
            if (!empty($resultado)) {
                return (int)$resultado[0]['id'];
            }
            
            // Fallback final
            error_log("Estado '$nombreEstado' no encontrado, usando fallback");
            return $nombreEstado === 'publicado' ? 3 : 1;
            
        } catch (Exception $e) {
            error_log("Error en obtenerIdEstadoPorNombre: " . $e->getMessage());
            return $nombreEstado === 'publicado' ? 3 : 1;
        }
    }

    /**
     * Guarda una nueva noticia y procesa sus imágenes
     */
    public function guardarNoticia($datos) {
        try {
            error_log("=== INICIO guardarNoticia en Controller ===");
            error_log("Datos completos recibidos: " . print_r($datos, true));
            
            // Separar datos de noticia de datos de imágenes
            $datosNoticia = [];
            $datosImagenes = [];
            
            // Campos específicos de la tabla noticias
            $camposNoticia = ['titulo', 'autor', 'resumen', 'contenido', 'id_usuario_creador', 'id_categoria', 'id_estado', 'publicado_en'];
            
            foreach ($camposNoticia as $campo) {
                if (isset($datos[$campo])) {
                    $datosNoticia[$campo] = $datos[$campo];
                }
            }
            
            // Agregar timestamps si no están presentes
            if (!isset($datosNoticia['creado_en'])) {
                $datosNoticia['creado_en'] = date('Y-m-d H:i:s');
            }
            if (!isset($datosNoticia['actualizado_en'])) {
                $datosNoticia['actualizado_en'] = date('Y-m-d H:i:s');
            }
            
            error_log("Datos de noticia preparados: " . print_r($datosNoticia, true));
            
            // Insertar la noticia usando el método existente del DatabaseManager
            $idNoticia = $this->db->insertSeguro('noticias', $datosNoticia);
            
            if (!$idNoticia) {
                throw new Exception("No se pudo insertar la noticia en la base de datos");
            }
            
            error_log("Noticia insertada con ID: $idNoticia");
            
            // Ahora procesar las imágenes
            $camposImagenes = ['url_grande', 'url_thumbnail', 'url_thumbnail_1', 'url_thumbnail_2'];
            
            foreach ($camposImagenes as $campo) {
                if (isset($datos[$campo]) && !empty($datos[$campo])) {
                    $datosImagenes[$campo] = $datos[$campo];
                }
            }
            
            error_log("Datos de imágenes encontrados: " . print_r($datosImagenes, true));
            
            // Insertar cada imagen como un registro separado
            if (!empty($datosImagenes)) {
                $tiposImagen = [
                    'url_grande' => ['descripcion' => 'Imagen principal', 'es_principal' => 1],
                    'url_thumbnail' => ['descripcion' => 'Thumbnail principal', 'es_principal' => 0],
                    'url_thumbnail_1' => ['descripcion' => 'Thumbnail 1', 'es_principal' => 0],
                    'url_thumbnail_2' => ['descripcion' => 'Thumbnail 2', 'es_principal' => 0]
                ];
                
                foreach ($datosImagenes as $campo => $nombreArchivo) {
                    if (!empty($nombreArchivo)) {
                        $registroImagen = [
                            'id_noticia' => $idNoticia,
                            'url' => $nombreArchivo,
                            'descripcion' => $tiposImagen[$campo]['descripcion'],
                            'es_principal' => $tiposImagen[$campo]['es_principal'],
                            'id_estado' => 1 // activo
                        ];
                        
                        error_log("Insertando imagen: " . print_r($registroImagen, true));
                        
                        $resultadoImagen = $this->db->insertSeguro('imagenes', $registroImagen);
                        
                        if ($resultadoImagen) {
                            error_log("Imagen insertada exitosamente con ID: $resultadoImagen - Archivo: $nombreArchivo");
                        } else {
                            error_log("ERROR: No se pudo insertar imagen: $nombreArchivo");
                        }
                    }
                }
            } else {
                error_log("No hay imágenes para insertar");
            }
            
            error_log("=== FIN guardarNoticia - ID retornado: $idNoticia ===");
            return $idNoticia;
            
        } catch (Exception $e) {
            error_log("EXCEPCIÓN en guardarNoticia: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Actualiza una noticia existente
     */
    public function actualizarNoticia($id, $datos) {
        try {
            error_log("=== INICIO actualizarNoticia en Controller ===");
            error_log("ID: $id, Datos: " . print_r($datos, true));
            
            // Verificar que la noticia existe
            $noticiaExiste = $this->db->select('noticias', 'id', ['id' => $id]);
            if (empty($noticiaExiste)) {
                error_log("ERROR: No se encontró noticia con ID: $id");
                return false;
            }
            
            // Separar datos de noticia de datos de imágenes
            $datosNoticia = [];
            $datosImagenes = [];
            
            // Campos específicos de la tabla noticias
            $camposNoticia = ['titulo', 'autor', 'resumen', 'contenido', 'id_categoria', 'id_estado', 'publicado_en'];
            
            foreach ($camposNoticia as $campo) {
                if (isset($datos[$campo])) {
                    $datosNoticia[$campo] = $datos[$campo];
                }
            }
            
            // Actualizar timestamp
            $datosNoticia['actualizado_en'] = date('Y-m-d H:i:s');
            
            error_log("Datos de noticia para actualizar: " . print_r($datosNoticia, true));
            
            // Actualizar la noticia usando updateSeguro
            $resultado = $this->db->updateSeguro('noticias', $datosNoticia, ['id' => $id]);
            
            if (!$resultado) {
                error_log("ERROR: updateSeguro falló para noticia ID: $id");
                throw new Exception("No se pudo actualizar la noticia");
            }
            
            error_log("✅ Noticia actualizada exitosamente");
            
            // Procesar imágenes si las hay
            $camposImagenes = ['url_grande', 'url_thumbnail', 'url_thumbnail_1', 'url_thumbnail_2'];
            
            foreach ($camposImagenes as $campo) {
                if (isset($datos[$campo]) && !empty($datos[$campo])) {
                    $datosImagenes[$campo] = $datos[$campo];
                }
            }
            
            error_log("Datos de imágenes para procesar: " . print_r($datosImagenes, true));
            
            // Si hay nuevas imágenes, procesarlas
            if (!empty($datosImagenes)) {
                error_log("Procesando " . count($datosImagenes) . " imágenes para noticia ID: $id");
                
                // Marcar imágenes existentes como inactivas
                $sqlInactivar = "UPDATE imagenes SET id_estado = 2 WHERE id_noticia = ?";
                $resultadoInactivar = $this->db->query($sqlInactivar, [$id]);
                
                if ($resultadoInactivar) {
                    error_log("✅ Imágenes existentes marcadas como inactivas");
                } else {
                    error_log("⚠️ WARNING: No se pudieron marcar las imágenes existentes como inactivas");
                }
                
                // Insertar nuevas imágenes
                $tiposImagen = [
                    'url_grande' => ['descripcion' => 'Imagen principal', 'es_principal' => 1],
                    'url_thumbnail' => ['descripcion' => 'Thumbnail principal', 'es_principal' => 0],
                    'url_thumbnail_1' => ['descripcion' => 'Thumbnail 1', 'es_principal' => 0],
                    'url_thumbnail_2' => ['descripcion' => 'Thumbnail 2', 'es_principal' => 0]
                ];
                
                $imagenesInsertadas = 0;
                $erroresImagenes = [];
                
                foreach ($datosImagenes as $campo => $nombreArchivo) {
                    if (!empty($nombreArchivo)) {
                        $registroImagen = [
                            'id_noticia' => $id,
                            'url' => $nombreArchivo,
                            'descripcion' => $tiposImagen[$campo]['descripcion'],
                            'es_principal' => $tiposImagen[$campo]['es_principal'],
                            'id_estado' => 1 // activo
                        ];
                        
                        error_log("Insertando imagen: " . print_r($registroImagen, true));
                        
                        // CAPTURAR EL RESULTADO DE insertSeguro
                        $idImagenInsertada = $this->db->insertSeguro('imagenes', $registroImagen);
                        
                        if ($idImagenInsertada && $idImagenInsertada > 0) {
                            $imagenesInsertadas++;
                            error_log("✅ Imagen insertada exitosamente con ID: $idImagenInsertada - Archivo: $nombreArchivo");
                        } else {
                            $erroresImagenes[] = $nombreArchivo;
                            error_log("❌ ERROR: No se pudo insertar imagen: $nombreArchivo");
                        }
                    }
                }
                
                error_log("Resumen de inserción de imágenes:");
                error_log("- Total intentadas: " . count($datosImagenes));
                error_log("- Exitosas: $imagenesInsertadas");
                error_log("- Fallidas: " . count($erroresImagenes));
                
                if (!empty($erroresImagenes)) {
                    error_log("- Archivos que fallaron: " . implode(', ', $erroresImagenes));
                }
                
                // Verificar estado final de las imágenes
                $imagenesFinales = $this->db->query("SELECT COUNT(*) as total FROM imagenes WHERE id_noticia = ? AND id_estado = 1", [$id]);
                $totalImagenesActivas = $imagenesFinales[0]['total'] ?? 0;
                error_log("Total de imágenes activas después de la actualización: $totalImagenesActivas");
                
            } else {
                error_log("No hay nuevas imágenes para procesar");
            }
            
            error_log("=== FIN actualizarNoticia - Éxito ===");
            return true;
            
        } catch (Exception $e) {
            error_log("EXCEPCIÓN en actualizarNoticia: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Método específico para obtener el último ID insertado
     */
    public function obtenerUltimoIdInsertado(): int
    {
        try {
            return (int) $this->conexion->lastInsertId();
        } catch (Exception $e) {
            error_log("Error al obtener último ID insertado: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Método para verificar si una imagen se asoció correctamente a una noticia
     */
    public function verificarImagenesDeNoticia(int $idNoticia): array
    {
        try {
            $sql = "SELECT * FROM imagenes WHERE id_noticia = ? AND id_estado = 1 ORDER BY es_principal DESC, id ASC";
            $imagenes = $this->db->query($sql, [$idNoticia]);
            
            error_log("Verificación de imágenes para noticia $idNoticia: " . count($imagenes) . " imágenes encontradas");
            
            return $imagenes;
        } catch (Exception $e) {
            error_log("Error al verificar imágenes de noticia $idNoticia: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Método para insertar una imagen individual y devolver su ID
     */
    public function insertarImagenNoticia(int $idNoticia, string $nombreArchivo, string $tipo = 'thumbnail', bool $esPrincipal = false): int|false
    {
        try {
            $registroImagen = [
                'id_noticia' => $idNoticia,
                'url' => $nombreArchivo,
                'descripcion' => $tipo,
                'es_principal' => $esPrincipal ? 1 : 0,
                'id_estado' => 1 // activo
            ];
            
            error_log("Insertando imagen individual: " . print_r($registroImagen, true));
            
            $idImagenInsertada = $this->db->insertSeguro('imagenes', $registroImagen);
            
            if ($idImagenInsertada && $idImagenInsertada > 0) {
                error_log("✅ Imagen individual insertada con ID: $idImagenInsertada");
                return $idImagenInsertada;
            } else {
                error_log("❌ Error al insertar imagen individual");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Excepción al insertar imagen individual: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Método mejorado para guardar noticia con mejor manejo de IDs
     */
    public function guardarNoticiaConImagenes($datos) {
        try {
            error_log("=== INICIO guardarNoticiaConImagenes ===");
            error_log("Datos recibidos: " . print_r($datos, true));
            
            // Separar datos de noticia de datos de imágenes
            $datosNoticia = [];
            $datosImagenes = [];
            
            // Campos específicos de la tabla noticias
            $camposNoticia = ['titulo', 'autor', 'resumen', 'contenido', 'id_usuario_creador', 'id_categoria', 'id_estado', 'publicado_en'];
            
            foreach ($camposNoticia as $campo) {
                if (isset($datos[$campo])) {
                    $datosNoticia[$campo] = $datos[$campo];
                }
            }
            
            // Agregar timestamps
            $datosNoticia['creado_en'] = date('Y-m-d H:i:s');
            $datosNoticia['actualizado_en'] = date('Y-m-d H:i:s');
            
            error_log("Datos de noticia preparados: " . print_r($datosNoticia, true));
            
            // Insertar la noticia primero
            $idNoticia = $this->db->insertSeguro('noticias', $datosNoticia);
            
            if (!$idNoticia || $idNoticia <= 0) {
                throw new Exception("No se pudo insertar la noticia en la base de datos");
            }
            
            error_log("✅ Noticia insertada con ID: $idNoticia");
            
            // Procesar imágenes
            $camposImagenes = ['url_grande', 'url_thumbnail', 'url_thumbnail_1', 'url_thumbnail_2'];
            $imagenesInsertadas = 0;
            $erroresImagenes = [];
            
            foreach ($camposImagenes as $campo) {
                if (isset($datos[$campo]) && !empty($datos[$campo])) {
                    $nombreArchivo = $datos[$campo];
                    $esPrincipal = ($campo === 'url_grande');
                    $tipo = match($campo) {
                        'url_grande' => 'Imagen principal',
                        'url_thumbnail' => 'Thumbnail principal',
                        'url_thumbnail_1' => 'Thumbnail 1',
                        'url_thumbnail_2' => 'Thumbnail 2',
                        default => 'Imagen'
                    };
                    
                    $idImagenInsertada = $this->insertarImagenNoticia($idNoticia, $nombreArchivo, $tipo, $esPrincipal);
                    
                    if ($idImagenInsertada) {
                        $imagenesInsertadas++;
                        error_log("✅ Imagen $campo asociada con ID: $idImagenInsertada");
                    } else {
                        $erroresImagenes[] = $campo;
                        error_log("❌ Error al asociar imagen $campo");
                    }
                }
            }
            
            error_log("Resumen final:");
            error_log("- Noticia ID: $idNoticia");
            error_log("- Imágenes insertadas: $imagenesInsertadas");
            error_log("- Errores en imágenes: " . count($erroresImagenes));
            
            // Verificar resultado final
            $imagenesFinales = $this->verificarImagenesDeNoticia($idNoticia);
            error_log("- Imágenes verificadas en BD: " . count($imagenesFinales));
            
            error_log("=== FIN guardarNoticiaConImagenes - ID: $idNoticia ===");
            return $idNoticia;
            
        } catch (Exception $e) {
            error_log("EXCEPCIÓN en guardarNoticiaConImagenes: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Método mejorado para insertar en cualquier tabla y devolver el ID
     */
    public function insertarConId(string $tabla, array $datos): int|false
    {
        try {
            error_log("=== INICIO insertarConId ===");
            error_log("Tabla: $tabla");
            error_log("Datos: " . print_r($datos, true));
            
            if (empty($datos)) {
                error_log("ERROR: No hay datos para insertar");
                return false;
            }
            
            $campos = array_keys($datos);
            $placeholders = array_map(function($campo) { return ":$campo"; }, $campos);
            
            $sql = "INSERT INTO $tabla (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $placeholders) . ")";
            
            error_log("SQL generado: $sql");
            
            $stmt = $this->conexion->prepare($sql);
            
            // Bind de parámetros
            foreach ($datos as $campo => $valor) {
                $stmt->bindValue(":$campo", $valor);
                error_log("Binding :$campo = $valor");
            }
            
            $resultado = $stmt->execute();
            
            if ($resultado) {
                $lastId = $this->conexion->lastInsertId();
                error_log("✅ insertarConId exitoso en tabla $tabla. ID generado: $lastId");
                return (int)$lastId;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("❌ insertarConId falló en tabla $tabla: " . implode(', ', $errorInfo));
                return false;
            }
            
        } catch (Exception $e) {
            error_log("EXCEPCIÓN en insertarConId: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Método específico para insertar noticia y devolver ID
     */
    public function insertarNoticia(array $datosNoticia): int|false
    {
        try {
            error_log("=== INICIO insertarNoticia ===");
            
            // Validar datos obligatorios
            $camposObligatorios = ['titulo', 'autor', 'contenido', 'id_categoria'];
            foreach ($camposObligatorios as $campo) {
                if (empty($datosNoticia[$campo])) {
                    error_log("ERROR: Campo obligatorio '$campo' está vacío");
                    return false;
                }
            }
            
            // Agregar valores por defecto si no están presentes
            if (!isset($datosNoticia['id_estado'])) {
                $datosNoticia['id_estado'] = 1; // activo
            }
            
            if (!isset($datosNoticia['id_usuario_creador'])) {
                $datosNoticia['id_usuario_creador'] = 1; // usuario por defecto
            }
            
            if (!isset($datosNoticia['creado_en'])) {
                $datosNoticia['creado_en'] = date('Y-m-d H:i:s');
            }
            
            if (!isset($datosNoticia['actualizado_en'])) {
                $datosNoticia['actualizado_en'] = date('Y-m-d H:i:s');
            }
            
            // Insertar usando el método personalizado
            $idNoticia = $this->insertarConId('noticias', $datosNoticia);
            
            if ($idNoticia && $idNoticia > 0) {
                error_log("✅ Noticia insertada exitosamente con ID: $idNoticia");
                return $idNoticia;
            } else {
                error_log("❌ Error al insertar noticia");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("EXCEPCIÓN en insertarNoticia: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Método corregido para insertar imagen según la estructura real de la tabla
     */
    public function insertarImagenReal(array $datosImagen): int|false
    {
        try {
            error_log("=== INICIO insertarImagenReal ===");
            error_log("Datos recibidos: " . print_r($datosImagen, true));
            
            // Validar que tenemos el id_noticia
            if (empty($datosImagen['id_noticia'])) {
                error_log("ERROR: id_noticia es obligatorio");
                return false;
            }
            
            // Preparar registro según la estructura REAL de la tabla
            $registroImagen = [
                'id_noticia' => $datosImagen['id_noticia'],
                'url_thumbnail' => $datosImagen['url_thumbnail'] ?? '',
                'url_thumbnail_1' => $datosImagen['url_thumbnail_1'] ?? '',
                'url_thumbnail_2' => $datosImagen['url_thumbnail_2'] ?? '',
                'url_grande' => $datosImagen['url_grande'] ?? '',
                'descripcion' => $datosImagen['descripcion'] ?? null,
                'es_principal' => $datosImagen['es_principal'] ?? 0
            ];
            
            error_log("Registro preparado para insertar: " . print_r($registroImagen, true));
            
            // Insertar usando el método personalizado
            $idImagen = $this->insertarConId('imagenes', $registroImagen);
            
            if ($idImagen && $idImagen > 0) {
                error_log("✅ Imagen insertada exitosamente con ID: $idImagen");
                return $idImagen;
            } else {
                error_log("❌ Error al insertar imagen");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("EXCEPCIÓN en insertarImagenReal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Método corregido para guardar noticia completa con estructura real
     * VERSIÓN ÚNICA - ELIMINA LA DUPLICACIÓN
     */
    public function guardarNoticiaCompleta($datos): int|false 
    {
        try {
            error_log("=== INICIO guardarNoticiaCompleta CORREGIDO ===");
            error_log("Datos recibidos: " . print_r($datos, true));
            
            // Separar datos de noticia de datos de imágenes
            $datosNoticia = [];
            
            // Campos específicos de la tabla noticias
            $camposNoticia = ['titulo', 'autor', 'resumen', 'contenido', 'id_usuario_creador', 'id_categoria', 'id_estado', 'publicado_en'];
            
            foreach ($camposNoticia as $campo) {
                if (isset($datos[$campo])) {
                    $datosNoticia[$campo] = $datos[$campo];
                }
            }
            
            error_log("Datos de noticia separados: " . print_r($datosNoticia, true));
            
            // 1. INSERTAR LA NOTICIA PRIMERO
            $idNoticia = $this->insertarNoticia($datosNoticia);
            
            if (!$idNoticia || $idNoticia <= 0) {
                throw new Exception("No se pudo insertar la noticia");
            }
            
            error_log("✅ Noticia insertada con ID: $idNoticia");
            
            // 2. PROCESAR IMÁGENES SEGÚN ESTRUCTURA REAL
            $camposImagenes = ['url_grande', 'url_thumbnail', 'url_thumbnail_1', 'url_thumbnail_2'];
            $tieneImagenes = false;
            
            // Verificar si hay al menos una imagen
            foreach ($camposImagenes as $campo) {
                if (isset($datos[$campo]) && !empty($datos[$campo])) {
                    $tieneImagenes = true;
                    break;
                }
            }
            
            if ($tieneImagenes) {
                error_log("Procesando imágenes para noticia ID: $idNoticia");
                
                // Preparar registro completo de imagen según estructura real
                $registroImagenCompleto = [
                    'id_noticia' => $idNoticia,
                    'url_grande' => $datos['url_grande'] ?? '',
                    'url_thumbnail' => $datos['url_thumbnail'] ?? '',
                    'url_thumbnail_1' => $datos['url_thumbnail_1'] ?? '',
                    'url_thumbnail_2' => $datos['url_thumbnail_2'] ?? '',
                    'descripcion' => 'Imágenes de la noticia',
                    'es_principal' => !empty($datos['url_grande']) ? 1 : 0
                ];
                
                error_log("Registro completo de imagen: " . print_r($registroImagenCompleto, true));
                
                $idImagenInsertada = $this->insertarImagenReal($registroImagenCompleto);
                
                if ($idImagenInsertada && $idImagenInsertada > 0) {
                    error_log("✅ Registro de imágenes insertado con ID: $idImagenInsertada");
                } else {
                    error_log("❌ Error al insertar registro de imágenes");
                }
            } else {
                error_log("No hay imágenes para procesar");
            }
            
            // 3. VERIFICAR RESULTADO FINAL
            $verificacion = $this->verificarImagenesDeNoticiaReal($idNoticia);
            error_log("- Registros de imagen verificados en BD: " . count($verificacion));
            
            error_log("=== FIN guardarNoticiaCompleta - ÉXITO ===");
            return $idNoticia;
            
        } catch (Exception $e) {
            error_log("EXCEPCIÓN en guardarNoticiaCompleta: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Método corregido para verificar imágenes según estructura real
     */
    public function verificarImagenesDeNoticiaReal(int $idNoticia): array
    {
        try {
            $sql = "SELECT * FROM imagenes WHERE id_noticia = ? ORDER BY es_principal DESC, id ASC";
            $imagenes = $this->db->query($sql, [$idNoticia]);
            
            error_log("Verificación de imágenes para noticia $idNoticia: " . count($imagenes) . " registros encontrados");
            
            return $imagenes;
        } catch (Exception $e) {
            error_log("Error al verificar imágenes de noticia $idNoticia: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Método corregido para actualizar noticia completa
     */
    public function actualizarNoticiaCompleta($id, $datos): bool
    {
        try {
            error_log("=== INICIO actualizarNoticiaCompleta CORREGIDO ===");
            error_log("ID: $id, Datos: " . print_r($datos, true));
            
            // Verificar que la noticia existe
            $noticiaExiste = $this->db->select('noticias', 'id', ['id' => $id]);
            if (empty($noticiaExiste)) {
                error_log("ERROR: No se encontró noticia con ID: $id");
                return false;
            }
            
            // Separar datos de noticia de datos de imágenes
            $datosNoticia = [];
            
            // Campos específicos de la tabla noticias
            $camposNoticia = ['titulo', 'autor', 'resumen', 'contenido', 'id_categoria', 'id_estado', 'publicado_en'];
            
            foreach ($camposNoticia as $campo) {
                if (isset($datos[$campo])) {
                    $datosNoticia[$campo] = $datos[$campo];
                }
            }
            
            // Actualizar timestamp
            $datosNoticia['actualizado_en'] = date('Y-m-d H:i:s');
            
            // Actualizar la noticia
            $resultado = $this->db->updateSeguro('noticias', $datosNoticia, ['id' => $id]);
            
            if (!$resultado) {
                throw new Exception("No se pudo actualizar la noticia");
            }
            
            error_log("✅ Noticia actualizada exitosamente");
            
            // Procesar imágenes nuevas si las hay
            $camposImagenes = ['url_grande', 'url_thumbnail', 'url_thumbnail_1', 'url_thumbnail_2'];
            $tieneImagenesNuevas = false;
            
            foreach ($camposImagenes as $campo) {
                if (isset($datos[$campo]) && !empty($datos[$campo])) {
                    $tieneImagenesNuevas = true;
                    break;
                }
            }
            
            if ($tieneImagenesNuevas) {
                error_log("Procesando imágenes nuevas para noticia ID: $id");
                
                // Eliminar registros de imágenes existentes
                $sqlEliminar = "DELETE FROM imagenes WHERE id_noticia = ?";
                $this->db->query($sqlEliminar, [$id]);
                error_log("Registros de imágenes anteriores eliminados");
                
                // Insertar nuevo registro de imágenes
                $registroImagenCompleto = [
                    'id_noticia' => $id,
                    'url_grande' => $datos['url_grande'] ?? '',
                    'url_thumbnail' => $datos['url_thumbnail'] ?? '',
                    'url_thumbnail_1' => $datos['url_thumbnail_1'] ?? '',
                    'url_thumbnail_2' => $datos['url_thumbnail_2'] ?? '',
                    'descripcion' => 'Imágenes de la noticia actualizadas',
                    'es_principal' => !empty($datos['url_grande']) ? 1 : 0
                ];
                
                $idImagenInsertada = $this->insertarImagenReal($registroImagenCompleto);
                
                if ($idImagenInsertada) {
                    error_log("✅ Nuevas imágenes insertadas con ID: $idImagenInsertada");
                } else {
                    error_log("❌ Error al insertar nuevas imágenes");
                }
            }
            
            error_log("=== FIN actualizarNoticiaCompleta CORREGIDO - ÉXITO ===");
            return true;
            
        } catch (Exception $e) {
            error_log("EXCEPCIÓN en actualizarNoticiaCompleta: " . $e->getMessage());
            return false;
        }
    }
}