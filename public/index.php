<?php
require_once __DIR__ . '/../db/conexionDB.php';
require_once __DIR__ . '/../src/controllers/pagina_publica_controller.php';
session_start();

// Obtener conexión PDO
$pdo = ConexionDB::obtenerInstancia()->obtenerConexion();

// Instanciar el controlador
$controller = new PaginaPublicaController($pdo);

// Funciones para obtener datos
$ultimasNoticias = $controller->obtenerUltimasNoticias();
$noticiasPublicadas = $controller->contarNoticiasPublicadas();
$visitasHoy = $controller->obtenerVisitasHoy();

// --- USUARIOS ACTIVOS DINÁMICOS ---
// Guardar usuarios activos en la sesión global
if (!isset($_SESSION['usuarios_activos'])) {
    $_SESSION['usuarios_activos'] = [];
}
// Si el usuario es lector y está logueado, se agrega a la lista de activos
if (isset($_SESSION['usuario']) && $_SESSION['usuario']['id_rol'] == 4) {
    $_SESSION['usuarios_activos'][$_SESSION['usuario']['id']] = time();
}
// Limpiar usuarios inactivos (más de 30 minutos sin actividad)
$tiempoLimite = 60 * 30;
foreach ($_SESSION['usuarios_activos'] as $id => $ultimoAcceso) {
    if (time() - $ultimoAcceso > $tiempoLimite) {
        unset($_SESSION['usuarios_activos'][$id]);
    }
}
$usuariosActivos = count($_SESSION['usuarios_activos']);
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
        <div class="bg-gray-50 rounded-3xl shadow-lg px-8 py-6 flex flex-col items-center relative">
            <!-- Botones de registro, inicio de sesión o usuario logueado -->
            <div class="flex justify-center gap-3">
                <?php if (!isset($_SESSION['usuario'])): ?>
                    <a href="login_lector.php" class="px-5 py-1 rounded-full bg-blue-600 text-white font-medium shadow hover:bg-blue-700 transition text-sm">Iniciar sesión</a>
                    <a href="form_lector.php" class="px-5 py-1 rounded-full bg-white text-blue-700 border border-blue-600 font-medium shadow hover:bg-blue-50 transition text-sm">Registrarse</a>
                <?php else: ?>
                    <span class="px-5 py-1 rounded-full bg-blue-100 text-blue-700 font-medium text-sm">
                        Hola, <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>
                    </span>
                    <a href="logout_lector.php" class="px-5 py-1 rounded-full bg-red-600 text-white font-medium shadow hover:bg-red-700 transition text-sm">Cerrar sesión</a>
                <?php endif; ?>
            </div>
            <h1 class="text-3xl md:text-4xl text-blue-900 font-bold mb-2 text-center">Portal de Noticias</h1>
            <span class="text-gray-600 text-center">Mantente informado con las últimas noticias y acontecimientos</span>
        </div>
        <div class="flex justify-between mt-8 px-8">
            <div class="flex flex-col items-center">
                <h4 class="text-2xl font-bold text-blue-900 mb-1"><?= $noticiasPublicadas?></h4>
                <span class="text-gray-600 text-sm">Noticias Publicadas</span>
            </div>
            <div class="flex flex-col items-center">
                <h4 id="visitas-contador" class="text-2xl font-bold text-blue-900 mb-1"><?= $visitasHoy ?></h4>
                <span class="text-gray-600 text-sm">Visitas Hoy</span>
            </div>
            <div class="flex flex-col items-center">
                <h4 class="text-2xl font-bold text-blue-900 mb-1"><?= $usuariosActivos ?></h4>
                <span class="text-gray-600 text-sm">Usuarios Activos</span>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-6 grid-rows-6 gap-4 w-full max-w-5xl mb-5">
        <!-- Noticia principal dinámica -->
        <?php if (!empty($ultimasNoticias)):
            $principal = $ultimasNoticias[0];
        ?>
            <a href="./new-page.php?id=<?= $principal['id'] ?>" 
               class="col-span-4 row-span-6 bg-gradient-to-tr from-indigo-400 via-blue-400 to-purple-400 rounded-3xl shadow-lg flex flex-col justify-end p-8 relative overflow-hidden group transition hover:scale-[1.01] focus:outline-none"
               aria-label="Ver noticia principal"
               style="text-decoration: none;">
                <div class="absolute top-10 left-10 text-white text-sm font-bold opacity-90 z-10 pointer-events-none">
                    <span class="bg-white/45 px-3 py-1 rounded-lg">📰 Noticia Principal</span>
                </div>
                <div class="mt-auto relative z-10 pointer-events-none">
                    <?php if (!empty($principal['imagen'])): ?>
                        <div class="relative h-85 w-full mb-6 overflow-hidden rounded-2xl shadow-xl">
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
                            <?= date('j \d\e F, Y', strtotime($principal['publicado_en'])) ?>
                        </span>
                    </div>
                </div>
            </a>

            <!-- Noticias secundarias dinámicas -->
            <?php
            $secundarias = array_slice($ultimasNoticias, 1, 3);
            foreach ($secundarias as $i => $noticia):
            ?>
            <a href="./new-page.php?id=<?= $noticia['id'] ?>" 
               class="col-span-2 row-span-2 col-start-5 <?php if ($i == 1) echo 'row-start-3'; ?> bg-white rounded-2xl shadow p-5 flex flex-col justify-between relative group transition hover:scale-[1.01] focus:outline-none"
               aria-label="Ver noticia secundaria"
               style="text-decoration: none;">
                <div class="relative z-10">
                    <h3 class="font-semibold text-gray-800 mb-1"><?= htmlspecialchars($noticia['titulo']) ?></h3>
                    <p class="text-gray-600 text-sm mb-2"><?= htmlspecialchars($noticia['resumen'] ?? substr(strip_tags($noticia['contenido']), 0, 80) . '...') ?></p>
                </div>
                <span class="text-xs text-gray-400 relative z-10">
                    <?= date('j \d\e F, Y', strtotime($noticia['publicado_en'])) ?>
                    <?php if (!empty($noticia['autor'])): ?>
                        &nbsp;|&nbsp; <?= htmlspecialchars($noticia['autor']) ?>
                    <?php endif; ?>
                </span>
            </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="w-full max-w-5xl flex justify-start mb-10">
        <a href="./lista_noticias.php"
            class="px-8 py-2 rounded-full bg-gradient-to-r from-indigo-400 via-blue-400 to-purple-400 text-white font-medium shadow-md hover:opacity-90 transition">
            Ver Todas las Noticias
        </a>
    </div>
</body>

</html>