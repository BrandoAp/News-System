<?php
require_once __DIR__ . '/../../db/DatabaseManager.php';

class NoticiasController {
    protected $db;

    public function __construct(DatabaseManager $db) {
        $this->db = $db;
    }

    /**
     * Obtiene todas las noticias ordenadas por fecha descendente
     * @param string|null $busqueda Término de búsqueda opcional
     * @param int $pagina Número de página para paginación
     * @return array Listado de noticias con información básica
     */
   public function obtenerTodasLasNoticias($busqueda = null, $pagina = 1) {
    $condiciones = [];
    $limit = $this->db->getPerPage();
    $offset = ($pagina - 1) * $limit;
    
    if ($busqueda) {
        $condiciones = [
            'OR' => [
                'titulo[~]' => "%$busqueda%",
                'contenido[~]' => "%$busqueda%",
                'autor[~]' => "%$busqueda%"
            ]
        ];
    }
    
    $condiciones['LIMIT'] = [$offset, $limit];
    
    $noticias = $this->db->select('noticias', '*', $condiciones);
    
    // Ordenar por fecha de publicación descendente (usa publicado_en si existe, sino creado_en)
    usort($noticias, function($a, $b) {
        $fechaA = $a['publicado_en'] ?? $a['creado_en'] ?? null;
        $fechaB = $b['publicado_en'] ?? $b['creado_en'] ?? null;
        
        // Si alguna fecha es null, la ponemos al final
        if ($fechaA === null) return 1;
        if ($fechaB === null) return -1;
        
        return strtotime($fechaB) <=> strtotime($fechaA);
    });
    
    // Agregar campo 'fecha' para compatibilidad con la vista
    foreach ($noticias as &$noticia) {
        $noticia['fecha'] = $noticia['publicado_en'] ?? $noticia['creado_en'] ?? '';
        $this->agregarMediosANoticia($noticia);
    }
    unset($noticia);
    
    return $noticias;
}
    
    /**
     * Obtiene el total de noticias para paginación
     * @param string|null $busqueda Término de búsqueda opcional
     * @return int Total de noticias
     */
    public function contarNoticias($busqueda = null) {
        $condiciones = [];
        
        if ($busqueda) {
            $condiciones = [
                'OR' => [
                    'titulo[~]' => "%$busqueda%",
                    'contenido[~]' => "%$busqueda%",
                    'autor[~]' => "%$busqueda%"
                ]
            ];
        }
        
        return $this->db->count('noticias', $condiciones);
    }
    
    /**
     * Obtiene una noticia específica por ID con todos sus medios
     * @param int $id ID de la noticia
     * @return array|null Datos de la noticia o null si no existe
     */
    public function obtenerNoticiaPorId($id) {
        $noticia = $this->db->select('noticias', '*', ['id' => $id]);
        
        if (!empty($noticia)) {
            $noticia = $noticia[0];
            $this->agregarMediosANoticia($noticia, false); // Obtener todas las imágenes
            return $noticia;
        }
        
        return null;
    }
    
    /**
     * Crea una nueva noticia con sus medios asociados
     * @param array $datosNoticia Datos principales de la noticia
     * @param array $imagenes Array de imágenes a asociar
     * @param array $videos Array de videos a asociar
     * @return int|false ID de la nueva noticia o false en caso de error
     */
    public function crearNoticia($datosNoticia, $imagenes = [], $videos = []) {
        // Validar datos requeridos
        if (empty($datosNoticia['titulo']) || empty($datosNoticia['contenido']) || empty($datosNoticia['autor'])) {
            return false;
        }
        
        // Insertar la noticia principal
        $resultado = $this->db->insertSeguro('noticias', $datosNoticia);
        
        if ($resultado) {
            $idNoticia = $this->db->lastInsertId();
            
            // Insertar imágenes
            foreach ($imagenes as $imagen) {
                $this->db->insertSeguro('imagenes', [
                    'id_noticia' => $idNoticia,
                    'url_grande' => $imagen['url_grande'],
                    'url_pequena' => $imagen['url_pequena'],
                    'es_principal' => $imagen['es_principal'] ?? 0
                ]);
            }
            
            // Insertar videos
            foreach ($videos as $video) {
                $this->db->insertSeguro('videos', [
                    'id_noticia' => $idNoticia,
                    'url' => $video['url'],
                    'titulo' => $video['titulo'] ?? ''
                ]);
            }
            
            return $idNoticia;
        }
        
        return false;
    }
    
