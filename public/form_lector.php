<?php
require_once __DIR__ . '/../db/conexionDB.php';
require_once __DIR__ . '/../db/DatabaseManager.php';

// Conexión y manager
$pdo = ConexionDB::obtenerInstancia()->obtenerConexion();
$db = new DatabaseManager($pdo);

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    // Validación básica
    if ($nombre !== '' && filter_var($correo, FILTER_VALIDATE_EMAIL) && strlen($contrasena) >= 4) {
        // Verificar si el correo ya existe
        $usuarios = $db->select('usuarios', '*', ['correo' => $correo]);
        if (!empty($usuarios)) {
            $mensaje = 'El correo ya está registrado.';
        } else {
            // Hash de la contraseña
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            // id_rol = 4 para lector
            $exito = $db->insertSeguro('usuarios', [
                'nombre' => $nombre,
                'correo' => $correo,
                'contrasena' => $hash,
                'id_rol' => 4
            ]);
            if ($exito) {
                $mensaje = '¡Registro exitoso! Ya puedes iniciar sesión como lector.';
            } else {
                $mensaje = 'Error al registrar. Intenta nuevamente.';
            }
        }
    } else {
        $mensaje = 'Completa todos los campos correctamente (contraseña mínimo 4 caracteres).';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario Lector</title>
    <link rel="stylesheet" href="./css/noticias.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center">
    <div class="bg-white rounded-2xl shadow p-8 mt-12 w-full max-w-md">
        <h2 class="text-2xl font-bold text-blue-900 mb-4 text-center">Registro de Usuario Lector</h2>
        <?php if ($mensaje): ?>
            <div class="mb-4 text-center text-blue-700 font-medium"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <form method="post" class="flex flex-col gap-4">
            <input type="text" name="nombre" placeholder="Nombre completo" class="border border-gray-300 rounded-lg p-2" required>
            <input type="email" name="correo" placeholder="Correo electrónico" class="border border-gray-300 rounded-lg p-2" required>
            <input type="password" name="contrasena" placeholder="Contraseña" class="border border-gray-300 rounded-lg p-2" minlength="4" required>
            <button type="submit" class="bg-blue-600 text-white rounded-full px-6 py-2 font-medium hover:bg-blue-700 transition">Registrarse</button>
        </form>
        <a href="login.php" class="block mt-6 text-blue-600 hover:underline text-center">¿Ya tienes cuenta? Inicia sesión</a>
        <a href="index.php" class="block mt-2 text-blue-600 hover:underline text-center">Volver al inicio</a>
    </div>
</body>