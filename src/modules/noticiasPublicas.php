<?php
require_once __DIR__ . '/../../db/conexionDB.php';
require_once __DIR__ . '/../../db/DatabaseManager.php';
require_once __DIR__ . '/../validaciones/Sanitizador.php';

class NoticiasPublicas
{
    private $db;
    private $sanitizador;

    public function __construct($conexion = null)
    {
        if ($conexion instanceof DatabaseManager) {
            $this->db = $conexion;
        } else {
            $conexion = ConexionDB::obtenerInstancia()->obtenerConexion();
            $this->db = new DatabaseManager($conexion);
        }
        $this->sanitizador = new Sanitizador();
    }

    /**
     * Cuenta la cantidad de visitas registradas hoy en la tabla visitantes.
     * Si no existen visitas hoy, inserta una nueva visita para la IP actual.
     * @return int
     */
    public function contarVisitasHoy(): int
    {
        $hoy = date('Y-m-d');
        $ip = $_SERVER['REMOTE_ADDR'];

        // Sanitizar IP
        $ip = $this->sanitizador->limpiarTexto($ip);

        // Verificar si ya hay una visita de esta IP hoy
        $visita = $this->db->select('visitantes', '*', ['ip' => $ip, 'fecha' => $hoy]);
        if ($visita && isset($visita[0]['id'])) {
            $nuevoContador = $visita[0]['visitas'] + 1;
            $this->db->updateSeguro('visitantes', ['visitas' => $nuevoContador], ['id' => $visita[0]['id']]);
        } else {
            $this->db->insertSeguro('visitantes', [
                'ip' => $ip,
                'fecha' => $hoy,
                'visitas' => 1
            ]);
        }

        // Contar todas las visitas de hoy (sumando el campo visitas)
        $sqlTotal = "SELECT SUM(visitas) as total FROM visitantes WHERE fecha = :hoy";
        $stmtTotal = $this->db->obtenerConexion()->prepare($sqlTotal);
        $stmtTotal->execute(['hoy' => $hoy]);
        $row = $stmtTotal->fetch();
        return $row && $row['total'] !== null ? intval($row['total']) : 0;
    }

    // Obtener comentarios de una noticia con nombre del usuario
    public function obtenerComentariosDeNoticia($idNoticia) {
        // Solo sanitizar como texto, ya que sanitizarEntero no existe
        $idNoticia = $this->sanitizador->limpiarTexto($idNoticia);

        $sql = "SELECT c.*, u.nombre 
                FROM comentarios c 
                JOIN usuarios u ON c.id_usuario = u.id 
                WHERE c.id_noticia = :idNoticia 
                ORDER BY c.creado_en DESC";
        $stmt = $this->db->obtenerConexion()->prepare($sql);
        $stmt->execute(['idNoticia' => $idNoticia]);
        return $stmt->fetchAll();
    }

    // Insertar comentario
    public function agregarComentario($idNoticia, $idUsuario, $contenido) {
        // Sanitizar entradas
        $idNoticia = $this->sanitizador->limpiarTexto($idNoticia);
        $idUsuario = $this->sanitizador->limpiarTexto($idUsuario);
        $contenido = $this->sanitizador->limpiarTexto($contenido);

        return $this->db->insertSeguro('comentarios', [
            'id_noticia' => $idNoticia,
            'id_usuario' => $idUsuario,
            'contenido' => $contenido,
            'creado_en' => date('Y-m-d H:i:s')
        ]);
    }

    // Contar reacciones por tipo
    public function contarReacciones($idNoticia, $idTipoReaccion) {
        // Sanitizar entradas
        $idNoticia = $this->sanitizador->limpiarTexto($idNoticia);
        $idTipoReaccion = $this->sanitizador->limpiarTexto($idTipoReaccion);

        $sql = "SELECT COUNT(*) FROM likes WHERE id_noticia = :idNoticia AND id_tipo_reaccion = :idTipoReaccion";
        $stmt = $this->db->obtenerConexion()->prepare($sql);
        $stmt->execute(['idNoticia' => $idNoticia, 'idTipoReaccion' => $idTipoReaccion]);
        return (int)$stmt->fetchColumn();
    }

    // Verificar si el usuario ya reaccionó
    public function usuarioYaReacciono($idUsuario, $idNoticia, $idTipoReaccion) {
        // Sanitizar entradas
        $idUsuario = $this->sanitizador->limpiarTexto($idUsuario);
        $idNoticia = $this->sanitizador->limpiarTexto($idNoticia);
        $idTipoReaccion = $this->sanitizador->limpiarTexto($idTipoReaccion);

        $res = $this->db->select('likes', '*', [
            'id_usuario' => $idUsuario,
            'id_noticia' => $idNoticia,
            'id_tipo_reaccion' => $idTipoReaccion
        ]);
        return !empty($res);
    }

    // Insertar reacción
    public function agregarReaccion($idUsuario, $idNoticia, $idTipoReaccion) {
        // Sanitizar entradas
        $idUsuario = $this->sanitizador->limpiarTexto($idUsuario);
        $idNoticia = $this->sanitizador->limpiarTexto($idNoticia);
        $idTipoReaccion = $this->sanitizador->limpiarTexto($idTipoReaccion);

        return $this->db->insertSeguro('likes', [
            'id_usuario' => $idUsuario,
            'id_noticia' => $idNoticia,
            'id_tipo_reaccion' => $idTipoReaccion,
            'creado_en' => date('Y-m-d H:i:s')
        ]);
    }

    public function obtenerTiposReaccion() {
        return $this->db->select('tipos_reaccion', '*');
    }
}