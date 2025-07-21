<?php
require_once __DIR__ . '/../db/conexionDB.php';
require_once __DIR__ . '/../src/controllers/pagina_publica_controller.php';

//Obtener conexión PDO
$pdo = ConexionDB::obtenerInstancia()->obtenerConexion();

// Instanciar el controlador
$controller = new PaginaPublicaController($pdo);

// Obtener noticias publicadas
$noticias = $controller->obtenerTodasLasNoticias();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Noticias</title>
    <link rel="stylesheet" href="./css/lista_noticias.css">
    <style>
        /* Masonry layout usando CSS columns */
        .masonry {
            column-count: 1;
            column-gap: 1.5rem;
        }
        @media (min-width: 640px) {
            .masonry { column-count: 2; }
        }
        @media (min-width: 1024px) {
            .masonry { column-count: 4; }
        }
        .masonry-item {
            break-inside: avoid;
            margin-bottom: 1.5rem;
        }
        /* Limitar resumen a 3 líneas */
        .resumen-limitado {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            line-clamp: 3; /* Compatibilidad estándar */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center">
    <div class="w-full max-w-5xl mt-8">
        <div class="flex items-center mb-6">
            <a href="index.php" class="mr-4 px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                ←
            </a>
            <h1 class="text-3xl font-bold text-blue-900 ml-2">Lista de Noticias</h1>
        </div>
        <div class="masonry">
            <?php foreach ($noticias as $noticia): ?>
                <a href="new-page.php?id=<?= $noticia['id'] ?>" class="masonry-item bg-white rounded-xl shadow p-5 flex flex-col mb-4 transition hover:scale-[1.01] focus:outline-none" style="text-decoration: none;">
                    <?php if (!empty($noticia['imagen'])): ?>
                        <img src="<?= htmlspecialchars($noticia['imagen']) ?>" alt="Imagen de la noticia" class="rounded-lg mb-3 object-cover max-h-48 w-full">
                    <?php endif; ?>
                    <h2 class="text-xl font-semibold text-gray-800 mb-2"><?= htmlspecialchars($noticia['titulo']) ?></h2>
                    <p class="text-gray-600 mb-2 resumen-limitado">
                        <?= htmlspecialchars($noticia['resumen'] ?? substr(strip_tags($noticia['contenido']), 0, 100) . '...') ?>
                    </p>
                    <div class="text-xs text-gray-400 mb-1">
                        <?php if (!empty($noticia['publicado_en'])): ?>
                            <?= date('j \d\e F, Y', strtotime($noticia['publicado_en'])) ?>
                        <?php else: ?>
                            Fecha no disponible
                        <?php endif; ?>
                        <?php if (!empty($noticia['autor'])): ?>
                            &nbsp;|&nbsp; <?= htmlspecialchars($noticia['autor']) ?>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>