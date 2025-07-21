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

$mensaje_exito = $_SESSION['mensaje_exito'] ?? '';
$mensaje_error = $_SESSION['mensaje_error'] ?? '';

unset($_SESSION['mensaje_exito']);
unset($_SESSION['mensaje_error']);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Sistema de Noticias</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #cbd5e1 100%);
            min-height: 100vh;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .user-card {
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .user-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        
        .status-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
        }
        
        .status-active {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border: 1px solid #34d399;
            color: #065f46;
        }
        
        .alert-error {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border: 1px solid #f87171;
            color: #991b1b;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        .table-responsive {
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <?php include './header.php'; ?>

    <div class="container mx-auto mt-4 p-4 max-w-7xl">
        <!-- Header Section -->
        <div class="glass-card rounded-2xl p-6 mb-6">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">👥 Gestión de Usuarios</h1>
                    <p class="text-gray-600">Administra y controla todos los usuarios del sistema</p>
                </div>
                <a href="form_usuario.php" 
                   class="inline-flex items-center px-6 py-3 btn-primary text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                    <span class="mr-2">➕</span>
                    Nuevo Usuario
                </a>
            </div>
        </div>

        <!-- Mensajes de Estado -->
        <?php if ($mensaje_exito): ?>
            <div class="alert-success rounded-xl p-4 mb-6 flex items-center">
                <span class="text-2xl mr-3">✅</span>
                <span class="font-medium"><?= htmlspecialchars($mensaje_exito) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($mensaje_error): ?>
            <div class="alert-error rounded-xl p-4 mb-6 flex items-center">
                <span class="text-2xl mr-3">❌</span>
                <span class="font-medium"><?= htmlspecialchars($mensaje_error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="glass-card rounded-xl p-6">
                <div class="flex items-center">
                    <div class="text-4xl mr-4">👤</div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Usuarios</p>
                        <p class="text-2xl font-bold text-gray-900"><?= count($usuarios) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="glass-card rounded-xl p-6">
                <div class="flex items-center">
                    <div class="text-4xl mr-4">🟢</div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Usuarios Activos</p>
                        <p class="text-2xl font-bold text-green-600">
                            <?= count(array_filter($usuarios, fn($u) => $u['id_estado'] == 1)) ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="glass-card rounded-xl p-6">
                <div class="flex items-center">
                    <div class="text-4xl mr-4">🔴</div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Usuarios Inactivos</p>
                        <p class="text-2xl font-bold text-red-600">
                            <?= count(array_filter($usuarios, fn($u) => $u['id_estado'] == 0)) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Usuarios -->
        <div class="glass-card rounded-2xl p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                    <span class="mr-3 text-3xl">📋</span>
                    Lista de Usuarios
                </h2>
            </div>

            <?php if (empty($usuarios)): ?>
                <div class="text-center py-12">
                    <div class="text-6xl mb-4">👥</div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No hay usuarios disponibles</h3>
                    <p class="text-gray-500 mb-6">Comienza agregando el primer usuario</p>
                    <a href="form_usuario.php" class="btn-primary text-white px-6 py-3 rounded-lg font-semibold">
                        + Crear Primer Usuario
                    </a>
                </div>
            <?php else: ?>
                <!-- Vista de Tabla (Desktop) -->
                <div class="hidden lg:block table-responsive">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado por</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Creación</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($usuarios as $index => $u): ?>
                                <tr class="hover:bg-gray-50 animate-fade-in-up" style="animation-delay: <?= $index * 0.05 ?>s;">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <span class="text-blue-600 font-semibold text-sm">
                                                        <?= strtoupper(substr($u['nombre'], 0, 2)) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($u['nombre']) ?></div>
                                                <div class="text-sm text-gray-500"><?= htmlspecialchars($u['correo']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            <?= htmlspecialchars($u['rol'] ?? 'Sin rol') ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="<?= (int)$u['id_estado'] === 1 ? 'status-active' : 'status-inactive' ?> status-badge">
                                            <?= (int)$u['id_estado'] === 1 ? '🟢 Activo' : '🔴 Inactivo' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= htmlspecialchars($u['creado_por'] ?? '—') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= !empty($u['creado_en']) ? date('d/m/Y H:i', strtotime($u['creado_en'])) : '—' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="form_usuario.php?id=<?= $u['id'] ?>"
                                           class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 transition-colors">
                                            ✏️ Editar
                                        </a>

                                        <form method="POST" action="../src/controllers/usuario_controller.php" class="inline">
                                            <input type="hidden" name="Accion" value="CambiarEstado">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <input type="hidden" name="nuevo_estado" value="<?= ($u['id_estado'] == 1) ? 0 : 1 ?>">

                                            <button type="submit"
                                                    onclick="return confirm('¿Seguro que quieres <?= ($u['id_estado'] == 1) ? 'desactivar' : 'activar' ?> este usuario?');"
                                                    class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white <?= ($u['id_estado'] == 1) ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' ?> transition-colors">
                                                <?= ($u['id_estado'] == 1) ? '🚫 Desactivar' : '✅ Activar' ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Vista de Tarjetas (Mobile/Tablet) -->
                <div class="lg:hidden grid gap-4">
                    <?php foreach ($usuarios as $index => $u): ?>
                        <div class="user-card glass-card rounded-xl p-6 animate-fade-in-up" style="animation-delay: <?= $index * 0.05 ?>s;">
                            <!-- Header -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                                        <span class="text-blue-600 font-semibold">
                                            <?= strtoupper(substr($u['nombre'], 0, 2)) ?>
                                        </span>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($u['nombre']) ?></h3>
                                        <p class="text-sm text-gray-500"><?= htmlspecialchars($u['correo']) ?></p>
                                    </div>
                                </div>
                                <span class="<?= (int)$u['id_estado'] === 1 ? 'status-active' : 'status-inactive' ?> status-badge">
                                    <?= (int)$u['id_estado'] === 1 ? '🟢 Activo' : '🔴 Inactivo' ?>
                                </span>
                            </div>

                            <!-- Detalles -->
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Rol:</span>
                                    <span class="text-sm font-medium"><?= htmlspecialchars($u['rol'] ?? 'Sin rol') ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Creado por:</span>
                                    <span class="text-sm font-medium"><?= htmlspecialchars($u['creado_por'] ?? '—') ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Fecha:</span>
                                    <span class="text-sm font-medium">
                                        <?= !empty($u['creado_en']) ? date('d/m/Y H:i', strtotime($u['creado_en'])) : '—' ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="flex gap-2">
                                <a href="form_usuario.php?id=<?= $u['id'] ?>"
                                   class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-center py-2 px-3 rounded-lg text-sm font-semibold transition-colors">
                                    ✏️ Editar
                                </a>

                                <form method="POST" action="../src/controllers/usuario_controller.php" class="flex-1">
                                    <input type="hidden" name="Accion" value="CambiarEstado">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="nuevo_estado" value="<?= ($u['id_estado'] == 1) ? 0 : 1 ?>">

                                    <button type="submit"
                                            onclick="return confirm('¿Seguro que quieres <?= ($u['id_estado'] == 1) ? 'desactivar' : 'activar' ?> este usuario?');"
                                            class="w-full <?= ($u['id_estado'] == 1) ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' ?> text-white py-2 px-3 rounded-lg text-sm font-semibold transition-colors">
                                        <?= ($u['id_estado'] == 1) ? '🚫 Desactivar' : '✅ Activar' ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