    /**
     * Actualiza una noticia existente y sus medios
     * @param int $id ID de la noticia a actualizar
     * @param array $datosNoticia Nuevos datos de la noticia
     * @param array $imagenes Nuevas imágenes (reemplazan las existentes)
     * @param array $videos Nuevos videos (reemplazan los existentes)
     * @return bool True si se actualizó correctamente
     */
    public function actualizarNoticia($id, $datosNoticia, $imagenes = [], $videos = []) {
        // Validar ID
        if (empty($id)) {
            return false;
        }
        
        // Eliminar campos que no deberían actualizarse
        unset($datosNoticia['id']);
        
        // Actualizar la noticia principal
        $resultado = $this->db->updateSeguro('noticias', $datosNoticia, ['id' => $id]);
        
        if ($resultado) {
            // Eliminar imágenes existentes
            $this->db->delete('imagenes', ['id_noticia' => $id]);
            
            // Insertar nuevas imágenes
            foreach ($imagenes as $imagen) {
                $this->db->insertSeguro('imagenes', [
                    'id_noticia' => $id,
                    'url_grande' => $imagen['url_grande'],
                    'url_pequena' => $imagen['url_pequena'],
                    'es_principal' => $imagen['es_principal'] ?? 0
                ]);
            }
            
            // Eliminar videos existentes
            $this->db->delete('videos', ['id_noticia' => $id]);
            
            // Insertar nuevos videos
            foreach ($videos as $video) {
                $this->db->insertSeguro('videos', [
                    'id_noticia' => $id,
                    'url' => $video['url'],
                    'titulo' => $video['titulo'] ?? ''
                ]);
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Elimina una noticia y todos sus medios asociados
     * @param int $id ID de la noticia a eliminar
     * @return bool True si se eliminó correctamente
     */
    public function eliminarNoticia($id) {
        // Primero eliminar imágenes y videos asociados
        $this->db->delete('imagenes', ['id_noticia' => $id]);
        $this->db->delete('videos', ['id_noticia' => $id]);
        
        // Luego eliminar la noticia
        return $this->db->delete('noticias', ['id' => $id]);
    }
    
    /**
     * Busca noticias por término
     * @param string $termino Término de búsqueda
     * @param int $pagina Número de página para paginación
     * @return array Noticias que coinciden con la búsqueda
     */
    public function buscarNoticias($termino, $pagina = 1) {
        return $this->obtenerTodasLasNoticias($termino, $pagina);
    }
    
    /**
     * Agrega información de medios (imágenes/videos) a una noticia
     * @param array &$noticia Referencia a la noticia a modificar
     * @param bool $soloPrincipal Si es true, solo obtiene la imagen principal
     */
    private function agregarMediosANoticia(&$noticia, $soloPrincipal = true) {
        // Obtener imágenes
        $condicionesImg = ['id_noticia' => $noticia['id']];
        if ($soloPrincipal) {
            $condicionesImg['es_principal'] = 1;
        }
        
        $imagenes = $this->db->select('imagenes', '*', $condicionesImg);
        
        if ($soloPrincipal) {
            $noticia['imagen'] = !empty($imagenes) ? $imagenes[0]['url_grande'] : '';
        } else {
            $noticia['imagenes'] = $imagenes;
            $noticia['imagen_principal'] = '';
            
            foreach ($imagenes as $img) {
                if ($img['es_principal']) {
                    $noticia['imagen_principal'] = $img['url_grande'];
                    break;
                }
            }
        }
        
        // Obtener videos
        $videos = $this->db->select('videos', '*', ['id_noticia' => $noticia['id']]);
        $noticia['videos'] = $videos;
        $noticia['tiene_video'] = !empty($videos);
    }
}
?>