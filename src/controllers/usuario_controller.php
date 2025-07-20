<?php
session_start();

require_once __DIR__ . '/../modules/usuario.php';
$usuario = new Usuario();
$accion = $_POST['Accion'] ?? $_GET['Accion'] ?? '';
$creado_por = $_SESSION['usuario_id'] ?? null;
switch ($accion) {
    case 'Guardar':
    case 'Modificar':
        $contrasena = $_POST['contrasena'] ?? '';
        $data = [
            "nombre"     => $_POST['nombre'] ?? '',
            "correo"     => $_POST['correo'] ?? '',
            "id_rol"     => isset($_POST['id_rol']) ? (int)$_POST['id_rol'] : null,
            "id_estado"  => $_POST['id_estado'] ?? 1,
            "creado_por" => $creado_por,
            "contrasena" => $contrasena
        ];

        // Validar datos 
        $errores = $usuario->validar($data, $accion);

        if (!empty($errores)) {
            $_SESSION['errores_registro'] = $errores;
            $_SESSION['datos_formulario'] = $data;
            $redir = ($accion === 'Guardar') 
                ? "/News-System/public/registrar_usuario.php" 
                : "/News-System/public/registrar_usuario.php?id=" . ($_POST['id'] ?? '');
            header("Location: $redir");
            exit;
        }
        if (!empty($contrasena)) {
            $data['contrasena'] = password_hash($contrasena, PASSWORD_DEFAULT);
        } else {
            unset($data['contrasena']); 
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
        ob_clean();
        echo json_encode($usuario->buscarTodos());
        break;

    case 'Obtener':
        header('Content-Type: application/json');
        ob_clean();
        echo json_encode($usuario->buscarPorId($_GET['id'] ?? 0));
        break;

    case 'CambiarEstado':
        $id = $_POST['id'] ?? 0;
        $nuevoEstado = $_POST['nuevo_estado'] ?? 0;
        $ok = $usuario->cambiarEstado($id, $nuevoEstado);

        if ($ok) {
            $_SESSION['mensaje_exito'] = $nuevoEstado == 1 ? "Usuario activado" : "Usuario desactivado";
        } else {
            $_SESSION['mensaje_error'] = "Error al cambiar el estado";
        }

        header("Location: /News-System/public/registrar_usuario.php");
        exit;


    default:
        header('Content-Type: application/json');
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}
?>
