<?php
require_once __DIR__ . '/../modules/Login.php';
require_once __DIR__ . '/../validaciones/Sanitizador.php';

class LoginController {
    private Login $login;

    public function __construct() {
        $this->login = new Login();
    }

    public function procesar(array $datos): void {
        session_start();
        $nombre = Sanitizador::limpiarTexto($datos['nombre'] ?? '');
        $contrasena = Sanitizador::limpiarTexto($datos['contrasena'] ?? '');

        $errores = $this->login->validarLogin([
            'nombre' => $nombre,
            'contrasena' => $contrasena
        ]);

        if (!empty($errores)) {
            $_SESSION['error'] = implode("<br>", $errores);
            header("Location: ../../public/login.php");
            exit;
        }

        if ($this->login->autenticar($nombre, $contrasena)) {
            $db = ConexionDB::obtenerInstancia()->obtenerConexion();
            $stmt = $db->prepare("SELECT u.id, u.nombre, r.nombre AS rol FROM usuarios u JOIN roles r ON u.id_rol = r.id WHERE u.nombre = ?");
            $stmt->execute([$nombre]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                header("Location: /News-System/public/dashboard");
                exit;
            } else {
                $_SESSION['error'] = "Error al obtener los datos del usuario.";
            }
        } else {
            $_SESSION['error'] = "Nombre de usuario o contraseña incorrectos.";
        }

        header("Location: ../../public/login.php");
        exit;
    }
}
