<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header class="bg-gradient-to-r from-blue-600 to-purple-700 text-white p-4 flex justify-between items-center">
  <h1 class="text-xl font-semibold flex items-center gap-2">📰 Sistema de Noticias</h1>
  <div class="flex gap-3 items-center">
    <?php if (isset($_SESSION['usuario_nombre'])): ?>
      <span class="text-sm">👤 <?= htmlspecialchars($_SESSION['usuario_nombre']) ?> (<?= htmlspecialchars($_SESSION['usuario_rol']) ?>)</span>
      <a href="logout.php" class="bg-white text-blue-600 px-3 py-1 rounded hover:bg-gray-200 text-sm">Cerrar Sesión</a>
    <?php else: ?>
      <a href="login.php" class="bg-white text-blue-600 px-3 py-1 rounded hover:bg-gray-200 text-sm">Iniciar Sesión</a>
    <?php endif; ?>
  </div>
</header>

<nav class="bg-white shadow-md px-6 py-3 flex gap-4 text-sm font-semibold">
  <a href="dashboard.php" class="text-gray-600 hover:text-blue-600 flex items-center gap-1">🏠 Dashboard</a>
    <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
    <a href="./categoria.php" class="text-gray-600 hover:text-blue-600 flex items-center gap-1">Categorías</a>
  <?php endif; ?>

  <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
    <a href="./registrar_usuario.php" class="text-white bg-blue-600 px-3 py-1 rounded flex items-center gap-1"> Usuarios</a>
  <?php endif; ?>

  <?php if (isset($_SESSION['usuario_rol']) && in_array($_SESSION['usuario_rol'], ['admin', 'editor', 'supervisor'])): ?>
    <a href="./indexnoticia.php" class="text-gray-600 hover:text-blue-600 flex items-center gap-1"> Noticias</a>
  <?php endif; ?>
</nav>
