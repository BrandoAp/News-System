<?php

session_start();
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);


?>

<!DOCTYPE html>
<html lang="es" class="bg-gray-900 min-h-screen flex items-center justify-center">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login</title>
  <link rel="stylesheet" href="../public/css/login.css">
</head>
<body>
  <main class="bg-gradient-to-r from-blue-900 via-blue-800 to-blue-900 p-8 rounded-lg shadow-lg w-full max-w-md text-white">
    <h2 class="text-3xl font-semibold mb-6 text-center">Iniciar Sesión</h2>

    <?php if (!empty($error)): ?>
      <p class="bg-red-700 text-red-100 p-3 rounded mb-6 text-center"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="../src/controllers/procesar_login.php" class="space-y-6">
      <div>
        <label for="nombre" class="block mb-2 font-medium">Nombre de usuario</label>
        <input 
          id="nombre" 
          name="nombre" 
          type="text" 
          required
          class="w-full p-3 rounded bg-blue-700 text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-400"
          placeholder="Tu nombre de usuario"
        />
      </div>

      <div>
        <label for="contrasena" class="block mb-2 font-medium">Contraseña</label>
        <input 
          id="contrasena" 
          name="contrasena" 
          type="password" 
          required
          class="w-full p-3 rounded bg-blue-700 text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-400"
          placeholder="••••••••"
        />
      </div>

      <button 
        type="submit" 
        class="w-full bg-blue-600 hover:bg-blue-700 transition-colors p-3 rounded font-semibold text-lg"
      >
        Ingresar
      </button>
    </form>
  </main>
</body>
</html>
