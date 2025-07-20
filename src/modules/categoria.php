<?php


require_once __DIR__ . '/../../db/conexionDB.php';
require_once __DIR__ . '/../../db/DatabaseManager.php';


class Categoria {
    private $db;
    public function __construct() {
        $pdo = ConexionDB::obtenerInstancia()->obtenerConexion();
        $this->db = new DatabaseManager($pdo);
    }

    public function obtenerTodos(): array {
        return $this->db->select('categorias', '*');
    }

    public function obtenerPorId(int $id): ?array {
        $res = $this->db->select('categorias', '*', ['id' => $id]);
        return $res ? $res[0] : null;
    }

    public function registrar(array $datos): bool {
        return $this->db->insertSeguro('categorias', $datos);
    }

    public function actualizar(int $id, array $datos): bool {
        return $this->db->updateSeguro('categorias', $datos, ['id' => $id]);
    }

    public function actualizarEstado(int $id): bool {
        return $this->db->updateSeguro('categorias', ['id_estado' => 2], ['id' => $id]);
    }

    public function contadorNoticias(int $id): int {
        // Tabla real: 'noticias'
        $r = $this->db->select('noticias', 'COUNT(*) AS total', ['id_categoria' => $id]);
        return $r ? (int)$r[0]['total'] : 0;
    }
}
