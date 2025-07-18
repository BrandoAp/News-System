<?php

require_once __DIR__ . '/../../db/DatabaseManager.php';

class PaginaPublicaController
{
    private $db;

    public function __construct(PDO $pdoConnection)
    {
        $this->db = new DatabaseManager($pdoConnection);
    }

    /**
     * Obtiene las 4 últimas noticias guardadas con su imagen principal (si existe)
     * @return array
     */
    public function obtenerUltimasNoticias()
    {
        // Obtener las noticias ordenadas por fecha descendente
        $noticias = $this->db->select('noticias', '*');
        usort($noticias, function($a, $b) {
            return strtotime($b['publicado_en']) <=> strtotime($a['publicado_en']);
        });
        $noticias = array_slice($noticias, 0, 4);

        // Para cada noticia, obtener la imagen principal (si existe)
        foreach ($noticias as &$noticia) {
            $imagenes = $this->db->select('imagenes', '*', [
                'id_noticia' => $noticia['id'],
                'es_principal' => 1
            ]);
            // Si hay imagen principal, usar url_grande, si no, dejar vacío
            $noticia['imagen'] = !empty($imagenes) ? $imagenes[0]['url_grande'] : '';
        }
        unset($noticia);

        return $noticias;
    }

    /**
     * Obtiene la lista completa de noticias con su imagen principal (si existe)
     * @return array
     */
    public function obtenerTodasLasNoticias()
    {
        $noticias = $this->db->select('noticias', '*');
        usort($noticias, function($a, $b) {
            return strtotime($b['publicado_en']) <=> strtotime($a['publicado_en']);
        });

        foreach ($noticias as &$noticia) {
            // Buscar cualquier imagen asociada si no hay principal
            $imagenes = $this->db->select('imagenes', '*', [
                'id_noticia' => $noticia['id'],
                'es_principal' => 1
            ]);
            if (!empty($imagenes)) {
                $noticia['imagen'] = $imagenes[0]['url_grande'];
            } else {
                // Si no hay imagen principal, buscar cualquier imagen asociada
                $imagenesSec = $this->db->select('imagenes', '*', [
                    'id_noticia' => $noticia['id']
                ]);
                $noticia['imagen'] = !empty($imagenesSec) ? $imagenesSec[0]['url_grande'] : '';
            }
        }
        unset($noticia);

        return $noticias;
    }

    public function contarNoticiasPublicadas(){
        return $this->db->count('noticias', ['id_estado' => 3]);
    }
}
