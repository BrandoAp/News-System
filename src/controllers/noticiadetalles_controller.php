<?php 

    require_once __DIR__ . '/../../db/DatabaseManager.php';
    require_once __DIR__ . '/../../src/modules/noticiasPublicas.php';
    require_once __DIR__ . '/../../src/validaciones/Sanitizador.php';

class NoticiaDetallesController{
    private $db;
    private $noticiasPublicas;
    private $sanitizador;

    public function __construct(PDO $pdoConnection)
    {
        $this->db = new DatabaseManager($pdoConnection);
        $this->noticiasPublicas = new NoticiasPublicas($this->db);
        $this->sanitizador = new Sanitizador();
    }
    /**
     * Obtiene la lista completa de noticias con su imagen principal (si existe)
     * @return array
     */
    public function obtenerTodasLasNoticias()
    {
        // Solo noticias con id_estado = 3 (publicadas)
        $noticias = $this->db->select('noticias', '*', ['id_estado' => 3]);
        usort($noticias, function($a, $b) {
            // Manejar valores NULL en publicado_en
            $fechaA = $a['publicado_en'] ? strtotime($a['publicado_en']) : 0;
            $fechaB = $b['publicado_en'] ? strtotime($b['publicado_en']) : 0;
            return $fechaB <=> $fechaA;
        });

        foreach ($noticias as &$noticia) {
            $idNoticia = $this->sanitizador->limpiarTexto($noticia['id']);
            $imagenes = $this->db->select('imagenes', '*', [
                'id_noticia' => $idNoticia,
                'es_principal' => 1
            ]);
            if (!empty($imagenes)) {
                $noticia['imagen'] = $imagenes[0]['url_grande'];
            } else {
                $imagenesSec = $this->db->select('imagenes', '*', [
                    'id_noticia' => $idNoticia
                ]);
                $noticia['imagen'] = !empty($imagenesSec) ? $imagenesSec[0]['url_grande'] : '';
            }
        }
        unset($noticia);

        return $noticias;
    }

    public function agregarComentario($idNoticia, $idUsuario, $contenido) {
        $idNoticia = $this->sanitizador->limpiarTexto($idNoticia);
        $idUsuario = $this->sanitizador->limpiarTexto($idUsuario);
        $contenido = $this->sanitizador->limpiarTexto($contenido);
        return $this->noticiasPublicas->agregarComentario($idNoticia, $idUsuario, $contenido);
    }

    /**
     * Agregar respuesta a un comentario (solo admin)
     */
    public function responderComentario($idNoticia, $idUsuario, $contenido, $idComentarioPadre) {
        $idNoticia = $this->sanitizador->limpiarTexto($idNoticia);
        $idUsuario = $this->sanitizador->limpiarTexto($idUsuario);
        $contenido = $this->sanitizador->limpiarTexto($contenido);
        $idComentarioPadre = $this->sanitizador->limpiarTexto($idComentarioPadre);
        
        return $this->db->insertSeguro('comentarios', [
            'id_noticia' => $idNoticia,
            'id_usuario' => $idUsuario,
            'contenido' => $contenido,
            'id_respuesta' => $idComentarioPadre
        ]);
    }

    /**
     * Eliminar comentario (solo supervisor)
     */
    public function eliminarComentario($idComentario) {
        $idComentario = $this->sanitizador->limpiarTexto($idComentario);
        
        // Primero eliminamos las respuestas a este comentario
        $this->db->deleteSeguro('comentarios', ['id_respuesta' => $idComentario]);
        
        // Luego eliminamos el comentario principal
        return $this->db->deleteSeguro('comentarios', ['id' => $idComentario]);
    }

    public function obtenerComentariosDeNoticia($idNoticia) {
        $idNoticia = $this->sanitizador->limpiarTexto($idNoticia);
        return $this->noticiasPublicas->obtenerComentariosDeNoticia($idNoticia);
    }

    /**
     * Obtener comentarios con sus respuestas organizados jerárquicamente
     */
    public function obtenerComentariosConRespuestas($idNoticia) {
        $idNoticia = $this->sanitizador->limpiarTexto($idNoticia);
        
        // Obtener todos los comentarios de la noticia con información del usuario
        $sql = "SELECT c.*, u.nombre 
                FROM comentarios c 
                INNER JOIN usuarios u ON c.id_usuario = u.id 
                WHERE c.id_noticia = ? 
                ORDER BY c.creado_en ASC";
        
        $comentarios = $this->db->query($sql, [$idNoticia]);
        
        // Organizar comentarios por jerarquía
        $comentariosPrincipales = [];
        $respuestas = [];
        
        foreach ($comentarios as $comentario) {
            if ($comentario['id_respuesta'] === null) {
                // Es un comentario principal
                $comentario['respuestas'] = [];
                $comentariosPrincipales[$comentario['id']] = $comentario;
            } else {
                // Es una respuesta
                $respuestas[$comentario['id_respuesta']][] = $comentario;
            }
        }
        
        // Asignar respuestas a comentarios principales
        foreach ($respuestas as $idPadre => $listRespuestas) {
            if (isset($comentariosPrincipales[$idPadre])) {
                $comentariosPrincipales[$idPadre]['respuestas'] = $listRespuestas;
            }
        }
        
        return array_values($comentariosPrincipales);
    }

    public function obtenerImagenesDeNoticia($idNoticia)
    {
        $idNoticia = $this->sanitizador->limpiarTexto($idNoticia);
        return $this->db->select('imagenes', '*', [
            'id_noticia' => $idNoticia
        ]);
    }
}