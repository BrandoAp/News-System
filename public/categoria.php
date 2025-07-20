<?php

require_once __DIR__ . '/../src/controllers/categoria_controller.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>CRUD de Categorías</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/public/css/categoria.css">
</head>
 <?php include './header.php'; ?>
<body class="p-6 bg-gray-100 font-sans">

  <h1 class="text-4xl font-extrabold mb-8 text-blue-900">🛠️ Administración de Categorías</h1>

  <?php if ($action === 'crear'): ?>
    <div class="bg-white p-8 rounded-lg shadow-lg mb-8">
        <h2 class="text-3xl font-bold mb-6 text-blue-800">➕ Agregar Nueva Categoría</h2>
        <form action="categoria.php?action=guardar" method="post" class="space-y-4">
            <div>
                <label for="nombre" class="block text-gray-800 font-semibold mb-1">Nombre:</label>
                <input type="text" id="nombre" name="nombre" class="border border-gray-300 rounded-md p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="icono" class="block text-gray-800 font-semibold mb-1 mt-4">Icono:</label>
                <input type="text" id="icono" name="icono" class="border border-gray-300 rounded-md p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="descripcion" class="block text-gray-800 font-semibold mb-1 mt-4">Descripción:</label>
                <textarea id="descripcion" name="descripcion" rows="3" class="border border-gray-300 rounded-md p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="flex space-x-4 mt-6">
                <button type="submit" class="bg-blue-900 text-white px-5 py-2 rounded-md hover:bg-blue-800 transition duration-200">Guardar</button>
                <a href="categoria.php" class="bg-gray-600 text-white px-5 py-2 rounded-md hover:bg-gray-700 transition duration-200">Cancelar</a>
            </div>
        </form>
    </div>

  <?php elseif ($action === 'editar' && isset($categoria)): ?>
    <div class="bg-white p-8 rounded-lg shadow-lg mb-8">
        <h2 class="text-3xl font-bold mb-6 text-blue-800">✏️ Editar Categoría: <span class="text-blue-700"><?= htmlspecialchars($categoria['nombre']) ?></span></h2>
        <form action="categoria.php?action=actualizar&id=<?= $categoria['id'] ?>" method="post" class="space-y-4">
            <input type="hidden" name="id" value="<?= $categoria['id'] ?>">
            <div>
                <label for="nombre" class="block text-gray-800 font-semibold mb-1">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($categoria['nombre']) ?>" class="border border-gray-300 rounded-md p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="icono" class="block text-gray-800 font-semibold mb-1 mt-4">Icono:</label>
                <input type="text" id="icono" name="icono" value="<?= htmlspecialchars($categoria['icono']) ?>" class="border border-gray-300 rounded-md p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="descripcion" class="block text-gray-800 font-semibold mb-1 mt-4">Descripción:</label>
                <textarea id="descripcion" name="descripcion" rows="3" class="border border-gray-300 rounded-md p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($categoria['descripcion']) ?></textarea>
            </div>
            <div class="flex space-x-4 mt-6">
                <button type="submit" class="bg-blue-900 text-white px-5 py-2 rounded-md hover:bg-blue-800 transition duration-200">Actualizar</button>
                <a href="categoria.php" class="bg-gray-600 text-white px-5 py-2 rounded-md hover:bg-gray-700 transition duration-200">Cancelar</a>
            </div>
        </form>
    </div>
  <?php endif; ?>

  <h2 class="text-3xl font-bold mb-6 text-blue-800">📋 Lista de Categorías</h2>
  <div class="mb-6">
    <a href="categoria.php?action=crear" class="bg-blue-900 text-white px-5 py-2 rounded-md hover:bg-blue-800 transition duration-200 inline-flex items-center">
        + Agregar Categoría
    </a>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($categorias as $c): ?>
      <div class="bg-white p-6 rounded-lg shadow-lg">
        <div class="flex items-center mb-3">
          <?php if (!empty($c['icono'])): ?>
            <span class="text-4xl mr-3 text-blue-700"><?= htmlspecialchars($c['icono']) ?></span>
          <?php endif; ?>
          <h3 class="text-2xl font-bold text-blue-800"><?= htmlspecialchars($c['nombre']) ?></h3>
        </div>

        <?php if (!empty($c['descripcion'])): ?>
          <p class="text-gray-700 mb-4 text-base leading-relaxed"><?= htmlspecialchars($c['descripcion']) ?></p>
        <?php endif; ?>

        <p class="text-sm text-gray-500 mb-5 italic">
          <?= $model->contadorNoticias((int)$c['id']) ?> noticias asociadas
        </p>

        <div class="flex gap-3">
          <a
            href="categoria.php?action=editar&id=<?= $c['id'] ?>"
            class="bg-blue-600 text-white text-sm font-semibold rounded-md px-4 py-2 hover:bg-blue-700 transition duration-200 shadow-md"
          >✏️ Editar</a>
          <a
            href="categoria.php?action=deshabilitar&id=<?= $c['id'] ?>"
            onclick="return confirm('¿Estás seguro de que quieres deshabilitar esta categoría? ¡Esta acción no se puede deshacer fácilmente!')"
            class="bg-red-600 text-white text-sm font-semibold rounded-md px-4 py-2 hover:bg-red-700 transition duration-200 shadow-md"
          >🚫 Deshabilitar</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

</body>
</html>