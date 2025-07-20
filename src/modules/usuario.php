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
    

    public function guardar($data) {
        $data = $this->sanitizarDatos($data);
        return $this->db->insertSeguro("usuarios", $data);
    }

    public function editar($id, $data) {
        $this->controlErrores->limpiarErrores();
        $data = $this->sanitizarDatos($data);
        $errores = $this->validar($data, 'Modificar');
        if (!empty($errores)) return false;

        return $this->db->updateSeguro("usuarios", $data, ["id" => $id]);
    }

    public function buscarTodos() {
        return $this->db->select("usuarios", "*", ["id_estado[!]" => -1]);
    }

    public function buscarPorId($id) {
        return $this->db->select("usuarios", "*", ["id" => $id])[0] ?? null;
    }

    public function cambiarEstado($id, $nuevoEstado) {
        return $this->db->updateSeguro("usuarios", ["id_estado" => $nuevoEstado], ["id" => $id]);
    }

    public function validar($data, $accion = 'Guardar'): array {
        $this->controlErrores->limpiarErrores();

        if ($accion === 'Guardar') {
            ValidadorUsuarios::validarRegistro($data, $this->controlErrores, $this->db);
        } elseif ($accion === 'Modificar') {
            ValidadorUsuarios::validarEdicion($data, $this->controlErrores, $this->db);
        }

        return $this->controlErrores->obtenerErrores();
    }


    //sanatizar obvio microbi
    private function sanitizarDatos(array $data): array {
         $data['nombre'] = Sanitizador::limpiarTexto($data['nombre'] ?? '');
         $data['correo'] = Sanitizador::limpiarCorreo($data['correo'] ?? '');
         $data['id_rol'] = isset($data['id_rol']) ? (int)$data['id_rol'] : null;
         if (isset($data['contrasena'])) {
             $data['contrasena'] = trim($data['contrasena']);
            }
        return $data;
    }

    public function obtenerErrores(): array {
        return $this->controlErrores->obtenerErrores();
    }
    //eto es pa agregar lo de roles y creado por jjsjs
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
                WHERE u.id_estado != -1";

        $stmt = $conexion->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    //funcion papurri pa validar el login oh si oh si
    public function validarLogin($data): array {
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

}


