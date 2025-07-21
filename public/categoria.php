<?php
// public/categoria.php
session_start();
require_once __DIR__ . '/../src/controllers/categoria_controller.php';

// recoger errores y datos anteriores de la sesión
$errors = $_SESSION['errors_cat'] ?? [];
$old    = $_SESSION['old_cat']    ?? [];
unset($_SESSION['errors_cat'], $_SESSION['old_cat']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>CRUD de Categorías</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/public/css/categoria.css">
</head>
<body class="p-6 bg-gray-100 font-sans">
  <?php include __DIR__ . '/header.php'; ?>

  <h1 class="text-4xl font-extrabold mb-8 text-blue-900">🛠️ Administración de Categorías</h1>

  <?php if ($action === 'crear'): ?>
    <div class="bg-white p-8 rounded-lg shadow-lg mb-8">
      <h2 class="text-3xl font-bold mb-6 text-blue-800">➕ Agregar Nueva Categoría</h2>

      <form action="categoria.php?action=guardar" method="post" class="space-y-4">
        <div>
          <label for="nombre" class="block text-gray-800 font-semibold mb-1">Nombre:</label>
          <input
            type="text"
            id="nombre"
            name="nombre"
            value="<?= htmlspecialchars($old['nombre'] ?? '') ?>"
            class="border border-gray-300 rounded-md p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          >
          <?php if (isset($errors['nombre'])): ?>
            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($errors['nombre']) ?></p>
          <?php endif; ?>
        </div>

        <div>
          <label for="icono" class="block text-gray-800 font-semibold mb-1 mt-4">Icono:</label>
          <input
            type="text"
            id="icono"
            name="icono"
            value="<?= htmlspecialchars($old['icono'] ?? '') ?>"
            class="border border-gray-300 rounded-md p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
        </div>

        <div>
          <label for="descripcion" class="block text-gray-800 font-semibold mb-1 mt-4">Descripción:</label>
          <textarea
            id="descripcion"
            name="descripcion"
            rows="3"
            class="border border-gray-300 rounded-md p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
          ><?= htmlspecialchars($old['descripcion'] ?? '') ?></textarea>
          <?php if (isset($errors['descripcion'])): ?>
            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($errors['descripcion']) ?></p>
          <?php endif; ?>
        </div>

        <div class="flex space-x-4 mt-6">
          <button
            type="submit"
            class="bg-blue-900 text-white px-5 py-2 rounded-md hover:bg-blue-800 transition duration-200"
          >Guardar</button>
          <a
            href="categoria.php"
            class="bg-gray-600 text-white px-5 py-2 rounded-md hover:bg-gray-700 transition duration-200"
          >Cancelar</a>
        </div>
      </form>
    </div>

  <?php elseif ($action === 'editar' && isset($categoria)): ?>
    <div class="bg-white p-8 rounded-lg shadow-lg mb-8">
      <h2 class="text-3xl font-bold mb-6 text-blue-800">
        ✏️ Editar Categoría: <span class="text-blue-700"><?= htmlspecialchars($categoria['nombre']) ?></span>
      </h2>

      <form action="categoria.php?action=actualizar&id=<?= $categoria['id'] ?>" method="post" class="space-y-4">
        <input type="hidden" name="id" value="<?= $categoria['id'] ?>">

        <div>
          <label for="nombre" class="block text-gray-800 font-semibold mb-1">Nombre:</label>
          <input
            type="text"
            id="nombre"
            name="nombre"
            value="<?= htmlspecialchars($old['nombre'] ?? $categoria['nombre']) ?>"
            class="border border-gray-300 rounded-md p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          >
          <?php if (isset($errors['nombre'])): ?>
            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($errors['nombre']) ?></p>
          <?php endif; ?>
        </div>

        <div>
          <label for="icono" class="block text-gray-800 font-semibold mb-1 mt-4">Icono:</label>
          <input
            type="text"
            id="icono"
            name="icono"
            value="<?= htmlspecialchars($old['icono'] ?? $categoria['icono']) ?>"
            class="border border-gray-300 rounded-md p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
        </div>

        <div>
          <label for="descripcion" class="block text-gray-800 font-semibold mb-1 mt-4">Descripción:</label>
          <textarea
            id="descripcion"
            name="descripcion"
            rows="3"
            class="border border-gray-300 rounded-md p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
          ><?= htmlspecialchars($old['descripcion'] ?? $categoria['descripcion']) ?></textarea>
          <?php if (isset($errors['descripcion'])): ?>
            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($errors['descripcion']) ?></p>
          <?php endif; ?>
        </div>

        <div class="flex space-x-4 mt-6">
          <button
            type="submit"
            class="bg-blue-900 text-white px-5 py-2 rounded-md hover:bg-blue-800 transition duration-200"
          >Actualizar</button>
          <a
            href="categoria.php"
            class="bg-gray-600 text-white px-5 py-2 rounded-md hover:bg-gray-700 transition duration-200"
          >Cancelar</a>
        </div>
      </form>
    </div>
  <?php endif; ?>

  <h2 class="text-3xl font-bold mb-6 text-blue-800">📋 Lista de Categorías</h2>
  <div class="mb-6">
    <a
      href="categoria.php?action=crear"
      class="bg-blue-900 text-white px-5 py-2 rounded-md hover:bg-blue-800 transition duration-200 inline-flex items-center"
    >+ Agregar Categoría</a>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($categorias as $c): ?>
      <div class="bg-white p-6 rounded-lg shadow-lg">
        <div class="flex items-center mb-4">
          <?php if (!empty($c['icono'])): ?>
            <span class="text-4xl mr-3 text-blue-700"><?= htmlspecialchars($c['icono']) ?></span>
          <?php endif; ?>
          <h3 class="text-2xl font-bold text-blue-800"><?= htmlspecialchars($c['nombre']) ?></h3>
        </div>

        <?php if (!empty($c['descripcion'])): ?>
          <p class="text-gray-700 mb-4"><?= htmlspecialchars($c['descripcion']) ?></p>
        <?php endif; ?>

        <p class="text-sm text-gray-500 italic mb-6">
          <?= $model->contadorNoticias((int)$c['id']) ?> noticias asociadas
        </p>

        <div class="flex gap-3">
          <a
            href="categoria.php?action=editar&id=<?= $c['id'] ?>"
            class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm"
          >✏️ Editar</a>

          <?php if ((int)$c['id_estado'] === 1): ?>
            <a
              href="categoria.php?action=deshabilitar&id=<?= $c['id'] ?>"
              onclick="return confirm('¿Seguro que quieres deshabilitar esta categoría?');"
              class="bg-red-600 text-white px-4 py-2 rounded-md text-sm"
            >🚫 Deshabilitar</a>
          <?php else: ?>
            <a
              href="categoria.php?action=activar&id=<?= $c['id'] ?>"
              onclick="return confirm('¿Seguro que quieres activar esta categoría?');"
              class="bg-green-600 text-white px-4 py-2 rounded-md text-sm"
            >✅ Activar</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
