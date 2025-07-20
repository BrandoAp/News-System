<?php

require_once __DIR__ . '/../../db/DatabaseManager.php';
require_once __DIR__ . '/../../src/modules/noticiasPublicas.php';
require_once __DIR__ . '/../../src/validaciones/Sanitizador.php';

class PaginaPublicaController
{
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
     * Obtiene las 4 últimas noticias guardadas con su imagen principal (si existe)
     * @return array
     */
    public function obtenerUltimasNoticias()
    {
        // Solo noticias con id_estado = 3 (publicadas)
        $noticias = $this->db->select('noticias', '*', ['id_estado' => 3]);
        usort($noticias, function($a, $b) {
            return strtotime($b['publicado_en']) <=> strtotime($a['publicado_en']);
        });
        $noticias = array_slice($noticias, 0, 4);

        foreach ($noticias as &$noticia) {
            $idNoticia = $this->sanitizador->limpiarTexto($noticia['id']);
            $imagenes = $this->db->select('imagenes', '*', [
                'id_noticia' => $idNoticia,
                'es_principal' => 1
            ]);
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

    public function contarNoticiasPublicadas(){
        return $this->db->count('noticias', ['id_estado' => 3]);
    }

    /**
     * Obtiene el conteo de visitas del día usando el método de DatabaseManager
     * @return int
     */
    public function obtenerVisitasHoy(){
        return $this->noticiasPublicas->contarVisitasHoy();
    }

    public function obtenerImagenesDeNoticia($idNoticia)
    {
        $idNoticia = $this->sanitizador->limpiarTexto($idNoticia);
        return $this->db->select('imagenes', '*', [
            'id_noticia' => $idNoticia
        ]);
    }

    public function obtenerComentariosDeNoticia($idNoticia) {
        $idNoticia = $this->sanitizador->limpiarTexto($idNoticia);
        return $this->noticiasPublicas->obtenerComentariosDeNoticia($idNoticia);
    }

    public function agregarComentario($idNoticia, $idUsuario, $contenido) {
        $idNoticia = $this->sanitizador->limpiarTexto($idNoticia);
        $idUsuario = $this->sanitizador->limpiarTexto($idUsuario);
        $contenido = $this->sanitizador->limpiarTexto($contenido);
        return $this->noticiasPublicas->agregarComentario($idNoticia, $idUsuario, $contenido);
    }

    public function contarReacciones($idNoticia, $tipo) {
        $idNoticia = $this->sanitizador->limpiarTexto($idNoticia);
        $tipo = $this->sanitizador->limpiarTexto($tipo);
        return $this->noticiasPublicas->contarReacciones($idNoticia, $tipo);
    }

    public function usuarioYaReacciono($idUsuario, $idNoticia, $tipo) {
        $idUsuario = $this->sanitizador->limpiarTexto($idUsuario);
        $idNoticia = $this->sanitizador->limpiarTexto($idNoticia);
        $tipo = $this->sanitizador->limpiarTexto($tipo);
        return $this->noticiasPublicas->usuarioYaReacciono($idUsuario, $idNoticia, $tipo);
    }

    public function agregarReaccion($idUsuario, $idNoticia, $tipo) {
        $idUsuario = $this->sanitizador->limpiarTexto($idUsuario);
        $idNoticia = $this->sanitizador->limpiarTexto($idNoticia);
        $tipo = $this->sanitizador->limpiarTexto($tipo);
        return $this->noticiasPublicas->agregarReaccion($idUsuario, $idNoticia, $tipo);
    }

    public function obtenerTiposReaccion() {
        return $this->noticiasPublicas->obtenerTiposReaccion();
    }
}
