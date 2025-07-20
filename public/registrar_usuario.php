<?php 
// Recuperar mensajes de la sesión
session_start();
require_once '../db/DatabaseManager.php';
require_once '../db/conexionDB.php';
require_once '../src/modules/usuario.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$db = ConexionDB::obtenerInstancia()->obtenerConexion();
$gestor = new DatabaseManager($db);
$usuarios = Usuario::obtenerUsuariosConDetalles(); 

// Capturar mensajes para mostrar en la página
$mensaje_exito = $_SESSION['mensaje_exito'] ?? '';
$mensaje_error = $_SESSION['mensaje_error'] ?? '';

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

      <!-- Mostrar mensajes -->
      <?php if ($mensaje_exito): ?>
        <div style="background-color:#d1fae5; color:#065f46; padding:10px; margin-bottom:15px; border-radius:5px;">
          <?= htmlspecialchars($mensaje_exito) ?>
        </div>
      <?php endif; ?>
      <?php if ($mensaje_error): ?>
        <div style="background-color:#fee2e2; color:#991b1b; padding:10px; margin-bottom:15px; border-radius:5px;">
          <?= htmlspecialchars($mensaje_error) ?>
        </div>
      <?php endif; ?>

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
              <th class="px-4 py-2 text-left">Creado por</th>
              <th class="px-4 py-2 text-left">Fecha de creación</th>
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
                <td class="px-4 py-2"><?= htmlspecialchars($u['rol'] ?? '—') ?></td>
                <td class="px-4 py-2">
                  <?php if ($u['id_estado'] == 1): ?>
                    <span class="text-xs px-2 py-1 bg-green-200 text-green-800 rounded-full">Activo</span>
                  <?php else: ?>
                    <span class="text-xs px-2 py-1 bg-red-200 text-red-800 rounded-full">Inactivo</span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-2"><?= htmlspecialchars($u['creado_por'] ?? '—') ?></td>
                <td class="px-4 py-2"><?= !empty($u['creado_en']) ? date('d/m/Y H:i', strtotime($u['creado_en'])) : '—' ?></td>
                <td class="px-4 py-2 flex gap-2">
                  <a href="form_usuario.php?id=<?= $u['id'] ?>"
                     class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">
                    Editar
                  </a>

                  <form method="POST" action="..//src/controllers/usuario_controller.php" style="display:inline;">
                    <input type="hidden" name="Accion" value="CambiarEstado">
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <input type="hidden" name="nuevo_estado" value="<?= ($u['id_estado'] == 1) ? 0 : 1 ?>">

                    <button type="submit"
                            onclick="return confirm('¿Seguro que quieres <?= ($u['id_estado'] == 1) ? 'desactivar' : 'activar' ?> este usuario?');"
                            class="<?= ($u['id_estado'] == 1) ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-purple-500 hover:bg-purple-600' ?> text-white px-3 py-1 rounded text-xs">
                      <?= ($u['id_estado'] == 1) ? 'Desactivar' : 'Activar' ?>
                    </button>
                  </form>

                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</body>
</html>
