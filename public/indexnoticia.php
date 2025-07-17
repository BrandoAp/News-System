<?php
require_once __DIR__ . '/../db/conexionDB.php';
require_once __DIR__ . '/../db/DatabaseManager.php';
require_once __DIR__ . '/../src/controllers/NoticiasController.php';

// Obtener conexión PDO
$pdo = ConexionDB::obtenerInstancia()->obtenerConexion();

// Crear instancia de DatabaseManager
$dbManager = new DatabaseManager($pdo);
$noticiasController = new NoticiasController($dbManager);
    $busqueda = $_GET['busqueda'] ?? null;
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
// Funciones para obtener datos
    $noticias = $noticiasController->obtenerTodasLasNoticias($busqueda, $pagina);
    $totalNoticias = $noticiasController->contarNoticias($busqueda);
    $totalPaginas = ceil($totalNoticias / $dbManager->getPerPage());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noticias</title>
</head>
 <body>
    
 <div class="container mt-4">
    <h1>Listado de Noticias</h1>
    <?php include '../src/modules/buscador.php'; ?>
    <div class="mb-3">
        <a href="../src/modules/crearnoticia.php" class="btn btn-primary">Crear Noticia</a>
    </div>
    
    <!-- Listado de noticias -->
    <?php foreach ($noticias as $noticia): ?>
    <div class="card mb-4">
        <?php if ($noticia['imagen']): ?>
        <img src="<?= $noticia['imagen'] ?>" class="card-img-top" alt="Imagen de la noticia">
        <?php endif; ?>
        
        <div class="card-body">
            <h2 class="card-title"><?= htmlspecialchars($noticia['titulo']) ?></h2>
            <p class="card-text"><?= substr(htmlspecialchars($noticia['contenido']), 0, 150) ?>...</p>
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">Publicado el <?= $noticia['fecha'] ?> por <?= $noticia['autor'] ?></small>
                <div>
                    <a href="mostrar.php?id=<?= $noticia['id'] ?>" class="btn btn-sm btn-info">Ver más</a>
                    <a href="editar.php?id=<?= $noticia['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                    <a href="eliminar.php?id=<?= $noticia['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro?')">Eliminar</a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <!-- Navegación (paginación) -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1">Anterior</a>
            </li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item">
                <a class="page-link" href="#">Siguiente</a>
            </li>
        </ul>
    </nav>
</div>

</body>
</html>