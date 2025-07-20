<?php
session_start();

require_once __DIR__ . '/../db/conexionDB.php';
require_once __DIR__ . '/../db/DatabaseManager.php';
require_once __DIR__ . '/../src/controllers/NoticiasController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: indexnoticia.php');
    exit();
}

$id_noticia = filter_input(INPUT_POST, 'id_noticia', FILTER_VALIDATE_INT);
$accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if ($id_noticia === false || $id_noticia === null || empty($accion)) {
    $_SESSION['mensaje_error'] = 'Solicitud no válida.';
    header('Location: indexnoticia.php');
    exit();
}

try {
    $pdo = ConexionDB::obtenerInstancia()->obtenerConexion();
    $dbManager = new DatabaseManager($pdo);
    $noticiasController = new NoticiasController($pdo);

    $estados = [
        'publicar' => $noticiasController->obtenerIdEstadoPorNombre('publicado'),
        'despublicar' => $noticiasController->obtenerIdEstadoPorNombre('inactivo'), // O el estado que prefieras para un borrador
        'archivar' => $noticiasController->obtenerIdEstadoPorNombre('archivado'),
    ];

    if (!isset($estados[$accion])) {
        throw new Exception('Acción no reconocida.');
    }

    $nuevo_id_estado = $estados[$accion];
    $datos_actualizar = ['id_estado' => $nuevo_id_estado];
    
    // Si se publica, actualizar la fecha de publicación
    if($accion === 'publicar') {
        $datos_actualizar['publicado_en'] = date('Y-m-d H:i:s');
    }

    $actualizado = $dbManager->updateSeguro('noticias', $datos_actualizar, ['id' => $id_noticia]);

    if ($actualizado) {
        $_SESSION['mensaje_exito'] = 'El estado de la noticia ha sido actualizado correctamente.';
    } else {
        $_SESSION['mensaje_error'] = 'No se pudo actualizar la noticia.';
    }

} catch (Exception $e) {
    error_log("Error en procesar_acciones.php: " . $e->getMessage());
    $_SESSION['mensaje_error'] = 'Ocurrió un error al procesar la solicitud.';
}

header('Location: indexnoticia.php');
exit();