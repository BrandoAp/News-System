<?php
require_once __DIR__ . '/../db/conexionDB.php';
require_once __DIR__ . '/../src/controllers/pagina_publica_controller.php';

// Obtener conexión PDO
$pdo = ConexionDB::obtenerInstancia()->obtenerConexion();

// Instanciar el controlador
$controller = new PaginaPublicaController($pdo);

// Funciones para obtener datos
$ultimasNoticias = $controller->obtenerUltimasNoticias();
$noticiasPublicadas = $controller->contarNoticiasPublicadas();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News System</title>
    <link rel="stylesheet" href="./css/output.css">
</head>

<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-start">
    <div class="w-full max-w-5xl mt-8 mb-10">
        <div class="bg-gray-50 rounded-3xl shadow-lg px-8 py-6 flex flex-col items-center">
            <h1 class="text-3xl md:text-4xl text-blue-900 font-bold mb-2 text-center">Portal de Noticias</h1>
            <span class="text-gray-600 text-center">Mantente informado con las últimas noticias y acontecimientos</span>
        </div>
        <div class="flex justify-between mt-8 px-8">
            <div class="flex flex-col items-center">
                <h4 class="text-2xl font-bold text-blue-900 mb-1"><?= $noticiasPublicadas?></h4>
                <span class="text-gray-600 text-sm">Noticias Publicadas</span>
            </div>
            <div class="flex flex-col items-center">
                <h4 class="text-2xl font-bold text-blue-900 mb-1">1235</h4>
                <span class="text-gray-600 text-sm">Visitas Hoy</span>
            </div>
            <div class="flex flex-col items-center">
                <h4 class="text-2xl font-bold text-blue-900 mb-1">44</h4>
                <span class="text-gray-600 text-sm">Usuarios Activos</span>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-6 grid-rows-6 gap-4 w-full max-w-5xl mb-5">
        <!-- Noticia principal dinámica -->
        <?php if (!empty($ultimasNoticias)):
            $principal = $ultimasNoticias[0];
        ?>
            <div class="col-span-4 row-span-6 bg-gradient-to-tr from-indigo-400 via-blue-400 to-purple-400 rounded-3xl shadow-lg flex flex-col justify-end p-8 relative overflow-hidden">
                <div class="absolute top-10 left-10 text-white text-sm font-bold opacity-90 z-10">
                    <span class="bg-white/45 px-3 py-1 rounded-lg">📰 Noticia Principal</span>
                </div>
                <div class="mt-auto">
                    <?php if (!empty($principal['imagen'])): ?>
                        <div class="relative h-85 w-full mb-6 overflow-hidden rounded-2xl shadow-xl z-0">
                            <img src="<?= htmlspecialchars($principal['imagen']) ?>"
                                alt="Imagen de la noticia"
                                class="w-full h-90 object-cover" />
                        </div>

                    <?php endif; ?>
                    <h2 class="text-2xl font-bold text-white mb-2"><?= htmlspecialchars($principal['titulo']) ?></h2>
                    <p class="text-white/90 mb-4">
                        <?= htmlspecialchars($principal['resumen'] ?? substr(strip_tags($principal['contenido']), 0, 120) . '...') ?>
                    </p>
                    <div class="flex items-center justify-between text-white/80 text-sm">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z" />
                            </svg>
                            <?= date('j \d\e F, Y', strtotime($principal['publicado_en'])) ?>
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 0 18 14.158V11a6.002 6.002 0 0 0-4-5.659V5a2 2 0 1 0-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9" />
                            </svg>
                            <?= isset($principal['visitas']) ? intval($principal['visitas']) : '0' ?> visitas
                        </span>
                    </div>
                </div>
            </div>
            <!-- Noticias secundarias dinámicas -->
            <?php
            $secundarias = array_slice($ultimasNoticias, 1, 3);
            foreach ($secundarias as $i => $noticia):
            ?>
                <div class="col-span-2 row-span-2 col-start-5 <?php if ($i == 1) echo 'row-start-3'; ?> bg-white rounded-2xl shadow p-5 flex flex-col justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-1"><?= htmlspecialchars($noticia['titulo']) ?></h3>
                        <p class="text-gray-600 text-sm mb-2"><?= htmlspecialchars($noticia['resumen'] ?? substr(strip_tags($noticia['contenido']), 0, 80) . '...') ?></p>
                    </div>
                    <span class="text-xs text-gray-400">
                        <?= date('j \d\e F, Y', strtotime($noticia['publicado_en'])) ?>
                        <?php if (!empty($noticia['autor'])): ?>
                            &nbsp;|&nbsp; <?= htmlspecialchars($noticia['publicado_en']) ?>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="w-full max-w-5xl flex justify-start mb-10">
        <a href="#"
            class="px-8 py-2 rounded-full bg-gradient-to-r from-indigo-400 via-blue-400 to-purple-400 text-white font-medium shadow-md hover:opacity-90 transition">
            Ver Todas las Noticias
        </a>
    </div>
</body>

</html>