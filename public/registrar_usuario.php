<?php
require_once '../db/DatabaseManager.php';
require_once '../db/conexionDB.php';

$db = ConexionDB::obtenerInstancia()->obtenerConexion();
$gestor = new DatabaseManager($db);
$usuarios = $gestor->select('usuarios', '*');

// Recuperar mensajes de la sesión
$mensaje_exito = $_SESSION['mensaje_exito'] ?? '';
$mensaje_error = $_SESSION['mensaje_error'] ?? '';
$errores_registro = $_SESSION['errores_registro'] ?? [];
$datos_formulario = $_SESSION['datos_formulario'] ?? [];

unset($_SESSION['mensaje_exito']);
unset($_SESSION['mensaje_error']);
unset($_SESSION['errores_registro']);
unset($_SESSION['datos_formulario']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Usuarios</title>
  <link rel="stylesheet" href="../public/css/usuarios.css">
</head>
<body class="bg-gray-100 min-h-screen">
  <?php include './header.php'; ?>

  <main class="p-6 max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow p-6">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Gestión de Usuarios</h2>
        <a href="form_usuario.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded flex items-center gap-2">
          <span>➕</span> <span>Agregar Usuario</span>
        </a>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full table-auto text-sm text-gray-700">
          <thead class="bg-gray-100 text-xs text-gray-600 uppercase">
            <tr>
              <th class="px-4 py-2 text-left">ID</th>
              <th class="px-4 py-2 text-left">Nombre</th>
              <th class="px-4 py-2 text-left">Email</th>
              <th class="px-4 py-2 text-left">Rol</th>
              <th class="px-4 py-2 text-left">Estado</th>
              <th class="px-4 py-2 text-left">Acciones</th>
            </tr>
          </thead>
          <tbody id="tablaUsuarios">
            <?php foreach ($usuarios as $index => $u): ?>
              <tr class="border-b hover:bg-gray-50 animate-fade-in-up"
                  style="border-bottom: 1px solid #d1d5db; animation-delay: <?= $index * 0.05 ?>s;">
                <td class="px-4 py-2"><?= htmlspecialchars($u['id']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($u['nombre']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($u['correo']) ?></td>
                <td class="px-4 py-2 capitalize"><?= htmlspecialchars($u['rol']) ?></td>
                <td class="px-4 py-2">
                  <?php if ($u['id_estado'] == 1): ?>
                    <span class="text-xs px-2 py-1 bg-green-200 text-green-800 rounded-full">Activo</span>
                  <?php else: ?>
                    <span class="text-xs px-2 py-1 bg-red-200 text-red-800 rounded-full">Inactivo</span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-2 flex gap-2">
                  <a href="form_usuario.php?id=<?= $u['id'] ?>"
                     class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">
                    Editar
                  </a>
                  <?php if ($u['id_estado'] == 1): ?>
                    <button onclick="cambiarEstadoUsuario(<?= $u['id'] ?>, 0)"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs">
                      Desactivar
                    </button>
                  <?php else: ?>
                    <button onclick="cambiarEstadoUsuario(<?= $u['id'] ?>, 1)"
                            class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded text-xs">
                      Activar
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../src/js/usuarios.js"></script>
</body>
</html>
