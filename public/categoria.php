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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías - Sistema de Noticias</title>
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
        
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }
        
        .category-card {
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .category-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .search-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }
        
        .form-input {
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
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
    </style>
</head>
<body>
    <?php include __DIR__ . '/header.php'; ?>
    
    <div class="container mx-auto mt-4 p-4 max-w-7xl">
        <!-- Header Section -->
        <div class="glass-card rounded-2xl p-6 mb-6">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">📂 Gestión de Categorías</h1>
                    <p class="text-gray-600">Administra y controla todas las categorías del sistema</p>
                </div>
                <a href="categoria.php?action=crear" 
                   class="inline-flex items-center px-6 py-3 btn-primary text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                    <span class="mr-2">+</span>
                    Nueva Categoría
                </a>
            </div>
        </div>

        <!-- Formulario de Crear/Editar -->
        <?php if ($action === 'crear'): ?>
            <div class="glass-card rounded-2xl p-8 mb-8">
                <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
                    <span class="mr-3 text-3xl">➕</span>
                    Agregar Nueva Categoría
                </h2>

                <form action="categoria.php?action=guardar" method="post" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-2">Nombre de la Categoría *</label>
                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                value="<?= htmlspecialchars($old['nombre'] ?? '') ?>"
                                class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Ej: Tecnología, Deportes, Política..."
                                required
                            >
                            <?php if (isset($errors['nombre'])): ?>
                                <p class="text-red-600 text-sm mt-2 flex items-center">
                                    <span class="mr-1">⚠️</span>
                                    <?= htmlspecialchars($errors['nombre']) ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="icono" class="block text-sm font-semibold text-gray-700 mb-2">Icono (Emoji)</label>
                            <input
                                type="text"
                                id="icono"
                                name="icono"
                                value="<?= htmlspecialchars($old['icono'] ?? '') ?>"
                                class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="📱 💼 ⚽ 🎭 🔬"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="descripcion" class="block text-sm font-semibold text-gray-700 mb-2">Descripción</label>
                        <textarea
                            id="descripcion"
                            name="descripcion"
                            rows="4"
                            class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            placeholder="Descripción breve de la categoría..."
                        ><?= htmlspecialchars($old['descripcion'] ?? '') ?></textarea>
                        <?php if (isset($errors['descripcion'])): ?>
                            <p class="text-red-600 text-sm mt-2 flex items-center">
                                <span class="mr-1">⚠️</span>
                                <?= htmlspecialchars($errors['descripcion']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button
                            type="submit"
                            class="btn-primary text-white px-8 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200"
                        >
                            💾 Guardar Categoría
                        </button>
                        <a
                            href="categoria.php"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-lg font-semibold transition-colors duration-200"
                        >
                            ❌ Cancelar
                        </a>
                    </div>
                </form>
            </div>

        <?php elseif ($action === 'editar' && isset($categoria)): ?>
            <div class="glass-card rounded-2xl p-8 mb-8">
                <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
                    <span class="mr-3 text-3xl">✏️</span>
                    Editar Categoría: 
                    <span class="text-blue-600 ml-2"><?= htmlspecialchars($categoria['nombre']) ?></span>
                </h2>

                <form action="categoria.php?action=actualizar&id=<?= $categoria['id'] ?>" method="post" class="space-y-6">
                    <input type="hidden" name="id" value="<?= $categoria['id'] ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-2">Nombre de la Categoría *</label>
                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                value="<?= htmlspecialchars($old['nombre'] ?? $categoria['nombre']) ?>"
                                class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required
                            >
                            <?php if (isset($errors['nombre'])): ?>
                                <p class="text-red-600 text-sm mt-2 flex items-center">
                                    <span class="mr-1">⚠️</span>
                                    <?= htmlspecialchars($errors['nombre']) ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="icono" class="block text-sm font-semibold text-gray-700 mb-2">Icono (Emoji)</label>
                            <input
                                type="text"
                                id="icono"
                                name="icono"
                                value="<?= htmlspecialchars($old['icono'] ?? $categoria['icono']) ?>"
                                class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="descripcion" class="block text-sm font-semibold text-gray-700 mb-2">Descripción</label>
                        <textarea
                            id="descripcion"
                            name="descripcion"
                            rows="4"
                            class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        ><?= htmlspecialchars($old['descripcion'] ?? $categoria['descripcion']) ?></textarea>
                        <?php if (isset($errors['descripcion'])): ?>
                            <p class="text-red-600 text-sm mt-2 flex items-center">
                                <span class="mr-1">⚠️</span>
                                <?= htmlspecialchars($errors['descripcion']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button
                            type="submit"
                            class="btn-primary text-white px-8 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200"
                        >
                            💾 Actualizar Categoría
                        </button>
                        <a
                            href="categoria.php"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-lg font-semibold transition-colors duration-200"
                        >
                            ❌ Cancelar
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Lista de Categorías -->
        <div class="glass-card rounded-2xl p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                    <span class="mr-3 text-3xl">📋</span>
                    Lista de Categorías
                </h2>
                <div class="text-sm text-gray-600">
                    Total: <span class="font-semibold text-blue-600"><?= count($categorias) ?></span> categorías
                </div>
            </div>

            <?php if (empty($categorias)): ?>
                <div class="text-center py-12">
                    <div class="text-6xl mb-4">📂</div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No hay categorías disponibles</h3>
                    <p class="text-gray-500 mb-6">Comienza creando tu primera categoría</p>
                    <a href="categoria.php?action=crear" class="btn-primary text-white px-6 py-3 rounded-lg font-semibold">
                        + Crear Primera Categoría
                    </a>
                </div>
            <?php else: ?>
                <div class="category-grid">
                    <?php foreach ($categorias as $c): ?>
                        <div class="category-card glass-card rounded-xl p-6">
                            <!-- Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center">
                                    <?php if (!empty($c['icono'])): ?>
                                        <span class="text-4xl mr-3"><?= htmlspecialchars($c['icono']) ?></span>
                                    <?php else: ?>
                                        <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
                                            <span class="text-gray-500">📂</span>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($c['nombre']) ?></h3>
                                        <div class="<?= (int)$c['id_estado'] === 1 ? 'status-active' : 'status-inactive' ?> status-badge inline-block mt-1">
                                            <?= (int)$c['id_estado'] === 1 ? '🟢 Activa' : '🔴 Inactiva' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Descripción -->
                            <?php if (!empty($c['descripcion'])): ?>
                                <p class="text-gray-600 text-sm mb-4 leading-relaxed">
                                    <?= htmlspecialchars($c['descripcion']) ?>
                                </p>
                            <?php endif; ?>

                            <!-- Estadísticas -->
                            <div class="bg-gray-50 rounded-lg p-3 mb-4">
                                <div class="flex items-center text-sm text-gray-600">
                                    <span class="mr-2">📊</span>
                                    <span class="font-semibold text-blue-600"><?= $model->contadorNoticias((int)$c['id']) ?></span>
                                    <span class="ml-1">noticias asociadas</span>
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="flex gap-2">
                                <a
                                    href="categoria.php?action=editar&id=<?= $c['id'] ?>"
                                    class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-center py-2 px-3 rounded-lg text-sm font-semibold transition-colors duration-200"
                                >
                                    ✏️ Editar
                                </a>

                                <?php if ((int)$c['id_estado'] === 1): ?>
                                    <a
                                        href="categoria.php?action=deshabilitar&id=<?= $c['id'] ?>"
                                        onclick="return confirm('¿Seguro que quieres deshabilitar esta categoría?');"
                                        class="flex-1 bg-red-500 hover:bg-red-600 text-white text-center py-2 px-3 rounded-lg text-sm font-semibold transition-colors duration-200"
                                    >
                                        🚫 Deshabilitar
                                    </a>
                                <?php else: ?>
                                    <a
                                        href="categoria.php?action=activar&id=<?= $c['id'] ?>"
                                        onclick="return confirm('¿Seguro que quieres activar esta categoría?');"
                                        class="flex-1 bg-green-500 hover:bg-green-600 text-white text-center py-2 px-3 rounded-lg text-sm font-semibold transition-colors duration-200"
                                    >
                                        ✅ Activar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
