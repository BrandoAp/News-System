<?php
require_once __DIR__ . '/../db/conexionDB.php';
require_once __DIR__ . '/../src/controllers/pagina_publica_controller.php';
session_start();

$pdo = ConexionDB::obtenerInstancia()->obtenerConexion();
$controller = new PaginaPublicaController($pdo);

$idNoticia = isset($_GET['id']) ? intval($_GET['id']) : 0;
$noticia = null;

if ($idNoticia > 0) {
    $todas = $controller->obtenerTodasLasNoticias();
    foreach ($todas as $n) {
        if ($n['id'] == $idNoticia) {
            $noticia = $n;
            break;
        }
    }
    $imagenes = $controller->obtenerImagenesDeNoticia($idNoticia);
}

if (!$noticia) {
    echo "<h2 style='color:red;text-align:center;margin-top:2rem;'>Noticia no encontrada</h2>";
    exit;
}

// Procesar comentario
$comentarioError = '';
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['comentario']) &&
    isset($_SESSION['usuario']) &&
    $_SESSION['usuario']['id_rol'] == 4
) {
    $idUsuario = $_SESSION['usuario']['id'];
    $contenido = trim($_POST['comentario']);
    if ($contenido !== '') {
        $controller->agregarComentario($idNoticia, $idUsuario, $contenido);
        // header("Location: new-page.php?id=$idNoticia");
        // exit;
    } else {
        $comentarioError = 'El comentario no puede estar vacío.';
    }
}

// Procesar reacción
$reaccionError = '';
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['reaccion']) &&
    isset($_SESSION['usuario']) &&
    $_SESSION['usuario']['id_rol'] == 4
) {
    $idUsuario = $_SESSION['usuario']['id'];
    $idTipoReaccion = $_POST['reaccion'];
    if ($controller->usuarioYaReacciono($idUsuario, $idNoticia, $idTipoReaccion)) {
        $reaccionError = 'Ya has realizado esta reacción.';
    } else {
        $controller->agregarReaccion($idUsuario, $idNoticia, $idTipoReaccion);
        header("Location: new-page.php?id=$idNoticia");
        exit;
    }
}

// Obtener comentarios y reacciones
$comentarios = $controller->obtenerComentariosDeNoticia($idNoticia);
$likes = $controller->contarReacciones($idNoticia, 'like');
$guardados = $controller->contarReacciones($idNoticia, 'guardar');
$yaLike = false;
if (isset($_SESSION['usuario']) && $_SESSION['usuario']['id_rol'] == 4) {
    $yaLike = $controller->usuarioYaReacciono($_SESSION['usuario']['id'], $idNoticia, 'like');
}

