<?php
require_once __DIR__ . '/../usuarios/usuario.php';
require_once __DIR__ . '/../../db/conexionDB.php';
require_once __DIR__ . '/../../db/DatabaseManager.php';

$usuario = new Usuario();
$accion = $_POST['Accion'] ?? $_GET['Accion'] ?? '';

switch ($accion) {
    case 'Guardar':
    case 'Modificar':
        session_start();
        $contrasena = $_POST['contrasena'] ?? '';

        $data = [
            "nombre"     => $_POST['nombre'] ?? '',
            "correo"     => $_POST['correo'] ?? '',
            "rol"        => $_POST['rol'] ?? 'lector',
            "id_estado"  => $_POST['id_estado'] ?? 1
        ];

        if ($accion === 'Guardar') {
            if (empty($contrasena)) {
                $_SESSION['mensaje_error'] = "La contraseña es obligatoria.";
                $_SESSION['datos_formulario'] = $data;
                header("Location: /News-System/public/registrar_usuario.php");
                exit;
            }
            $data['contrasena'] = password_hash($contrasena, PASSWORD_DEFAULT);
        } else {
            if (!empty($contrasena)) {
                $data['contrasena'] = password_hash($contrasena, PASSWORD_DEFAULT);
            }
        }

        $errores = $usuario->validar($data, $accion);

        if (!empty($errores)) {
            $_SESSION['errores_registro'] = $errores;
            $_SESSION['datos_formulario'] = $data;
            $redir = ($accion === 'Guardar') ? "/News-System/public/registrar_usuario.php" : "/News-System/public/registrar_usuario.php?id=" . $_POST['id'];
            header("Location: $redir");
            exit;
        }

        if ($accion === 'Guardar') {
            $ok = $usuario->guardar($data);
            $_SESSION['mensaje_exito'] = $ok ? "Usuario registrado correctamente" : "Error al guardar";
        } else {
            $id = $_POST['id'] ?? 0;
            $ok = $usuario->editar($id, $data);
            $_SESSION['mensaje_exito'] = $ok ? "Usuario actualizado correctamente" : "Error al actualizar";
        }

        header("Location: /News-System/public/registrar_usuario.php");
        exit;

    case 'Listar':
        header('Content-Type: application/json');
        $data = $usuario->buscarTodos();
        ob_clean();
        echo json_encode($data);
        break;

    case 'Obtener':
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? 0;
        $usuarioData = $usuario->buscarPorId($id);
        ob_clean();
        echo json_encode($usuarioData);
        break;

    case 'CambiarEstado':
        header('Content-Type: application/json');
        $id = $_POST['id'] ?? 0;
        $nuevoEstado = $_POST['nuevo_estado'] ?? 0;

        if ($id > 0) {
            $resultado = $usuario->cambiarEstado($id, $nuevoEstado);
            if ($resultado) {
                $mensaje = $nuevoEstado == 1 ? 'Usuario activado correctamente' : 'Usuario desactivado correctamente';
                ob_clean();
                echo json_encode(['success' => true, 'message' => $mensaje]);
            } else {
                ob_clean();
                echo json_encode(['success' => false, 'message' => 'Error al cambiar el estado del usuario']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID de usuario no válido']);
        }
        break;

    case 'Eliminar':
        header('Content-Type: application/json');
        $id = $_POST['id'] ?? 0;

        if ($id > 0) {
            $resultado = $usuario->eliminar($id);
            if ($resultado) {
                ob_clean();
                echo json_encode(['success' => true, 'message' => 'Usuario eliminado correctamente']);
            } else {
                ob_clean();
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el usuario']);
            }
        } else {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'ID de usuario no válido']);
        }
        break;

    default:
        header('Content-Type: application/json');
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}
?>
