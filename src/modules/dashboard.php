<?php
// src/modules/Dashboard.php

require_once __DIR__   . '/../../db/DatabaseManager.php';
require_once __DIR__   . '/../controllers/pagina_publica_controller.php';

class Dashboard
{
    private DatabaseManager $db;
    private PaginaPublicaController $pubCtrl;

    public function __construct(PDO $pdo)
    {
        $this->db      = new DatabaseManager($pdo);
        $this->pubCtrl = new PaginaPublicaController($pdo);
    }

    // Usuarios
    public function usuariosTotales(): int
    {
        return $this->db->count('usuarios');
    }
    public function usuariosActivos(): int
    {
        return $this->db->count('usuarios', ['id_estado' => 1]);
    }
    public function usuariosInactivos(): int
    {
        return $this->db->count('usuarios', ['id_estado' => 0]);
    }

    // Categorías
    public function categoriasTotales(): int
    {
        return $this->db->count('categorias');
    }
    public function categoriasActivas(): int
    {
        return $this->db->count('categorias', ['id_estado' => 1]);
    }
    public function categoriasInactivas(): int
    {
        return $this->db->count('categorias', ['id_estado' => 2]);
    }

    // Noticias
    public function noticiasTotales(): int
    {
        return $this->db->count('noticias');
    }
    public function noticiasPublicadas(): int
    {
        return (int)$this->db->scalar(
            "SELECT COUNT(*) FROM noticias WHERE id_estado = ?",
            [3]
        );
    }
    public function noticiasArchivadas(): int
    {
        return (int)$this->db->scalar(
            "SELECT COUNT(*) FROM noticias WHERE id_estado = ?",
            [4]
        );
    }

    // Visitas de hoy
    public function visitasHoy(): int
    {
        return $this->pubCtrl->obtenerVisitasHoy();
    }

    // Top categorías
    public function topCategorias(int $limit = 5): array
    {
        $sql = "
            SELECT c.nombre, COUNT(n.id) AS total
            FROM categorias c
            LEFT JOIN noticias n ON n.id_categoria = c.id
            GROUP BY c.id
            ORDER BY total DESC
            LIMIT {$limit}
        ";
        return $this->db->query($sql);
    }

    // Últimas  noticias
    public function ultimasNoticias(int $limit = 5): array
    {
        $sql = "
            SELECT id, titulo, publicado_en, id_estado
            FROM noticias
            ORDER BY creado_en DESC
            LIMIT {$limit}
        ";
        return $this->db->query($sql);
    }
}
