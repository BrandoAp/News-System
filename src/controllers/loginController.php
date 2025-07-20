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

        $resultado = $this->login->autenticar($nombre, $contrasena);
        $estado = $resultado['estado'] ?? 'error_desconocido';

        switch ($estado) {
            case 'autenticado':
                $usuario = $resultado['usuario'];
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                $_SESSION['id_estado'] = $usuario['id_estado'];
                header("Location: /ProyectoFinalDSVII/News-System/public/dashboard.php");
                exit;

            case 'inactivo':
                $_SESSION['error'] = "Acceso denegado: tu cuenta está inactiva.";
                break;

            case 'contrasena_incorrecta':
                $_SESSION['error'] = "Contraseña incorrecta.";
                break;

            case 'usuario_no_encontrado':
                $_SESSION['error'] = "Usuario no encontrado.";
                break;

            case 'error_bd':
                $_SESSION['error'] = "Error de base de datos.";
                break;

            default:
                $_SESSION['error'] = "Error desconocido.";
                break;
           }

          header("Location: ../../public/login.php");
          exit;

    }
}
