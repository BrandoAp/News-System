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
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Grid uniforme - tarjetas muy compactas */
        .grid-noticias {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 0.75rem;
        }
        
        /* Tarjetas muy compactas */
        .tarjeta-noticia {
            height: 260px;
            display: flex;
            flex-direction: column;
        }
        
        /* Imagen pequeña */
        .imagen-noticia {
            height: 100px;
            width: 100%;
            object-fit: cover;
        }
        
        /* Contenido con flex-grow */
        .contenido-noticia {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        /* Resumen limitado a 2 líneas */
        .resumen-limitado {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center">
    <div class="w-full max-w-7xl mt-6 px-4">
        <div class="flex items-center mb-4">
            <a href="index.php" class="mr-3 px-3 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700 transition">
                ←
            </a>
            <h1 class="text-2xl font-bold text-blue-900 ml-2">Lista de Noticias</h1>
        </div>
        
        <div class="grid-noticias">
            <?php foreach ($noticias as $noticia): ?>
                <a href="new-page.php?id=<?= $noticia['id'] ?>" 
                   class="tarjeta-noticia bg-white rounded-md shadow-sm overflow-hidden transition-all duration-300 hover:scale-[1.02] hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500/20" 
                   style="text-decoration: none;">
                    
                    <?php if (!empty($noticia['imagen'])): ?>
                        <img src="/News-System/public/uploads/noticias/<?= htmlspecialchars($noticia['imagen']) ?>" 
                             alt="Imagen de la noticia" 
                             class="imagen-noticia">
                    <?php else: ?>
                        <div class="imagen-noticia bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                            <span class="text-2xl text-gray-400">📰</span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="contenido-noticia p-3">
                        <div class="flex-1">
                            <h2 class="text-base font-semibold text-gray-800 mb-1 leading-tight">
                                <?= htmlspecialchars($noticia['titulo']) ?>
                            </h2>
                            <p class="text-gray-600 text-xs mb-2 resumen-limitado leading-snug">
                                <?= htmlspecialchars($noticia['resumen'] ?? substr(strip_tags($noticia['contenido']), 0, 80) . '...') ?>
                            </p>
                        </div>
                        
                        <div class="text-xs text-gray-400 border-t border-gray-100 pt-1.5 mt-2">
                            <?php if (!empty($noticia['publicado_en'])): ?>
                                <span class="font-medium">
                                    <?= date('j/m/Y', strtotime($noticia['publicado_en'])) ?>
                                </span>
                            <?php else: ?>
                                <span>Sin fecha</span>
                            <?php endif; ?>
                            <?php if (!empty($noticia['autor'])): ?>
                                <span class="mx-1">•</span>
                                <span><?= htmlspecialchars(substr($noticia['autor'], 0, 15)) ?><?= strlen($noticia['autor']) > 15 ? '...' : '' ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>