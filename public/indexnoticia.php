<?php
session_start();

require_once __DIR__ . '/../db/conexionDB.php';
require_once __DIR__ . '/../db/DatabaseManager.php';
require_once __DIR__ . '/../src/controllers/NoticiasController.php';

$mensajeExito = $_SESSION['mensaje_exito'] ?? '';
unset($_SESSION['mensaje_exito']);
$mensajeError = $_SESSION['mensaje_error'] ?? '';
unset($_SESSION['mensaje_error']);

try {
    $pdo = ConexionDB::obtenerInstancia()->obtenerConexion();
    $noticiasController = new NoticiasController($pdo);

    $busqueda = trim($_GET['busqueda'] ?? '');
    $tipoBusqueda = $_GET['tipo_busqueda'] ?? 'general'; // Nuevo parámetro
    $idCategoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    if ($pagina < 1) $pagina = 1;

    $noticiasPorPagina = 3;
    
    // Obtener categorías y autores para los filtros
    $categorias = $noticiasController->obtenerCategorias();
    $autores = $noticiasController->obtenerAutores();
    
    // Usar los nuevos métodos del controlador con filtros
    $totalNoticias = $noticiasController->contarNoticias($busqueda, $idCategoria, $tipoBusqueda);
    $noticias = $noticiasController->obtenerNoticias($pagina, $noticiasPorPagina, $busqueda, $idCategoria, $tipoBusqueda);
    $totalPaginas = ceil($totalNoticias / $noticiasPorPagina);

} catch (Exception $e) {
    error_log("Error en indexnoticas.php: " . $e->getMessage());
    $mensajeError = 'Error al cargar las noticias. Por favor, inténtalo más tarde.';
    $noticias = [];
    $totalPaginas = 0;
    $categorias = [];
    $autores = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Noticias</title>
    <link rel="stylesheet" href="/News-System/public/css/noticias.css">
</head>
<body class="bg-gray-100">
    <?php include './header.php'; ?>
    
    <div class="container mx-auto mt-8 p-4">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Listado de Noticias</h1>
            <a href="./gestionar_noticia.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow">
                + Crear Nueva Noticia
            </a>
        </div>

        <?php if ($mensajeExito): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert"><?= htmlspecialchars($mensajeExito) ?></div>
        <?php endif; ?>
        <?php if ($mensajeError): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert"><?= htmlspecialchars($mensajeError) ?></div>
        <?php endif; ?>

        <!-- Barra buscadora con filtro de categorías y tipo de búsqueda -->
        <div class="mb-6 bg-white p-4 rounded-lg shadow">
            <form method="GET" class="space-y-4">
                <!-- Tipo de búsqueda -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="tipo_busqueda" class="block text-sm font-medium text-gray-700 mb-2">Buscar por:</label>
                        <select 
                            name="tipo_busqueda" 
                            id="tipo_busqueda"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            onchange="actualizarCampoBusqueda()"
                        >
                            <option value="general" <?= ($tipoBusqueda == 'general') ? 'selected' : '' ?>>General</option>
                            <option value="categoria" <?= ($tipoBusqueda == 'categoria') ? 'selected' : '' ?>>Por Categoría</option>
                            <option value="autor" <?= ($tipoBusqueda == 'autor') ? 'selected' : '' ?>>Por Autor</option>
                        </select>
                    </div>

                    <!-- Campo de búsqueda dinámico -->
                    <div class="md:col-span-2">
                        <label for="busqueda" class="block text-sm font-medium text-gray-700 mb-2">Término de búsqueda:</label>
                        <input 
                            type="text" 
                            name="busqueda" 
                            id="busqueda"
                            placeholder="Escriba aquí su búsqueda..." 
                            value="<?= htmlspecialchars($busqueda) ?>" 
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            list="sugerencias"
                        >
                        <!-- Datalist para sugerencias -->
                        <datalist id="sugerencias">
                            <!-- Se llenará con JavaScript -->
                        </datalist>
                    </div>

                    <!-- Filtro de categorías (solo cuando no se busca por categoría) -->
                    <div id="filtro_categoria" <?= ($tipoBusqueda == 'categoria') ? 'style="display:none"' : '' ?>>
                        <label for="categoria" class="block text-sm font-medium text-gray-700 mb-2">Filtrar por categoría:</label>
                        <select 
                            name="categoria" 
                            id="categoria"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>" <?= ($idCategoria == $categoria['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex gap-2">
                    <button 
                        type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        Buscar
                    </button>
                    <a 
                        href="?" 
                        class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500"
                    >
                        Limpiar
                    </a>
                </div>
            </form>

            <!-- Indicadores de filtros activos -->
            <?php if ($busqueda || $idCategoria): ?>
                <div class="mt-3 flex flex-wrap gap-2">
                    <?php if ($busqueda): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                            <?php
                            $tipoBusquedaTexto = [
                                'general' => 'Búsqueda general',
                                'categoria' => 'Búsqueda por categoría',
                                'autor' => 'Búsqueda por autor'
                            ];
                            ?>
                            <?= $tipoBusquedaTexto[$tipoBusqueda] ?? 'Búsqueda' ?>: "<?= htmlspecialchars($busqueda) ?>"
                            <a href="?<?= ($idCategoria && $tipoBusqueda != 'categoria') ? 'categoria=' . $idCategoria : '' ?>" class="ml-2 text-blue-600 hover:text-blue-800">×</a>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($idCategoria && $tipoBusqueda != 'categoria'): ?>
                        <?php
                        $nombreCategoriaActiva = '';
                        foreach ($categorias as $cat) {
                            if ($cat['id'] == $idCategoria) {
                                $nombreCategoriaActiva = $cat['nombre'];
                                break;
                            }
                        }
                        ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-green-800">
                            Categoría: <?= htmlspecialchars($nombreCategoriaActiva) ?>
                            <a href="?<?= $busqueda ? 'busqueda=' . urlencode($busqueda) . '&tipo_busqueda=' . $tipoBusqueda : '' ?>" class="ml-2 text-green-600 hover:text-green-800">×</a>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($noticias)): ?>
            <p class="text-center text-gray-500 mt-10">
                <?php if ($busqueda || $idCategoria): ?>
                    No se encontraron noticias que coincidan con los filtros aplicados.
                <?php else: ?>
                    No se encontraron noticias.
                <?php endif; ?>
            </p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($noticias as $noticia): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col">
                        <img src="<?= htmlspecialchars($noticia['imagen_portada'] ?? '/News-System/public/assets/img/placeholder.jpg') ?>" class="w-full h-48 object-cover" alt="Imagen de la noticia">
                        
                        <div class="p-6 flex-grow">
                            <h2 class="text-xl font-bold mb-2 text-gray-800"><?= htmlspecialchars($noticia['titulo']) ?></h2>
                            
                            <!-- Mostrar categoría -->
                            <?php if (isset($noticia['categoria_nombre'])): ?>
                                <div class="mb-2">
                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                        <?= htmlspecialchars($noticia['categoria_nombre']) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <p class="text-gray-600 text-sm mb-4 leading-relaxed">
                                <?= htmlspecialchars(substr($noticia['contenido'], 0, 120)) ?>...
                            </p>

                            <!-- Contador de imágenes -->
                            <div class="mb-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700">
                                    📸 <?= $noticia['total_imagenes'] ?> imágenes
                                </span>
                            </div>
                        </div>

                        <div class="p-6 bg-gray-50 border-t">
                            <div class="text-xs text-gray-500 mb-4">
                                <span>Publicado por <strong><?= htmlspecialchars($noticia['autor']) ?></strong></span><br>
                                <span>el <?= htmlspecialchars(date('d/m/Y', strtotime($noticia['publicado_en']))) ?></span>
                                <span class="ml-2 px-2 py-1 rounded-full font-semibold <?= ($noticia['estado_publicacion'] == 'publicado') ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800' ?>">
                                    <?= htmlspecialchars(ucfirst($noticia['estado_publicacion'])) ?>
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="gestionar_noticia.php?id=<?= $noticia['id'] ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-bold py-2 px-3 rounded">Editar</a>
                                
                                <?php switch($noticia['estado_publicacion']) {
                                    case 'publicado': ?>
                                        <form action="procesar_acciones.php" method="POST" class="inline">
                                            <input type="hidden" name="id_noticia" value="<?= $noticia['id'] ?>">
                                            <input type="hidden" name="accion" value="despublicar">
                                            <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white text-xs font-bold py-2 px-3 rounded">Despublicar</button>
                                        </form>
                                        <?php break;
                                    default: ?>
                                        <form action="procesar_acciones.php" method="POST" class="inline">
                                            <input type="hidden" name="id_noticia" value="<?= $noticia['id'] ?>">
                                            <input type="hidden" name="accion" value="publicar">
                                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white text-xs font-bold py-2 px-3 rounded">Publicar</button>
                                        </form>
                                        <?php break;
                                } ?>
                                
                                <?php if ($noticia['estado_publicacion'] != 'archivado'): ?>
                                <form action="procesar_acciones.php" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de que quieres archivar esta noticia?');">
                                    <input type="hidden" name="id_noticia" value="<?= $noticia['id'] ?>">
                                    <input type="hidden" name="accion" value="archivar">
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold py-2 px-3 rounded">Archivar</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Paginación -->
        <?php if ($totalPaginas > 1): ?>
        <nav class="flex justify-center mt-10">
            <ul class="flex items-center -space-x-px h-10 text-base">
                <li>
                    <?php 
                    $urlAnterior = "?pagina=" . max(1, $pagina - 1);
                    if ($busqueda) {
                        $urlAnterior .= "&busqueda=" . urlencode($busqueda);
                        $urlAnterior .= "&tipo_busqueda=" . $tipoBusqueda;
                    }
                    if ($idCategoria) $urlAnterior .= "&categoria=" . $idCategoria;
                    ?>
                    <a href="<?= $urlAnterior ?>" class="flex items-center justify-center px-4 h-10 ms-0 leading-tight text-gray-500 bg-white border border-e-0 border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700">Anterior</a>
                </li>
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li>
                    <?php 
                    $urlPagina = "?pagina=" . $i;
                    if ($busqueda) {
                        $urlPagina .= "&busqueda=" . urlencode($busqueda);
                        $urlPagina .= "&tipo_busqueda=" . $tipoBusqueda;
                    }
                    if ($idCategoria) $urlPagina .= "&categoria=" . $idCategoria;
                    ?>
                    <a href="<?= $urlPagina ?>" class="flex items-center justify-center px-4 h-10 leading-tight <?= ($pagina == $i) ? 'text-blue-600 bg-blue-50 border-blue-300' : 'text-gray-500 bg-white border-gray-300' ?> hover:bg-gray-100 hover:text-gray-700"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <li>
                    <?php 
                    $urlSiguiente = "?pagina=" . min($totalPaginas, $pagina + 1);
                    if ($busqueda) {
                        $urlSiguiente .= "&busqueda=" . urlencode($busqueda);
                        $urlSiguiente .= "&tipo_busqueda=" . $tipoBusqueda;
                    }
                    if ($idCategoria) $urlSiguiente .= "&categoria=" . $idCategoria;
                    ?>
                    <a href="<?= $urlSiguiente ?>" class="flex items-center justify-center px-4 h-10 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700">Siguiente</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <script>
        // Datos PHP para JavaScript
        const categorias = <?= json_encode($categorias) ?>;
        const autores = <?= json_encode($autores) ?>;

        function actualizarCampoBusqueda() {
            const tipoBusqueda = document.getElementById('tipo_busqueda').value;
            const campoBusqueda = document.getElementById('busqueda');
            const filtroCategoria = document.getElementById('filtro_categoria');
            const sugerencias = document.getElementById('sugerencias');
            
            // Limpiar sugerencias
            sugerencias.innerHTML = '';
            
            // Actualizar placeholder y sugerencias según el tipo
            switch(tipoBusqueda) {
                case 'categoria':
                    campoBusqueda.placeholder = 'Escriba el nombre de la categoría (ej: deportes, farándula)';
                    filtroCategoria.style.display = 'none';
                    
                    // Agregar categorías como sugerencias
                    categorias.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.nombre;
                        sugerencias.appendChild(option);
                    });
                    break;
                    
                case 'autor':
                    campoBusqueda.placeholder = 'Escriba el nombre del autor';
                    filtroCategoria.style.display = 'block';
                    
                    // Agregar autores como sugerencias
                    autores.forEach(autor => {
                        const option = document.createElement('option');
                        option.value = autor.autor;
                        sugerencias.appendChild(option);
                    });
                    break;
                    
                default: // general
                    campoBusqueda.placeholder = 'Buscar por título, contenido o autor...';
                    filtroCategoria.style.display = 'block';
                    break;
            }
        }

        // Ejecutar al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            actualizarCampoBusqueda();
        });
    </script>
</body>
</html>