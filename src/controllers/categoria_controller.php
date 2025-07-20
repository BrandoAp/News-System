<?php
// src/controllers/categoria_controller.php

require_once __DIR__ . '/../modules/categoria.php';

$model    = new Categoria();
$action   = $_GET['action']    ?? 'index';
$id       = isset($_GET['id']) ? (int) $_GET['id'] : 0;

switch ($action) {

    case 'guardar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model->registrar([
                'nombre'      => trim($_POST['nombre']),
                'icono'       => trim($_POST['icono']) ?: null,
                'descripcion' => trim($_POST['descripcion']) ?: null,
            ]);
        }
        header('Location: categoria.php');
        exit;

    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model->actualizar($id, [
                'nombre'      => trim($_POST['nombre']),
                'icono'       => trim($_POST['icono']) ?: null,
                'descripcion' => trim($_POST['descripcion']) ?: null,
            ]);
        }
        header('Location: categoria.php');
        exit;

    case 'deshabilitar':
        $model->actualizarEstado($id);
        header('Location: categoria.php');
        exit;

    case 'crear':
        // Solo rompe para que la vista muestre el formulario de "crear"
        break;

    case 'editar':
        $categoria = $model->obtenerPorId($id);
        break;

    case 'index':
    default:
        // nada más, listamos
        break;
}

// datos para la vista
$categorias = $model->obtenerTodos();
