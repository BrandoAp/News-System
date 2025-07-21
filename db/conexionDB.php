<?php

class ConexionDB {
    private static $instancia = null;
    private $conexion;
    private $host = 'localhost';
    private $user = 'root';
    private $pass = 'Programming/07';
    private $db = 'bd_sistema_noticias';

    private function __construct() {
        try {
            $this->conexion = new PDO(
                "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4",
                $this->user,
                $this->pass
            );
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            die("Lo sentimos, ha ocurrido un error al conectar con la base de datos.");
        }
    }

    public static function obtenerInstancia() {
        if (self::$instancia === null) {
            self::$instancia = new ConexionDB();
        }
        return self::$instancia;
    }

    public function obtenerConexion() {
        return $this->conexion;
    }
}
?>