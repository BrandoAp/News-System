<?php
require_once __DIR__ . '/../../db/conexionDB.php';
require_once __DIR__ . '/../../db/DatabaseManager.php';

class Usuario {
    private $db;

    public function __construct() {
        $conexion = ConexionDB::obtenerInstancia()->obtenerConexion();
        $this->db = new DatabaseManager($conexion);
    }

    public function guardar($data) {
        return $this->db->insertSeguro("usuarios", $data);
    }

    public function editar($id, $data) {
        return $this->db->updateSeguro("usuarios", $data, ["id" => $id]);
    }

    public function buscarTodos() {
        return $this->db->select("usuarios", "*", ["id_estado[!]"=> -1]);
    }

    public function buscarPorId($id) {
        return $this->db->select("usuarios", "*", ["id" => $id])[0] ?? null;
    }

    // Método para cambiar estado (activar/desactivar)
    public function cambiarEstado($id, $nuevoEstado) {
        return $this->db->updateSeguro("usuarios", ["id_estado" => $nuevoEstado], ["id" => $id]);
    }

    // Método para eliminar usuario (cambiar estado a -1)
    public function eliminar($id) {
        return $this->db->updateSeguro("usuarios", ["id_estado" => -1], ["id" => $id]);
    }

    // Método legacy - mantenido para compatibilidad
    public function actualizarEstado(int $id): bool {
        return $this->db->updateSeguro('usuarios', ['id_estado' => 2], ['id' => $id]);
    }

    public function validar($data, $accion = 'Guardar') {
        $errors = [];

        if (empty($data['nombre'])) {
            $errors[] = "El nombre es obligatorio.";
        }

        if (empty($data['correo']) || !filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Correo inválido.";
        }

        if ($accion === 'Guardar') {
            if (empty($data['contrasena']) || strlen($data['contrasena']) < 6) {
                $errors[] = "Contraseña inválida. Mínimo 6 caracteres.";
            }
            
            $existe = $this->db->select("usuarios", "id", ["correo" => $data['correo']]);
            if (!empty($existe)) {
                $errors[] = "El correo ya está registrado.";
            }
        } elseif ($accion === 'Modificar') {
            if (!empty($data['contrasena']) && strlen($data['contrasena']) < 6) {
                $errors[] = "Contraseña inválida. Mínimo 6 caracteres.";
            }
        }

        $rolesPermitidos = ['admin', 'editor', 'lector', 'supervisor'];
        if (!in_array($data['rol'], $rolesPermitidos)) {
            $errors[] = "Rol no válido.";
        }

        return $errors;
    }
}
?>