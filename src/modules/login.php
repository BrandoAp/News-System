<?php
require_once __DIR__ . '/../modules/Usuario.php';
require_once __DIR__ . '/../../db/conexionDB.php';
require_once __DIR__ . '/../../src/validaciones/ILogin.php';

class Login implements ILogin {
    private Usuario $usuario;

    public function __construct() {
        $this->usuario = new Usuario();
    }

    public function validarLogin(array $data): array {
        return $this->usuario->validarCredenciales($data);
    }

    public function autenticar(string $nombre, string $contrasena): array {
        try {
            $db = ConexionDB::obtenerInstancia()->obtenerConexion();

            $stmt = $db->prepare("SELECT u.id, u.nombre, u.contrasena, u.id_estado, r.nombre AS rol FROM usuarios u JOIN roles r ON u.id_rol = r.id WHERE u.nombre = ?");
            $stmt->execute([$nombre]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                return ['estado' => 'usuario_no_encontrado'];
            }

            if ($usuario['id_estado'] == 0) {
                return ['estado' => 'inactivo'];
            }

            if (password_verify($contrasena, $usuario['contrasena'])) {
                return ['estado' => 'autenticado', 'usuario' => $usuario];
            }

            // Si no estaba hasheado antes
            if ($contrasena === $usuario['contrasena']) {
                $hashed = password_hash($contrasena, PASSWORD_DEFAULT);
                $stmtUpdate = $db->prepare("UPDATE usuarios SET contrasena = ? WHERE id = ?");
                $stmtUpdate->execute([$hashed, $usuario['id']]);
                return ['estado' => 'autenticado', 'usuario' => $usuario];
            }

            return ['estado' => 'contrasena_incorrecta'];
        } catch (PDOException $e) {
            return ['estado' => 'error_bd'];
        }
    }

}
