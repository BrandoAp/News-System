<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener la página actual
$pagina_actual = basename($_SERVER['PHP_SELF']);

// Función para determinar si un enlace está activo
function esEnlaceActivo($pagina_esperada) {
    global $pagina_actual;
    
    // Mapeo de páginas relacionadas
    $grupos_paginas = [
        'dashboard' => ['dashboard.php', 'index.php'],
        'categorias' => ['categoria.php', 'gestionar_categoria.php'],
        'usuarios' => ['registrar_usuario.php', 'gestionar_usuario.php', 'usuarios.php'],
        'noticias' => ['indexnoticia.php', 'gestionar_noticia.php', 'noticiadetalles.php'],
        'public' => ['lista_noticias.php', 'new-page.php']
    ];
    
    foreach ($grupos_paginas as $grupo => $paginas) {
        if ($pagina_esperada === $grupo && in_array($pagina_actual, $paginas)) {
            return true;
        }
    }
    
    return $pagina_actual === $pagina_esperada;
}

// Función para obtener clases CSS del enlace
function obtenerClasesEnlace($pagina_esperada, $tipo = 'normal') {
    $clases_base = "flex items-center gap-2 px-4 py-2 rounded-lg transition-all duration-200 font-medium";
    
    if (esEnlaceActivo($pagina_esperada)) {
        return $clases_base . " bg-blue-600 text-white shadow-md";
    } else {
        return $clases_base . " text-gray-600 hover:text-blue-600 hover:bg-gray-50";
    }
}
?>

<!-- Header Principal -->
<header class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-700 text-white shadow-lg">
    <div class="container mx-auto px-4 py-4">
        <div class="flex justify-between items-center">
            <!-- Logo y título -->
            <div class="flex items-center gap-3">
                <div class="bg-white/20 p-2 rounded-lg backdrop-blur-sm">
                    <span class="text-2xl">📰</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold">Sistema de Noticias</h1>
                    <p class="text-blue-100 text-sm">Panel de Administración</p>
                </div>
            </div>

            <!-- Información del usuario y logout -->
            <div class="flex gap-4 items-center">
                <?php if (isset($_SESSION['usuario_nombre'])): ?>
                    <div class="flex items-center gap-3 bg-white/15 px-4 py-2 rounded-lg backdrop-blur-sm border border-white/20">
                        <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                            <span class="text-sm">👤</span>
                        </div>
                        <div class="text-sm">
                            <div class="font-semibold"><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></div>
                            <div class="text-blue-100 text-xs"><?= htmlspecialchars(ucfirst($_SESSION['usuario_rol'])) ?></div>
                        </div>
                    </div>
                    <a href="logout.php" 
                       class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 shadow-md">
                        Cerrar Sesión
                    </a>
                <?php else: ?>
                    <a href="login.php" 
                       class="bg-white text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg font-medium transition-all duration-200 shadow-md">
                        Iniciar Sesión
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- Navegación principal -->
<nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-40">
    <div class="container mx-auto px-4 py-3">
        <div class="flex items-center justify-between">
            <!-- Enlaces principales -->
            <div class="flex gap-2">
                <!-- Dashboard -->
                <a href="dashboard.php" class="<?= obtenerClasesEnlace('dashboard') ?>">
                    <span>🏠</span>
                    <span>Dashboard</span>
                </a>

                <!-- Categorías (Solo admin) -->
                <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                    <a href="categoria.php" class="<?= obtenerClasesEnlace('categorias') ?>">
                        <span>📂</span>
                        <span>Categorías</span>
                    </a>
                <?php endif; ?>

                <!-- Usuarios (Solo admin) -->
                <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                    <a href="registrar_usuario.php" class="<?= obtenerClasesEnlace('usuarios') ?>">
                        <span>👥</span>
                        <span>Usuarios</span>
                    </a>
                <?php endif; ?>

                <!-- Noticias (Admin, Editor, Supervisor) -->
                <?php if (isset($_SESSION['usuario_rol']) && in_array($_SESSION['usuario_rol'], ['admin', 'editor', 'supervisor'])): ?>
                    <a href="indexnoticia.php" class="<?= obtenerClasesEnlace('noticias') ?>">
                        <span>📝</span>
                        <span>Noticias</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Acciones secundarias -->
            <div class="flex items-center gap-4">
                <!-- Breadcrumb dinámico -->
                <div class="hidden md:flex items-center gap-2 text-sm text-gray-500">
                    <?php
                    $breadcrumbs = [];
                    switch($pagina_actual) {
                        case 'dashboard.php':
                            $breadcrumbs = ['🏠 Dashboard'];
                            break;
                        case 'categoria.php':
                            $breadcrumbs = ['🏠 Dashboard', '📂 Categorías'];
                            break;
                        case 'gestionar_categoria.php':
                            $breadcrumbs = ['🏠 Dashboard', '📂 Categorías', '✏️ Gestionar'];
                            break;
                        case 'registrar_usuario.php':
                        case 'usuarios.php':
                            $breadcrumbs = ['🏠 Dashboard', '👥 Usuarios'];
                            break;
                        case 'indexnoticia.php':
                            $breadcrumbs = ['🏠 Dashboard', '📝 Noticias'];
                            break;
                        case 'gestionar_noticia.php':
                            $breadcrumbs = ['🏠 Dashboard', '📝 Noticias', '✏️ Gestionar'];
                            break;
                        case 'noticiadetalles.php':
                            $breadcrumbs = ['🏠 Dashboard', '📝 Noticias', '👁️ Ver Detalles'];
                            break;
                        case 'lista_noticias.php':
                            $breadcrumbs = ['🌐 Vista Pública', '📰 Lista de Noticias'];
                            break;
                        case 'new-page.php':
                            $breadcrumbs = ['🌐 Vista Pública', '📰 Lista de Noticias', '📖 Leer Noticia'];
                            break;
                    }
                    
                    if (!empty($breadcrumbs)) {
                        echo implode(' <span class="text-gray-300">›</span> ', $breadcrumbs);
                    }
                    ?>
                </div>

                <!-- Enlaces de acción -->
                <div class="flex items-center gap-2">
                    <!-- Enlace a vista pública -->
                    <a href="lista_noticias.php" 
                       class="<?= obtenerClasesEnlace('public') ?> text-xs">
                        <span>🌐</span>
                        <span class="hidden sm:inline">Vista Pública</span>
                    </a>

                    <!-- Indicador de rol actual -->
                    <?php if (isset($_SESSION['usuario_rol'])): ?>
                        <div class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-medium">
                            <?php
                            $roles_iconos = [
                                'admin' => '👑 Admin',
                                'editor' => '✏️ Editor',
                                'supervisor' => '👁️ Supervisor',
                                'lector' => '📖 Lector'
                            ];
                            echo $roles_iconos[$_SESSION['usuario_rol']] ?? $_SESSION['usuario_rol'];
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Indicador de página actual (solo en desarrollo) -->
<?php if (isset($_GET['debug'])): ?>
    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-2 text-sm">
        <strong>Debug:</strong> Página actual: <?= $pagina_actual ?>
    </div>
<?php endif; ?>
