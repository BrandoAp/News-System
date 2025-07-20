<?php
require_once __DIR__ . '/../modules/Usuario.php';
require_once __DIR__ . '/../../db/conexionDB.php';

class Login {
    private Usuario $usuario;

    public function __construct() {
        $this->usuario = new Usuario();
    }

    public function validarLogin(array $data): array {
        return $this->usuario->validarLogin($data);
    }

    public function autenticar(string $nombre, string $contrasena): bool {
        $db = ConexionDB::obtenerInstancia()->obtenerConexion();

        $stmt = $db->prepare("SELECT id, contrasena FROM usuarios WHERE nombre = ?");
        $stmt->execute([$nombre]);
        $datosUsuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$datosUsuario) {
            return false;
        }

        // Verificar contraseña hasheada
        if (password_verify($contrasena, $datosUsuario['contrasena'])) {
            return true;
        }

        // Si la contraseña guardada no está hasheada pero coincide con texto plano,
        // actualizar a hash seguro.
        if ($contrasena === $datosUsuario['contrasena']) {
            $hashed = password_hash($contrasena, PASSWORD_DEFAULT);
            $stmtUpdate = $db->prepare("UPDATE usuarios SET contrasena = ? WHERE id = ?");
            $stmtUpdate->execute([$hashed, $datosUsuario['id']]);
            return true;
        }

        return false;
    }
}
