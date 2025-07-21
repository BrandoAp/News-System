<?php
session_start();
require_once __DIR__ . '/../src/modules/usuario.php';

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioObj = new Usuario();
    $resultado = $usuarioObj->loginLector($_POST['correo'] ?? '', $_POST['contrasena'] ?? '');
    if (!empty($resultado['exito']) && !empty($resultado['usuario'])) {
        $_SESSION['usuario'] = $resultado['usuario'];
        header('Location: index.php');
        exit;
    } else {
        $mensaje = $resultado['mensaje'] ?? 'Error al iniciar sesión.';
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
        <a href="form_lector.php" class="block mt-6 text-blue-600 hover:underline text-center">¿No tienes cuenta? Regístrate</a>
        <a href="index.php" class="block mt-2 text-blue-600 hover:underline text-center">Volver al inicio</a>
    </div>
</body>
</html>