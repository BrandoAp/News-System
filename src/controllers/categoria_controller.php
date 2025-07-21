<?php
// src/controllers/categoria_controller.php

require_once __DIR__ . '/../modules/categoria.php';
require_once __DIR__ . '/../validaciones/validar.php';

$model    = new Categoria();
$action   = $_GET['action']    ?? 'index';
$id       = isset($_GET['id']) ? (int) $_GET['id'] : 0;

switch ($action) {

    case 'guardar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre      = trim($_POST['nombre'] ?? '');
            $icono       = trim($_POST['icono'] ?? '') ?: null;
            $descripcion = trim($_POST['descripcion'] ?? '') ?: '';

            // validaciones
            $errors = [];
            if (!Validador::validarTexto($nombre)) {
                $errors['nombre'] = 'El nombre sólo puede contener letras y espacios.';
            }
            if (!Validador::validarLongitud($descripcion, 5)) {
                $errors['descripcion'] = 'La descripción debe tener al menos 5 caracteres.';
            }

            if ($errors) {
                // Si hay errores, redirigimos al formulario de creación
                session_start();
                $_SESSION['errors_cat'] = $errors;
                $_SESSION['old_cat']    = compact('nombre','icono','descripcion');
                header('Location: categoria.php?action=crear');
                exit;
            }

            $model->registrar([
                'nombre'      => $nombre,
                'icono'       => $icono,
                'descripcion' => $descripcion ?: null,
            ]);
        }
        header('Location: categoria.php');
        exit;

    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre      = trim($_POST['nombre'] ?? '');
            $icono       = trim($_POST['icono'] ?? '') ?: null;
            $descripcion = trim($_POST['descripcion'] ?? '') ?: '';

            // validaciones 
            $errors = [];
            if (!Validador::validarTexto($nombre)) {
                $errors['nombre'] = 'El nombre sólo puede contener letras y espacios.';
            }
            if (!Validador::validarLongitud($descripcion, 5)) {
                $errors['descripcion'] = 'La descripción debe tener al menos 5 caracteres.';
            }

            if ($errors) {
                session_start();
                $_SESSION['errors_cat'] = $errors;
                $_SESSION['old_cat']    = compact('nombre','icono','descripcion');
                header("Location: categoria.php?action=editar&id=$id");
                exit;
            }

            $model->actualizar($id, [
                'nombre'      => $nombre,
                'icono'       => $icono,
                'descripcion' => $descripcion ?: null,
            ]);
        }
        header('Location: categoria.php');
        exit;

    case 'deshabilitar':
        $model->cambiarEstado($id, 2);
        header('Location: categoria.php');
        exit;

    case 'activar':
        $model->cambiarEstado($id, 1);
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
