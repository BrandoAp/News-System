<?php
require_once __DIR__ . '/Usuario.php';

header('Content-Type: application/json');

$usuario = new Usuario();

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

switch ($accion) {
    case 'registrar':
        $nombre = $_POST['nombre'] ?? '';
        $correo = $_POST['correo'] ?? '';
        $contrasena = $_POST['contrasena'] ?? '';
        $rol = $_POST['rol'] ?? 'editor';

        if (!$nombre || !$correo || !$contrasena) {
            echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
            exit;
        }

        $datos = [
            'nombre' => $nombre,
            'correo' => $correo,
            'contrasena' => password_hash($contrasena, PASSWORD_DEFAULT),
            'rol' => $rol,
            'id_estado' => 1
        ];

        $ok = $usuario->registrar($datos);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Usuario registrado correctamente' : 'Error al registrar usuario'
        ]);
        break;

    case 'actualizar':
        $id = $_POST['id'] ?? 0;
        $nombre = $_POST['nombre'] ?? '';
        $correo = $_POST['correo'] ?? '';
        $rol = $_POST['rol'] ?? '';
        $estado = $_POST['id_estado'] ?? 1;

        if (!$id || !$nombre || !$correo || !$rol) {
            echo json_encode(["success" => false, "message" => "Datos incompletos."]);
            exit;
        }

        $datos = [
            'nombre' => $nombre,
            'correo' => $correo,
            'rol' => $rol,
            'id_estado' => $estado
        ];

        $ok = $usuario->actualizar($id, $datos);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Usuario actualizado correctamente' : 'Error al actualizar usuario'
        ]);
        break;

    case 'listar':
        $usuarios = $usuario->obtenerTodos();
        echo json_encode($usuarios);
        break;

    default:
        echo json_encode(["success" => false, "message" => "Acción no válida"]);
        break;
}
