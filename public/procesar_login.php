<?php
session_start();

require_once __DIR__ . '/../db/conexionDB.php';
require_once __DIR__ . '/../src/validaciones/validar.php'; 
require_once __DIR__ . '/../src/modules/usuario.php'; 
require_once __DIR__ . '/../src/validaciones/validadorUsuaios.php'; 


$error = '';
$usuario = new Usuario();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nombre' => Sanitizador::limpiarTexto($_POST['nombre'] ?? ''),
        'contrasena' => Sanitizador::limpiarTexto($_POST['contrasena'] ?? '')
    ];

    $errores = $usuario->validarLogin($data);

    if (empty($errores)) {
        $db = ConexionDB::obtenerInstancia()->obtenerConexion();

        $stmt = $db->prepare("SELECT u.id, u.nombre, u.contrasena, r.nombre AS rol FROM usuarios u JOIN roles r ON u.id_rol = r.id WHERE u.nombre = ?");
        $stmt->execute([$data['nombre']]);
        $datosUsuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($datosUsuario) {
            $loginValido = false;

            if (password_verify($data['contrasena'], $datosUsuario['contrasena'])) {
                $loginValido = true;
            } elseif ($data['contrasena'] === $datosUsuario['contrasena']) {
                $loginValido = true;
                $hashed = password_hash($data['contrasena'], PASSWORD_DEFAULT);
                $stmtUpdate = $db->prepare("UPDATE usuarios SET contrasena = ? WHERE id = ?");
                $stmtUpdate->execute([$hashed, $datosUsuario['id']]);
            }

            if ($loginValido) {
                $_SESSION['usuario_id'] = $datosUsuario['id'];
                $_SESSION['usuario_nombre'] = $datosUsuario['nombre'];
                $_SESSION['usuario_rol'] = $datosUsuario['rol']; 
                header("Location: registrar_usuario");
                exit;
            } else {
                $error = "Nombre de usuario o contraseña incorrectos.";
            }
        } else {
            $error = "Nombre de usuario o contraseña incorrectos.";
        }
    } else {
        $error = implode("<br>", $errores);
    }
}
?>