// Obtener tipos de reacción desde la base de datos
$tiposReaccion = $controller->obtenerTiposReaccion();
$reaccionesUsuario = [];
if (isset($_SESSION['usuario']) && $_SESSION['usuario']['id_rol'] == 4) {
    foreach ($tiposReaccion as $tipo) {
        $reaccionesUsuario[$tipo['nombre']] = $controller->usuarioYaReacciono($_SESSION['usuario']['id'], $idNoticia, $tipo['id']);
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($noticia['titulo']) ?> | Detalle de Noticia</title>
    <link rel="stylesheet" href="./css/noticias.css">
</head>

<body class="bg-gray-100 min-h-screen flex flex-col items-center">
    <!-- Header -->
    <div class="w-full max-w-5xl mt-8">
        <div class="bg-gray-50 rounded-3xl shadow-lg px-8 py-6 flex flex-col items-center relative">
            <div class="flex justify-center gap-3">
                <?php if (!isset($_SESSION['usuario'])): ?>
                    <a href="login_lector.php" class="px-5 py-1 rounded-full bg-blue-600 text-white font-medium shadow hover:bg-blue-700 transition text-sm">Iniciar sesión</a>
                    <a href="form_lector.php" class="px-5 py-1 rounded-full bg-white text-blue-700 border border-blue-600 font-medium shadow hover:bg-blue-50 transition text-sm">Registrarse</a>
                <?php else: ?>
                    <span class="px-5 py-1 rounded-full bg-blue-100 text-blue-700 font-medium text-sm">Hola, <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></span>
                    <a href="logout_lector.php" class="px-5 py-1 rounded-full bg-red-600 text-white font-medium shadow hover:bg-red-700 transition text-sm">Cerrar sesión</a>
                <?php endif; ?>
            </div>
            <h1 class="text-3xl md:text-4xl text-blue-900 font-bold mb-2 text-center">Portal de Noticias</h1>
            <span class="text-gray-600 text-center">Mantente informado con las últimas noticias y acontecimientos</span>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="w-full max-w-5xl mt-8">
        <div class="bg-white rounded-2xl shadow p-8">
            <!-- Volver -->
            <a href="index.php" class="inline-flex items-center px-4 py-1 mb-4 rounded-full bg-gray-100 text-blue-700 text-sm font-medium hover:bg-gray-200 transition">
                &#8592; Volver a Noticias
            </a>
            <!-- Título y meta -->
            <h2 class="text-2xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($noticia['titulo']) ?></h2>
            <div class="flex flex-wrap items-center gap-4 text-gray-500 text-sm mb-4">
                <span class="flex items-center gap-1">
                    <!-- Icono calendario -->
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2" fill="none" />
                        <line x1="16" y1="2" x2="16" y2="6" stroke="currentColor" stroke-width="2" />
                        <line x1="8" y1="2" x2="8" y2="6" stroke="currentColor" stroke-width="2" />
                        <line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2" />
                    </svg>
                    <?= date('j \d\e F, Y', strtotime($noticia['publicado_en'])) ?>
                </span>
                <span class="flex items-center gap-1">
                    <!-- Icono usuario -->
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <circle cx="12" cy="8" r="4" />
                        <path d="M4 20c0-4 8-4 8-4s8 0 8 4" />
                    </svg>
                    <?= htmlspecialchars($noticia['autor']) ?>
                </span>
            </div>
            <!-- Imagen principal -->
            <?php if (!empty($noticia['imagen'])): ?>
                <div class="bg-gradient-to-tr from-indigo-400 via-blue-400 to-purple-400 rounded-xl flex items-center justify-center h-[320px] md:h-[380px] mb-8 relative overflow-hidden">
                    <img src="<?= htmlspecialchars($noticia['imagen']) ?>" alt="Imagen de la noticia" class="object-cover w-full h-full rounded-xl" />
                </div>
            <?php endif; ?>

            <!-- Imágenes secundarias (todas menos la principal) -->
            <?php
            // Filtrar imágenes secundarias (todas menos la principal)
            $imagenesSecundarias = [];
            if (!empty($imagenes)) {
                foreach ($imagenes as $img) {
                    // Si no es principal o el campo es_principal no existe o es 0
                    if (empty($img['es_principal']) || !$img['es_principal']) {
                        $imagenesSecundarias[] = $img;
                    }
                }
            }
            ?>
            <?php if (!empty($imagenesSecundarias)): ?>
                <div class="flex gap-3 mb-6">
                    <?php foreach ($imagenesSecundarias as $img): ?>
                        <img
                            src="<?= htmlspecialchars($img['url_thumbnail'] ?? $img['url_grande']) ?>"
                            alt="Imagen secundaria"
                            class="w-28 h-28 object-cover rounded-lg" />
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <!-- Imágenes secundarias (todas las thumbnails asociadas a la noticia) -->
            <?php
            // Recoge solo thumbnails que NO sean la imagen principal
            $imagenesThumbnails = [];
            if (!empty($imagenes)) {
                foreach ($imagenes as $img) {
                    if (
                        !empty($img['url_thumbnail']) &&
                        (empty($img['es_principal']) || !$img['es_principal'])
                    ) {
                        // Evita duplicados por URL
                        if (!in_array($img['url_thumbnail'], array_column($imagenesThumbnails, 'url_thumbnail'))) {
                            $imagenesThumbnails[] = $img;
                        }
                    }
                }
            }
            ?>
            <?php if (!empty($imagenesThumbnails)): ?>
                <div class="flex gap-3 mb-6">
                    <?php foreach ($imagenesThumbnails as $img): ?>
                        <img
                            src="<?= htmlspecialchars($img['url_thumbnail']) ?>"
                            alt="Imagen secundaria"
                            class="w-28 h-28 object-cover rounded-lg" />
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <!-- Texto de la noticia -->
            <p class="text-gray-700 mb-6">
                <?= nl2br(htmlspecialchars($noticia['contenido'])) ?>
            </p>
            <!-- Acciones -->
            <div class="flex gap-3 mb-6">
                <?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['id_rol'] == 4): ?>
                    <?php if (!empty($reaccionError)): ?>
                        <div class="text-red-600"><?= htmlspecialchars($reaccionError) ?></div>
                    <?php endif; ?>
                    <?php foreach ($tiposReaccion as $tipo): ?>
                        <form method="post" class="inline">
                            <input type="hidden" name="reaccion" value="<?= $tipo['id'] ?>">
                            <button type="submit"
                                class="px-4 py-2 rounded-lg font-medium transition text-sm flex items-center gap-1
                                <?= !empty($reaccionesUsuario[$tipo['nombre']]) ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' ?>">
                                <?= htmlspecialchars($tipo['icono']) ?>
                                <?= ucfirst(str_replace('_', ' ', $tipo['nombre'])) ?>
                                (<?= $controller->contarReacciones($idNoticia, $tipo['id']) ?>)
                            </button>
                        </form>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-gray-600 text-sm">Solo los lectores pueden reaccionar o comentar.</div>
                <?php endif; ?>
            </div>
            <!-- Comentarios -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Comentarios</h3>
                <form class="mb-6" method="POST">
                    <textarea name="comentario" class="w-full border border-gray-300 rounded-lg p-3 resize-none focus:outline-none focus:ring-2 focus:ring-blue-300" rows="2" placeholder="Escribe tu comentario..."></textarea>
                    <?php if (!empty($comentarioError)): ?>
                        <p class="text-red-500 text-sm mt-2"><?= $comentarioError ?></p>
                    <?php endif; ?>
                    <button type="submit" class="mt-2 px-6 py-2 bg-blue-600 text-white rounded-full font-medium hover:bg-blue-700 transition">Publicar Comentario</button>
                </form>
                <!-- Comentarios existentes -->
                <?php if (!empty($comentarios)): ?>
                    <?php foreach ($comentarios as $comentario): ?>
                        <div class="flex items-start gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-blue-200 flex items-center justify-center font-bold text-blue-700">
                                <?= strtoupper(substr(htmlspecialchars($comentario['nombre']), 0, 2)) ?>
                            </div>
                            <div>
                                <div class="bg-gray-100 rounded-lg px-4 py-2">
                                    <span class="font-semibold text-gray-800"><?= htmlspecialchars($comentario['nombre']) ?></span>
                                    <p class="text-gray-700 text-sm mt-1"><?= nl2br(htmlspecialchars($comentario['contenido'])) ?></p>
                                </div>
                                <span class="text-xs text-gray-400 ml-2">
                                    Hace <?= isset($comentario['creado_en']) ? intval((time() - strtotime($comentario['creado_en'])) / 60) : '?' ?> minutos
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-sm">No hay comentarios aún. Sé el primero en comentar.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>