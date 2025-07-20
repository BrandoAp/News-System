<?php
class ValidadorUsuarios {

    public static function validarRegistro(array $data, ControlErrores $errores, DatabaseManager $db): void {
        // Nombre obligatorio
        if (empty($data['nombre'])) {
            $errores->registrarError("El nombre es obligatorio.");
        }

        // Correo válido
        if (!Validador::validarCorreo($data['correo'] ?? '')) {
            $errores->registrarError("Correo inválido.");
        }

        // Contraseña válida 
        $password = $data['contrasena'] ?? '';
        if (!Validador::validarPassword($password)) {
            $errores->registrarError("Contraseña inválida. Debe tener mínimo 6 caracteres, una letra y un número.");
        }

        // Correo único
        if (!empty($data['correo'])) {
            $existe = $db->select("usuarios", "id", ["correo" => $data['correo']]);
            if (!empty($existe)) {
                $errores->registrarError("El correo ya está registrado.");
            }
        }

        // Validar rol
        $rolesValidos = array_column($db->select('roles', 'id'), 'id');
        if (!isset($data['id_rol']) || !in_array((int)$data['id_rol'], $rolesValidos)) {
            $errores->registrarError("Rol no válido.");
        }
    }

    public static function validarEdicion(array $data, ControlErrores $errores, DatabaseManager $db): void {
        // Nombre 
        if (empty($data['nombre'])) {
            $errores->registrarError("El nombre es obligatorio.");
        }

        // Correo válido
        if (!Validador::validarCorreo($data['correo'] ?? '')) {
            $errores->registrarError("Correo inválido.");
        }

        // Contraseña válida solo si no está vacía 
        $password = $data['contrasena'] ?? '';
        if (!empty($password) && !Validador::validarPassword($password)) {
            $errores->registrarError("Contraseña inválida. Debe tener mínimo 6 caracteres, una letra y un número.");
        }

        // Correo único 
        if (!empty($data['correo']) && isset($data['id'])) {
            $usuariosConCorreo = $db->select("usuarios", "id", ["correo" => $data['correo']]);
            foreach ($usuariosConCorreo as $idUsuario) {
                if ($idUsuario != $data['id']) {
                    $errores->registrarError("El correo ya está registrado en otro usuario.");
                    break;
                }
            }
        }

        // Validar rol
        $rolesValidos = array_column($db->select('roles', 'id'), 'id');
        if (!isset($data['id_rol']) || !in_array((int)$data['id_rol'], $rolesValidos)) {
            $errores->registrarError("Rol no válido.");
        }
    }
}
