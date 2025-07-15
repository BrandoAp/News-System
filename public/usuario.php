<?php
require_once 'DatabaseManager.php';
require_once 'ConexionDB.php'; 

class Usuario {
    private $db;

    public function __construct() {
        $conexion = ConexionDB::obtenerInstancia()->obtenerConexion(); 
        $this->db = new DatabaseManager($conexion);
    }

    public function registrar(array $datos): bool {
        return $this->db->insertSeguro("usuarios", $datos);
    }

    public function actualizar(int $id, array $datos): bool {
        return $this->db->updateSeguro("usuarios", $datos, ["id" => $id]);
    }

    public function obtenerTodos(): array {
        return $this->db->select("usuarios");
    }

    public function obtenerPorId(int $id): ?array {
        $resultado = $this->db->select("usuarios", "*", ["id" => $id]);
        return $resultado ? $resultado[0] : null;
    }

    public function buscarPorCorreo(string $correo): ?array {
        $resultado = $this->db->select("usuarios", "*", ["correo" => $correo]);
        return $resultado ? $resultado[0] : null;
    }
}
