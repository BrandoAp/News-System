<?php
    require_once __DIR__ . '../../src/modules/cargarnoticias.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Noticias - Sistema de Noticias</title>
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
        
        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        
        .news-card {
            height: 480px;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .news-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .news-image {
            height: 200px;
            width: 100%;
            object-fit: cover;
        }
        
        .news-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.5rem;
        }
        
        .search-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }
        
        Utility classes for line clamping
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .action-buttons {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 1rem;
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
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">📝 Gestión de Noticias</h1>
                    <p class="text-gray-600">Administra y controla todas las noticias del sistema</p>
                </div>
                <a href="./gestionar_noticia.php" 
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                    <span class="mr-2">+</span>
                    Crear Nueva Noticia
                </a>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($mensajeExito) && $mensajeExito): ?>
            <div class="glass-card bg-green-50 border-l-4 border-green-400 text-green-800 p-4 rounded-xl mb-6">
                <div class="flex items-center">
                    <span class="text-green-500 mr-3">✅</span>
                    <?= htmlspecialchars($mensajeExito) ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($mensajeError) && $mensajeError): ?>
            <div class="glass-card bg-red-50 border-l-4 border-red-400 text-red-800 p-4 rounded-xl mb-6">
                <div class="flex items-center">
                    <span class="text-red-500 mr-3">⚠️</span>
                    <?= htmlspecialchars($mensajeError) ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Search Section -->
        <div class="search-section p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4 text-white">🔍 Buscar y Filtrar Noticias</h2>
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="tipo_busqueda" class="block text-sm font-medium text-white/90 mb-2">Buscar por:</label>
                        <select 
                            name="tipo_busqueda" 
                            id="tipo_busqueda"
                            class="w-full px-4 py-2 rounded-lg border-0 bg-white/90 text-gray-800 focus:bg-white focus:ring-2 focus:ring-white/50 transition-all"
                        >
                            <option value="general" <?= (isset($tipoBusqueda) && $tipoBusqueda == 'general') ? 'selected' : '' ?>>General</option>
                            <option value="categoria" <?= (isset($tipoBusqueda) && $tipoBusqueda == 'categoria') ? 'selected' : '' ?>>Por Categoría</option>
                            <option value="autor" <?= (isset($tipoBusqueda) && $tipoBusqueda == 'autor') ? 'selected' : '' ?>>Por Autor</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="busqueda" class="block text-sm font-medium text-white/90 mb-2">Término de búsqueda:</label>
                        <input 
                            type="text" 
                            name="busqueda" 
                            id="busqueda"
                            placeholder="Escriba aquí su búsqueda..." 
                            value="<?= htmlspecialchars($busqueda ?? '') ?>" 
                            class="w-full px-4 py-2 rounded-lg border-0 bg-white/90 text-gray-800 focus:bg-white focus:ring-2 focus:ring-white/50 transition-all"
                        >
                    </div>

                    <div>
                        <label for="categoria" class="block text-sm font-medium text-white/90 mb-2">Categoría:</label>
                        <select 
                            name="categoria" 
                            id="categoria"
                            class="w-full px-4 py-2 rounded-lg border-0 bg-white/90 text-gray-800 focus:bg-white focus:ring-2 focus:ring-white/50 transition-all"
                        >
                            <option value="">Todas las categorías</option>
                            <?php if (isset($categorias)): ?>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= $categoria['id'] ?>" <?= (isset($idCategoria) && $idCategoria == $categoria['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($categoria['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button 
                        type="submit" 
                        class="px-6 py-2 bg-white text-gray-800 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-white/50 font-medium transition-all"
                    >
                        🔍 Buscar
                    </button>
                    <a 
                        href="indexnoticia.php" 
                        class="px-6 py-2 bg-white/20 text-white rounded-lg hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 font-medium transition-all"
                    >
                        🗑️ Limpiar
                    </a>
                </div>
            </form>
        </div>
        
        <!-- News Grid -->
        <?php if (isset($noticias) && !empty($noticias)): ?>
            <!-- Results Counter -->
            <div class="mb-6">
                <div class="glass-card rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <p class="text-gray-600">
                            <?php if (isset($totalNoticias)): ?>
                                Se encontraron <span class="font-semibold text-blue-600"><?= $totalNoticias ?></span> noticias
                            <?php else: ?>
                                Mostrando <span class="font-semibold text-blue-600"><?= count($noticias) ?></span> noticias
                            <?php endif; ?>
                        </p>
                        <?php if (isset($pagina) && isset($totalPaginas)): ?>
                            <p class="text-sm text-gray-500">
                                Página <?= $pagina ?> de <?= $totalPaginas ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="news-grid">
                <?php foreach ($noticias as $noticia): ?>
                    <div class="news-card glass-card rounded-xl <?= ($noticia['estado_publicacion'] == 'publicado') ? 'cursor-pointer' : '' ?>" 
                         <?= ($noticia['estado_publicacion'] == 'publicado') ? 'onclick="window.location.href=\'noticiadetalles.php?id=' . $noticia['id'] . '\'"' : '' ?>>
                        <!-- Image -->
                        <div class="relative">
                            <img src="<?= !empty($noticia['imagen_portada']) 
                                    ? '/News-System/public/uploads/noticias/' . htmlspecialchars($noticia['imagen_portada']) 
                                    : '/News-System/public/assets/img/placeholder.jpg' ?>" 
                                 class="news-image" alt="Imagen de la noticia" />
                            
                            <!-- Status Badge -->
                            <div class="absolute top-3 right-3">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold shadow-md <?= ($noticia['estado_publicacion'] == 'publicado') ? 'bg-green-500 text-white' : (($noticia['estado_publicacion'] == 'borrador') ? 'bg-yellow-500 text-white' : 'bg-gray-500 text-white') ?>">
                                    <?= htmlspecialchars(ucfirst($noticia['estado_publicacion'])) ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="news-content">
                            <div class="flex-1">
                                <!-- Category -->
                                <?php if (isset($noticia['categoria_nombre'])): ?>
                                    <div class="mb-3">
                                        <span class="inline-block bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full font-medium">
                                            📂 <?= htmlspecialchars($noticia['categoria_nombre']) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <!-- Title -->
                                <h2 class="text-lg font-bold mb-3 text-gray-800 line-clamp-2 leading-tight">
                                    <?= htmlspecialchars($noticia['titulo']) ?>
                                </h2>

                                <!-- Content Preview -->
                                <p class="text-gray-600 text-sm mb-4 line-clamp-3 leading-relaxed">
                                    <?php 
                                    $contenido_limpio = strip_tags($noticia['contenido']);
                                    $contenido_corto = mb_strlen($contenido_limpio) > 80 ? 
                                        mb_substr($contenido_limpio, 0, 80) . '......' : 
                                        $contenido_limpio;
                                    echo htmlspecialchars($contenido_corto);
                                    ?>
                                </p>

                                <!-- Meta Info -->
                                <div class="flex items-center justify-between text-xs text-gray-500 border-t border-gray-100 pt-3">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center">
                                            👤 <?= htmlspecialchars($noticia['autor']) ?>
                                        </span>
                                    </div>
                                    <?php if ($noticia['estado_publicacion'] == 'publicado' && !empty($noticia['publicado_en'])): ?>
                                        <span class="inline-flex items-center">
                                            📅 <?= date('d/m/Y', strtotime($noticia['publicado_en'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center">
                                            ✏️ <?= date('d/m/Y', strtotime($noticia['creado_en'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons" onclick="event.stopPropagation();">
                            <div class="grid grid-cols-2 gap-3">
                                <!-- Editar Button -->
                                <?php
                                $usuario_id = $_SESSION['usuario_id'] ?? null;
                                $usuario_rol = $_SESSION['usuario_rol'] ?? null;
                                $esAutor = ($usuario_id && $usuario_id == $noticia['id_usuario_creador']);
                                $esAdmin = ($usuario_rol === 'admin');
                                // Solo mostrar si es admin o si es el autor y su rol es editor/supervisor
                                if ($esAdmin || ($esAutor && in_array($usuario_rol, ['editor', 'supervisor']))) :
                            ?>
                                <a href="gestionar_noticia.php?id=<?= $noticia['id'] ?>" 
                                class="text-center bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold py-3 px-4 rounded-lg transition-colors inline-flex items-center justify-center">
                                    ✏️ Editar
                                </a>
                            <?php endif; ?>
                                
                                <!-- Acción principal según estado -->
                                <?php if ($noticia['estado_publicacion'] == 'publicado'&& isset($_SESSION['usuario_rol']) && in_array($_SESSION['usuario_rol'], ['admin','supervisor'])): ?>
                                    <!-- Archivar si está publicado -->
                                    <form action="procesar_acciones.php" method="POST" class="w-full" onsubmit="return confirm('¿Estás seguro de que quieres archivar esta noticia?');">
                                        <input type="hidden" name="id_noticia" value="<?= $noticia['id'] ?>">
                                        <input type="hidden" name="accion" value="archivar">
                                        <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white text-sm font-semibold py-3 px-4 rounded-lg transition-colors">
                                            🗃️ Archivar
                                        </button>
                                    </form>
                                <?php elseif ($noticia['estado_publicacion'] == 'borrador' || $noticia['estado_publicacion'] == 'archivado'&& isset($_SESSION['usuario_rol']) && in_array($_SESSION['usuario_rol'], ['admin','supervisor'])): ?>
                                    <!-- Publicar si está en borrador o archivado -->
                                    <form action="procesar_acciones.php" method="POST" class="w-full">
                                        <input type="hidden" name="id_noticia" value="<?= $noticia['id'] ?>">
                                        <input type="hidden" name="accion" value="publicar">
                                        <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white text-sm font-semibold py-3 px-4 rounded-lg transition-colors">
                                            📢 Publicar
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <!-- Fallback para cualquier otro estado -->
                                    <div class="w-full bg-gray-400 text-white text-sm font-semibold py-3 px-4 rounded-lg text-center">
                                        Estado: <?= htmlspecialchars($noticia['estado_publicacion']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="glass-card rounded-2xl p-12 text-center">
                <div class="text-6xl mb-4">📰</div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No hay noticias disponibles</h3>
                <p class="text-gray-500 mb-6">
                    <?php if (isset($busqueda) && $busqueda): ?>
                        No se encontraron noticias que coincidan con tu búsqueda
                    <?php else: ?>
                        Comienza creando tu primera noticia
                    <?php endif; ?>
                </p>
                <a href="gestionar_noticia.php" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    + Crear Nueva Noticia
                </a>
            </div>
        <?php endif; ?>
        
        <!-- Pagination -->
        <?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
            <nav class="flex justify-center mt-8">
                <div class="glass-card rounded-xl p-3">
                    <div class="flex items-center gap-2">
                        <!-- Previous Button -->
                        <?php 
                        $paginaAnterior = max(1, ($pagina ?? 1) - 1);
                        $urlAnterior = "?pagina=" . $paginaAnterior;
                        if (isset($busqueda) && $busqueda) {
                            $urlAnterior .= "&busqueda=" . urlencode($busqueda);
                            $urlAnterior .= "&tipo_busqueda=" . ($tipoBusqueda ?? 'general');
                        }
                        if (isset($idCategoria) && $idCategoria) $urlAnterior .= "&categoria=" . $idCategoria;
                        ?>
                        <a href="<?= $urlAnterior ?>" 
                           class="flex items-center justify-center px-4 py-2 text-sm leading-tight text-gray-600 bg-white rounded-lg hover:bg-gray-50 hover:text-gray-800 transition-colors <?= ($pagina ?? 1) <= 1 ? 'opacity-50 cursor-not-allowed' : '' ?>"
                           <?= ($pagina ?? 1) <= 1 ? 'onclick="return false;"' : '' ?>>
                            ← Anterior
                        </a>
                        
                        <!-- Page Numbers -->
                        <?php 
                        $paginaActual = $pagina ?? 1;
                        $inicio = max(1, $paginaActual - 2);
                        $fin = min($totalPaginas, $paginaActual + 2);
                        
                        for ($i = $inicio; $i <= $fin; $i++): 
                        ?>
                            <?php 
                            $urlPagina = "?pagina=" . $i;
                            if (isset($busqueda) && $busqueda) {
                                $urlPagina .= "&busqueda=" . urlencode($busqueda);
                                $urlPagina .= "&tipo_busqueda=" . ($tipoBusqueda ?? 'general');
                            }
                            if (isset($idCategoria) && $idCategoria) $urlPagina .= "&categoria=" . $idCategoria;
                            ?>
                            <a href="<?= $urlPagina ?>" 
                               class="flex items-center justify-center px-3 py-2 text-sm leading-tight rounded-lg transition-colors <?= ($paginaActual == $i) ? 'text-blue-600 bg-blue-50 border border-blue-300 font-semibold' : 'text-gray-600 bg-white hover:bg-gray-50' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <!-- Next Button -->
                        <?php 
                        $paginaSiguiente = min($totalPaginas, $paginaActual + 1);
                        $urlSiguiente = "?pagina=" . $paginaSiguiente;
                        if (isset($busqueda) && $busqueda) {
                            $urlSiguiente .= "&busqueda=" . urlencode($busqueda);
                            $urlSiguiente .= "&tipo_busqueda=" . ($tipoBusqueda ?? 'general');
                        }
                        if (isset($idCategoria) && $idCategoria) $urlSiguiente .= "&categoria=" . $idCategoria;
                        ?>
                        <a href="<?= $urlSiguiente ?>" 
                           class="flex items-center justify-center px-4 py-2 text-sm leading-tight text-gray-600 bg-white rounded-lg hover:bg-gray-50 hover:text-gray-800 transition-colors <?= $paginaActual >= $totalPaginas ? 'opacity-50 cursor-not-allowed' : '' ?>"
                           <?= $paginaActual >= $totalPaginas ? 'onclick="return false;"' : '' ?>>
                            Siguiente →
                        </a>
                    </div>
                    
                    <!-- Page Info -->
                    <div class="text-center mt-2 text-xs text-gray-500">
                        Página <?= $paginaActual ?> de <?= $totalPaginas ?>
                    </div>
                </div>
            </nav>
        <?php endif; ?>
    </div>

    <?php if (isset($categorias) && isset($autores)): ?>
    <script>
        window.categorias = <?= json_encode($categorias) ?>;
        window.autores = <?= json_encode($autores) ?>;
    </script>
    <?php endif; ?>
</body>
</html>