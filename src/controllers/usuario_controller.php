<?php
session_start();

require_once __DIR__ . '/../modules/usuario.php';
$usuario = new Usuario();
$accion = $_POST['Accion'] ?? $_GET['Accion'] ?? '';
$creado_por = $_SESSION['usuario_id'] ?? null;

switch ($accion) {
    case 'Guardar':
    case 'Modificar':
        $data = [
            "nombre"     => $_POST['nombre'] ?? '',
            "correo"     => $_POST['correo'] ?? '',
            "id_rol"     => isset($_POST['id_rol']) ? (int)$_POST['id_rol'] : null,
            "id_estado"  => $_POST['id_estado'] ?? 1,
            "contrasena" => $_POST['contrasena'] ?? '',
            "creado_por" => $creado_por
        ];

        // El modelo se encarga de sanitizar, validar y hashear
        $ok = ($accion === 'Guardar')
            ? $usuario->guardar($data)
            : $usuario->editar($_POST['id'] ?? 0, $data);

        // Si hay errores, los obtiene de la clase usuarios
        if (!$ok) {
            $_SESSION['errores_registro'] = $usuario->obtenerErrores();
            $_SESSION['datos_formulario'] = $data;
            $redir = "/News-System/public/registrar_usuario.php" . ($accion === 'Modificar' ? "?id=" . ($_POST['id'] ?? '') : '');
            header("Location: $redir");
            exit;
        }

        $_SESSION['mensaje_exito'] = ($accion === 'Guardar')
            ? "Usuario registrado correctamente"
            : "Usuario actualizado correctamente";
        
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
        $ok = $usuario->cambiarEstado($_POST['id'] ?? 0, $_POST['nuevo_estado'] ?? 0);
        $_SESSION[$ok ? 'mensaje_exito' : 'mensaje_error'] =
            $ok ? ($_POST['nuevo_estado'] == 1 ? "Usuario activado" : "Usuario desactivado") : "Error al cambiar el estado";
        
        header("Location: /News-System/public/registrar_usuario.php");
        exit;

    default:
        header('Content-Type: application/json');
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}
