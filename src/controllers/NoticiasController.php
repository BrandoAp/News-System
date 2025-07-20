<?php
require_once __DIR__ . '../../../db/DatabaseManager.php';
require_once __DIR__ . '../../modules/ImagenManager.php';

class NoticiasController
{
    private $db;
    private $imagenManager;

    public function __construct(PDO $pdoConnection)
    {
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
    public function obtenerIdEstadoPorNombre(string $nombreEstado): ?int
    {
        try {
            $resultado = $this->db->select('estados', 'id', ['nombre' => $nombreEstado]);
            return $resultado ? (int)$resultado[0]['id'] : null;
        } catch (Exception $e) {
            error_log("Error en obtenerIdEstadoPorNombre: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Guarda una nueva noticia y procesa sus imágenes
     */
    public function guardarNoticia($datos, $archivos = null) {
        try {
            $idNoticia = $this->db->insertSeguro('noticias', $datos);

            if ($idNoticia && $archivos && isset($archivos['imagenes']) && !empty($archivos['imagenes']['name'][0])) {
                $resultado = $this->imagenManager->procesarImagenesNoticia($archivos, $idNoticia);
                
                // Log del resultado del procesamiento de imágenes
                error_log("Resultado procesamiento imágenes: " . print_r($resultado, true));
            }

            return $idNoticia;
        } catch (Exception $e) {
            error_log("Error en guardarNoticia: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza una noticia existente y procesa nuevas imágenes si las hay
     */
    public function actualizarNoticia($id, $datos, $archivos = null) {
        try {
            $resultado = $this->db->updateSeguro('noticias', $datos, ['id' => $id]);

            if ($resultado && $archivos && isset($archivos['imagenes']) && !empty($archivos['imagenes']['name'][0])) {
                $resultadoImagenes = $this->imagenManager->procesarImagenesNoticia($archivos, $id);
                
                // Log del resultado del procesamiento de imágenes
                error_log("Resultado procesamiento imágenes en actualización: " . print_r($resultadoImagenes, true));
            }

            return $resultado;
        } catch (Exception $e) {
            error_log("Error en actualizarNoticia: " . $e->getMessage());
            return false;
        }
    }
}