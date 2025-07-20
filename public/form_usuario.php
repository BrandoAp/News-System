<?php
require_once '../db/DatabaseManager.php';
require_once '../db/conexionDB.php';

$db = ConexionDB::obtenerInstancia()->obtenerConexion();
$gestor = new DatabaseManager($db);
$roles = $gestor->select('roles', '*');
// Filtrar para eliminar el rol "lector"
$roles = array_filter($roles, function($rol) {
    return strtolower($rol['nombre']) !== 'lector';
});
$id = $_GET['id'] ?? null;
$modo_edicion = false;
$usuario = [];

if ($id) {
    $modo_edicion = true;
    $usuarios = $gestor->select('usuarios', '*', ['id' => $id]);
    $usuario = $usuarios[0] ?? [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $modo_edicion ? 'Editar Usuario' : 'Nuevo Usuario' ?></title>
    <link rel="stylesheet" href="../public/css/formulario_usuario.css">
    <style>
        
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
        <h2 class="text-3xl font-bold mb-6 text-center text-blue-800">
            <?= $modo_edicion ? 'Editar Usuario' : 'Registrar Nuevo Usuario' ?>
        </h2>

        <form action="../src/controllers/usuario_controller.php" method="POST" class="space-y-5">
            <input type="hidden" name="Accion" value="<?= $modo_edicion ? 'Modificar' : 'Guardar' ?>">
            <?php if ($modo_edicion): ?>
                <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
            <?php endif; ?>

            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                <input type="text" name="nombre" id="nombre" required
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                       value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>">
            </div>

            <div>
                <label for="correo" class="block text-sm font-medium text-gray-700 mb-1">Correo</label>
                <input type="email" name="correo" id="correo" required
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                       value="<?= htmlspecialchars($usuario['correo'] ?? '') ?>">
            </div>

            <div>
                <label for="contrasena" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <input type="password" name="contrasena" id="contrasena"
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                       placeholder="<?= $modo_edicion ? 'Dejar en blanco para no cambiar' : '' ?>">
                <?php if ($modo_edicion): ?>
                   
                <?php endif; ?>
            </div>

            <div>
                <label for="id_rol" class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                <select name="id_rol" id="id_rol" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="" disabled <?= empty($usuario['id_rol']) ? 'selected' : '' ?>>Seleccione un rol</option>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?= $rol['id'] ?>" <?= ($usuario['id_rol'] ?? '') == $rol['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($rol['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>



            <div class="flex justify-end space-x-4 mt-6">
                <a href="/News-System/public/registrar_usuario.php" class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <?= $modo_edicion ? 'Actualizar' : 'Guardar' ?>
                </button>
            </div>
        </form>
    </div>
</body>
</html>