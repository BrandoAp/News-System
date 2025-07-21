<?php

session_start();
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Login</title>
  <link rel="stylesheet" href="../public/css/login.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
  <main class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8 flex flex-col items-center">
    <h2 class="text-2xl md:text-3xl font-bold text-blue-800 mb-6 text-center">Iniciar sesión como Administrador</h2>

    <?php if (!empty($error)): ?>
      <div class="bg-red-100 text-red-700 border border-red-200 p-3 rounded mb-6 w-full text-center font-medium"
           style="color:#b91c1c !important; border-color:#fecaca !important; background:#fee2e2 !important;">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="../src/controllers/procesar_login.php" class="w-full space-y-5">
      <div>
        <input 
          id="nombre" 
          name="nombre" 
          type="text" 
          required
          class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-800 placeholder-gray-400 transition"
          placeholder="Correo electrónico"
        />
      </div>

      <div>
        <input 
          id="contrasena" 
          name="contrasena" 
          type="password" 
          required
          class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-800 placeholder-gray-400 transition"
          placeholder="Contraseña"
        />
      </div>

      <button 
        type="submit" 
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-full transition text-base shadow"
      >
        Iniciar sesión
      </button>
    </form>

    <div class="mt-6 w-full flex flex-col items-center gap-1 text-sm">
      <a href="registro.php" class="text-blue-600 hover:underline">¿No tienes cuenta? Regístrate</a>
      <a href="index.php" class="text-blue-500 hover:underline">Volver al inicio</a>
    </div>
  </main>
</body>
</html>
