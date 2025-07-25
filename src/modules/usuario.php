<?php
require_once __DIR__ . '/../../db/conexionDB.php';
require_once __DIR__ . '/../../db/DatabaseManager.php';
require_once __DIR__ . '/../validaciones/InterfazErrores.php';
require_once __DIR__ . '/../validaciones/ControlErrores.php';
require_once __DIR__ . '/../validaciones/validar.php';
require_once __DIR__ . '/../validaciones/Sanitizador.php';
require_once __DIR__ . '/../validaciones/validadorUsuaios.php';

class Usuario {
    private $db;
    private $controlErrores;

    public function __construct() {
        $conexion = ConexionDB::obtenerInstancia()->obtenerConexion();
        $this->db = new DatabaseManager($conexion);
        $this->controlErrores = new ControlErrores();
    }
    //Guarda un nuevo usuario en la base de dato
    public function guardar($data) {
        $this->controlErrores->limpiarErrores();

        $data = $this->sanitizarDatos($data);
        $this->validar($data, 'Guardar');

        if ($this->controlErrores->hayErrores()) {
            return false;
        }

        if (!empty($data['contrasena'])) {
            $data['contrasena'] = password_hash($data['contrasena'], PASSWORD_DEFAULT);
        }

        $exito = $this->db->insertSeguro("usuarios", $data);
        if (!$exito) {
            $this->controlErrores->registrarError("No se pudo guardar el usuario en la base de datos. Es posible que el correo ya esté registrado.");
        }

        return $exito;
    }
    //Edita un usuario existente en la base de datos
    public function editar($id, $data) {
        $this->controlErrores->limpiarErrores();

        $data = $this->sanitizarDatos($data);
        $this->validar($data, 'Modificar');

        if ($this->controlErrores->hayErrores()) {
            return false;
        }

        if (!empty($data['contrasena'])) {
            $data['contrasena'] = password_hash($data['contrasena'], PASSWORD_DEFAULT);
        } else {
            unset($data['contrasena']);
        }

        $exito = $this->db->updateSeguro("usuarios", $data, ["id" => $id]);
        if (!$exito) {
            $this->controlErrores->registrarError("No se pudo actualizar el usuario.");
        }

        return $exito;
    }
    // Busca todos los usuarios activos en la base de datos
    public function buscarTodos() {
        return $this->db->select("usuarios", "*", ["id_estado[!]" => -1]);
    }
    // Busca un usuario por su ID
    public function buscarPorId($id) {
        return $this->db->select("usuarios", "*", ["id" => $id])[0] ?? null;
    }
    // Cambia el estado de un usuario (activo, inactivo, eliminado)
    public function cambiarEstado($id, $nuevoEstado) {
        return $this->db->updateSeguro("usuarios", ["id_estado" => $nuevoEstado], ["id" => $id]);
    }
    // Valida los datos del usuario según la acción (Guardar o Modificar)
    public function validar($data, $accion = 'Guardar'): array {
        $this->controlErrores->limpiarErrores();

        if ($accion === 'Guardar') {
            ValidadorUsuarios::validarRegistro($data, $this->controlErrores, $this->db);
        } elseif ($accion === 'Modificar') {
            ValidadorUsuarios::validarEdicion($data, $this->controlErrores, $this->db);
        }

        return $this->controlErrores->obtenerErrores();
    }
    // Sanitiza los datos del usuario antes de guardarlos o editarlos
    private function sanitizarDatos(array $data): array {
        $data['nombre'] = Sanitizador::limpiarTexto($data['nombre'] ?? '');
        $data['correo'] = Sanitizador::limpiarCorreo($data['correo'] ?? '');
        $data['id_rol'] = isset($data['id_rol']) ? (int)$data['id_rol'] : null;

        if (isset($data['contrasena'])) {
            $data['contrasena'] = trim($data['contrasena']);
        }

        return $data;
    }
    // Obtiene los errores registrados por el ControlErrores
    public function obtenerErrores(): array {
        return $this->controlErrores->obtenerErrores();
    }
    // Obtiene todos los usuarios con sus detalles (rol, estado, creador)
    public static function obtenerUsuariosConDetalles() {
        $conexion = ConexionDB::obtenerInstancia()->obtenerConexion();

        $sql = "SELECT 
                    u.id,
                    u.nombre,
                    u.correo,
                    r.nombre AS rol,
                    u.id_estado,
                    u.creado_en,
                    creador.nombre AS creado_por
                FROM usuarios u
                LEFT JOIN roles r ON u.id_rol = r.id
                LEFT JOIN usuarios creador ON u.creado_por = creador.id 
                WHERE u.id_estado != -1 AND u.id_rol != 4";

        $stmt = $conexion->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Valida las credenciales de un usuario (nombre y contraseña)
    public function validarCredenciales($data): array {
        $this->controlErrores->limpiarErrores();

        if (empty($data['nombre'])) {
            $this->controlErrores->registrarError("El nombre es obligatorio.");
        }

        $password = $data['contrasena'] ?? '';

        if (!Validador::validarPassword($password)) {
            $this->controlErrores->registrarError("La contraseña es inválida.");
        }

        return $this->controlErrores->obtenerErrores();
    }
    // Registra un nuevo lector en la base de datos
    public function registrarLector($nombre, $correo, $contrasena) {
        $nombre = Sanitizador::limpiarTexto($nombre);
        $correo = Sanitizador::limpiarCorreo($correo);
        $contrasena = trim($contrasena);

        if ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($contrasena) < 4) {
            return ['exito' => false, 'mensaje' => 'Completa todos los campos correctamente (contraseña mínimo 4 caracteres).'];
        }

        $usuarios = $this->db->select('usuarios', '*', ['correo' => $correo]);
        if (!empty($usuarios)) {
            return ['exito' => false, 'mensaje' => 'El correo ya está registrado.'];
        }

        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $exito = $this->db->insertSeguro('usuarios', [
            'nombre' => $nombre,
            'correo' => $correo,
            'contrasena' => $hash,
            'id_rol' => 4
        ]);

        if ($exito) {
            return ['exito' => true, 'mensaje' => '¡Registro exitoso! Ya puedes iniciar sesión como lector.'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al registrar. Intenta nuevamente.'];
        }
    }
    // Inicia sesión de un lector validando sus credenciales
    public function loginLector($correo, $contrasena) {
        $correo = Sanitizador::limpiarCorreo($correo);
        $contrasena = trim($contrasena);

        if ($correo === '' || $contrasena === '') {
            return ['exito' => false, 'mensaje' => 'Completa todos los campos.'];
        }

        $usuarios = $this->db->select('usuarios', '*', [
            'correo' => $correo,
            'id_rol' => 4
        ]);

        if (!empty($usuarios)) {
            $usuario = $usuarios[0];
            if (password_verify($contrasena, $usuario['contrasena'])) {
                return [
                    'exito' => true,
                    'usuario' => [
                        'id' => $usuario['id'],
                        'nombre' => $usuario['nombre'],
                        'correo' => $usuario['correo'],
                        'id_rol' => $usuario['id_rol']
                    ]
                ];
            } else {
                return ['exito' => false, 'mensaje' => 'Contraseña incorrecta.'];
            }
        } else {
            return ['exito' => false, 'mensaje' => 'Usuario no encontrado o no es lector.'];
        }
    }
}
