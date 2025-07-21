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