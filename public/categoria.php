<?php

require_once __DIR__ . '/../src/controllers/categoria_controller.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>CRUD de Categorías</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    a, button { margin-right: 8px; }
    form { margin-bottom: 20px; }
    label { display: block; margin-top: 8px; }
  </style>
</head>
<body>

  <h1>CRUD de Categorías</h1>

  <?php if ($action === 'crear'): ?>
    <h2>➕ Agregar Nueva Categoría</h2>
    <form action="categoria.php?action=guardar" method="post">
      <label>Nombre:
        <input type="text" name="nombre" required>
      </label>
      <label>Icono:
        <input type="text" name="icono">
      </label>
      <label>Descripción:
        <textarea name="descripcion" rows="3"></textarea>
      </label>
      <button type="submit">Guardar</button>
      <a href="categoria.php">Cancelar</a>
    </form>

  <?php elseif ($action === 'editar' && isset($categoria)): ?>
    <h2>✏️ Editar Categoría: <?= htmlspecialchars($categoria['nombre']) ?></h2>
    <form action="categoria.php?action=actualizar&id=<?= $categoria['id'] ?>" method="post">
      <input type="hidden" name="id" value="<?= $categoria['id'] ?>">
      <label>Nombre:
        <input type="text" name="nombre" value="<?= htmlspecialchars($categoria['nombre']) ?>" required>
      </label>
      <label>Icono:
        <input type="text" name="icono" value="<?= htmlspecialchars($categoria['icono']) ?>">
      </label>
      <label>Descripción:
        <textarea name="descripcion" rows="3"><?= htmlspecialchars($categoria['descripcion']) ?></textarea>
      </label>
      <button type="submit">Actualizar</button>
      <a href="categoria.php">Cancelar</a>
    </form>
  <?php endif; ?>

 <h2>📋 Lista de Categorías</h2>
<a href="categoria.php?action=crear">+ Agregar Categoría</a>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
  <?php foreach ($categorias as $c): ?>
    <div class="bg-white p-4 rounded shadow">
      <div class="flex items-center mb-2">
        <?php if (!empty($c['icono'])): ?>
          <span class="text-2xl mr-2"><?= htmlspecialchars($c['icono']) ?></span>
        <?php endif; ?>
        <h3 class="text-xl font-semibold"><?= htmlspecialchars($c['nombre']) ?></h3>
      </div>

      <?php if (!empty($c['descripcion'])): ?>
        <p class="text-gray-600 mb-2"><?= htmlspecialchars($c['descripcion']) ?></p>
      <?php endif; ?>

      <!-- Aquí está el contador de noticias -->
      <p class="text-sm text-gray-500 mb-4">
        <?= $model->contadorNoticias((int)$c['id']) ?> noticias
      </p>

      <div class="flex gap-2">
        <a
          href="categoria.php?action=editar&id=<?= $c['id'] ?>"
          class="px-3 py-1 bg-blue-500 text-white rounded text-sm"
        >✏️ Editar</a>
        <a
          href="categoria.php?action=deshabilitar&id=<?= $c['id'] ?>"
          onclick="return confirm('¿Deshabilitar esta categoría?')"
          class="px-3 py-1 bg-red-500 text-white rounded text-sm"
        >🚫 Deshabilitar</a>
      </div>
    </div>
  <?php endforeach; ?>
</div>


</body>
</html>
