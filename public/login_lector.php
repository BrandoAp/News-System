<?php
session_start();
require_once __DIR__ . '/../db/conexionDB.php';
require_once __DIR__ . '/../db/DatabaseManager.php';

$pdo = ConexionDB::obtenerInstancia()->obtenerConexion();
$db = new DatabaseManager($pdo);

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    if ($correo !== '' && $contrasena !== '') {
        // Buscar usuario lector
        $usuarios = $db->select('usuarios', '*', [
            'correo' => $correo,
            'id_rol' => 4 // Solo lectores
        ]);
        if (!empty($usuarios)) {
            $usuario = $usuarios[0];
            if (password_verify($contrasena, $usuario['contrasena'])) {
                // Login exitoso
                $_SESSION['usuario'] = [
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'correo' => $usuario['correo'],
                    'id_rol' => $usuario['id_rol']
                ];
                header('Location: index.php');
                exit;
            } else {
                $mensaje = 'Contraseña incorrecta.';
            }
        } else {
            $mensaje = 'Usuario no encontrado o no es lector.';
        }
    } else {
        $mensaje = 'Completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión como Lector</title>
    <link rel="stylesheet" href="./css/noticias.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center">
    <div class="bg-white rounded-2xl shadow p-8 mt-12 w-full max-w-md">
        <h2 class="text-2xl font-bold text-blue-900 mb-4 text-center">Iniciar sesión como Lector</h2>
        <?php if ($mensaje): ?>
            <div class="mb-4 text-center text-red-600 font-medium"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <form method="post" class="flex flex-col gap-4">
            <input type="email" name="correo" placeholder="Correo electrónico" class="border border-gray-300 rounded-lg p-2" required>
            <input type="password" name="contrasena" placeholder="Contraseña" class="border border-gray-300 rounded-lg p-2" required>
            <button type="submit" class="bg-blue-600 text-white rounded-full px-6 py-2 font-medium hover:bg-blue-700 transition">Iniciar sesión</button>
        </form>
        <a href="form_visitantes.php" class="block mt-6 text-blue-600 hover:underline text-center">¿No tienes cuenta? Regístrate</a>
        <a href="index.php" class="block mt-2 text-blue-600 hover:underline text-center">Volver al inicio</a>
    </div>
</body>
</html>