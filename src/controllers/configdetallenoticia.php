<?php
require_once  './../db/conexionDB.php';
require_once './../src/controllers/noticiadetalles_controller.php';
session_start();
// Verifica que el usuario haya iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}


$pdo = ConexionDB::obtenerInstancia()->obtenerConexion();
$controller = new NoticiaDetallesController($pdo);

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
    if ($noticia) {
        $imagenes = $controller->obtenerImagenesDeNoticia($idNoticia);
    }
}

if (!$noticia) {
    echo "<h2 style='color:red;text-align:center;margin-top:2rem;'>Noticia no encontrada</h2>";
    exit;
}

// Variables para mensajes
$comentarioError = '';
$respuestaError = '';
$mensaje = '';

// Procesar comentario normal (lectores)
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['comentario']) &&
    $usuarioLogueado &&
    $rolUsuario == 'lector' // Cambiado a string
) {
    $contenido = trim($_POST['comentario']);
    if ($contenido !== '') {
        try {
            $controller->agregarComentario($idNoticia, $idUsuario, $contenido);
            $mensaje = 'Comentario agregado exitosamente.';
        } catch (Exception $e) {
            $comentarioError = 'Error al agregar comentario.';
        }
    } else {
        $comentarioError = 'El comentario no puede estar vacío.';
    }
}

// Procesar respuesta a comentario (admin)
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['respuesta']) &&
    isset($_POST['id_comentario_padre']) &&
    $usuarioLogueado &&
    $rolUsuario == 'admin'
) {
    $contenido = trim($_POST['respuesta']);
    $idComentarioPadre = intval($_POST['id_comentario_padre']);
    
    if ($contenido !== '') {
        try {
            $controller->responderComentario($idNoticia, $idUsuario, $contenido, $idComentarioPadre);
            $mensaje = 'Respuesta agregada exitosamente.';
        } catch (Exception $e) {
            $respuestaError = 'Error al agregar respuesta.';
        }
    } else {
        $respuestaError = 'La respuesta no puede estar vacía.';
    }
}

// Procesar eliminación de comentario 
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['eliminar_comentario']) &&
    $usuarioLogueado &&
    ($rolUsuario == 'supervisor') // Cambiado para usar roles de la BD
) {
    $idComentario = intval($_POST['eliminar_comentario']);
    try {
        $controller->eliminarComentario($idComentario);
        $mensaje = 'Comentario eliminado exitosamente.';
    } catch (Exception $e) {
        $mensaje = 'Error al eliminar comentario.';
    }
}

// Obtener comentarios con respuestas organizados jerárquicamente
try {
    $comentarios = $controller->obtenerComentariosConRespuestas($idNoticia);
} catch (Exception $e) {
    $comentarios = [];
}
?>