<?php
session_start();

require_once __DIR__ . '/../../db/conexionDB.php';
require_once __DIR__ . '/../../db/DatabaseManager.php';
require_once __DIR__ . '/../controllers/NoticiasController.php';

$mensajeExito = $_SESSION['mensaje_exito'] ?? '';
unset($_SESSION['mensaje_exito']);
$mensajeError = $_SESSION['mensaje_error'] ?? '';
unset($_SESSION['mensaje_error']);

// Inicializar variables por defecto
$noticias = [];
$totalPaginas = 0;
$categorias = [];
$autores = [];

try {
    $pdo = ConexionDB::obtenerInstancia()->obtenerConexion();
    
    $noticiasController = new NoticiasController($pdo);

    $busqueda = trim($_GET['busqueda'] ?? '');
    $tipoBusqueda = $_GET['tipo_busqueda'] ?? 'general';
    $idCategoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    if ($pagina < 1) $pagina = 1;

    $noticiasPorPagina = 5; // Número de noticias por página

    // Obtener datos
    $categorias = $noticiasController->obtenerCategorias();
    $autores = $noticiasController->obtenerAutores();
    
    $totalNoticias = $noticiasController->contarNoticias($busqueda, $idCategoria, $tipoBusqueda);
    $noticias = $noticiasController->obtenerNoticias($pagina, $noticiasPorPagina, $busqueda, $idCategoria, $tipoBusqueda);
    $totalPaginas = ceil($totalNoticias / $noticiasPorPagina);


} catch (Exception $e) {
    error_log("Error en cargarnoticias.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $mensajeError = 'Error al cargar las noticias. Por favor, inténtalo más tarde.';
}