<?php

require_once __DIR__ . '/../../db/DatabaseManager.php';

class NoticiasController
{
    private $db;

    public function __construct(PDO $pdoConnection)
    {
        $this->db = new DatabaseManager($pdoConnection);
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
        $noticia = $this->db->select('noticias', '*', ['id' => $id]);
        if (empty($noticia)) {
            return false;
        }
        $noticia = $noticia[0];
        $noticia['imagenes'] = $this->db->select('imagenes', '*', ['id_noticia' => $id]);
        return $noticia;
    }
    
    /**
     * Busca el ID de un estado por su nombre.
     */
    public function obtenerIdEstadoPorNombre(string $nombreEstado): ?int
    {
        $resultado = $this->db->select('estados', 'id', ['nombre' => $nombreEstado]);
        return $resultado ? (int)$resultado[0]['id'] : null;
 
    }
    
}