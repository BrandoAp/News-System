<?php

class DatabaseManager
{
    protected $conexion;
    private $perpage = 3;
    private $total;
    private $pagecut_query;

    public function __construct(PDO $pdoConnection)
    {
        $this->conexion = $pdoConnection;
    }
    


    public function select(string $tableName, string $columns = '*', array $conditions = []): array
{
    $sql = "SELECT {$columns} FROM {$tableName}";
    $params = [];
    $limit = '';

    if (!empty($conditions)) {
        $whereClauses = [];
        foreach ($conditions as $key => $value) {
            // Manejo especial para LIMIT
            if ($key === 'LIMIT') {
                $limit = " LIMIT " . implode(", ", $value);
                continue;
            }
            
            // Manejo de operadores especiales (LIKE, OR, etc.)
            if (strpos($key, '[~]') !== false) {
                $cleanKey = str_replace('[~]', '', $key);
                $whereClauses[] = "{$cleanKey} LIKE :{$cleanKey}_where";
                $params[":{$cleanKey}_where"] = $value;
            } elseif (strpos($key, '[OR]') !== false) {
                $orConditions = [];
                foreach ($value as $orKey => $orValue) {
                    $cleanOrKey = str_replace('[~]', '', $orKey);
                    $orConditions[] = "{$cleanOrKey} LIKE :{$cleanOrKey}_or";
                    $params[":{$cleanOrKey}_or"] = $orValue;
                }
                $whereClauses[] = "(" . implode(" OR ", $orConditions) . ")";
            } else {
                $whereClauses[] = "{$key} = :{$key}_where";
                $params[":{$key}_where"] = $value;
            }
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }
    }

    if (!empty($limit)) {
        $sql .= $limit;
    }

    try {
        $stmt = $this->conexion->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al hacer SELECT en {$tableName}: " . $e->getMessage());
        return [];
    }
}

    public function insertSeguro(string $tableName, array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));

        $sql = "INSERT INTO {$tableName} ({$columns}) VALUES ({$placeholders})";

        try {
            $stmt = $this->conexion->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en INSERT seguro en {$tableName}: " . $e->getMessage());
            return false;
        }
    }

    public function updateSeguro(string $tableName, array $data, array $conditions): bool
    {
        if (empty($data) || empty($conditions)) {
            return false;
        }

        $set = [];
        $params = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}_set";
            $params[":{$key}_set"] = $value;
        }
        $setSQL = implode(", ", $set);

        $where = [];
        foreach ($conditions as $key => $value) {
            $where[] = "{$key} = :{$key}_where";
            $params[":{$key}_where"] = $value;
        }
        $whereSQL = implode(" AND ", $where);
        
        $sql = "UPDATE {$tableName} SET {$setSQL} WHERE {$whereSQL}";

        try {
            $stmt = $this->conexion->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en UPDATE seguro en {$tableName}: " . $e->getMessage());
            return false;
        }
    }

    public function count($table, $conditions = [])
{
    $sql = "SELECT COUNT(*) as total FROM `$table`";

    if (!empty($conditions)) {
        $where = [];
        foreach ($conditions as $key => $value) {
            $where[] = "`$key` = :$key";
        }
        $sql .= " WHERE " . implode(' AND ', $where);
    }

    $stmt = $this->conexion->prepare($sql);
    foreach ($conditions as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ? intval($result['total']) : 0;
}


    /**
     * Obtiene el ID de la última fila insertada.
     * @return string|false El ID o false si no hay ID.
     */
    public function lastInsertId() {
        return $this->conexion->lastInsertId();
    }

        /**
     * 🛠️ NUEVO MÉTODO
     * Ejecuta una consulta SQL y devuelve todos los resultados.
     * Ideal para sentencias SELECT que devuelven múltiples filas.
     *
     * @param string $sql La consulta SQL a ejecutar.
     * @param array $params Los parámetros para la consulta preparada.
     * @return array Un array de resultados.
     */
    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // En un entorno de producción, loguea el error en lugar de mostrarlo.
            error_log('Database Query Error: ' . $e->getMessage());
            return []; // Devuelve un array vacío en caso de error.
        }
    }

    /**
     * ✨ NUEVO MÉTODO
     * Ejecuta una consulta y devuelve un único valor escalar (de la primera columna de la primera fila).
     * Perfecto para consultas como COUNT(*), SUM(), etc.
     *
     * @param string $sql La consulta SQL a ejecutar.
     * @param array $params Los parámetros para la consulta preparada.
     * @return mixed El valor escalar o null si no hay resultados.
     */
    public function scalar(string $sql, array $params = [])
    {
        try {
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Database Scalar Error: ' . $e->getMessage());
            return null; // Devuelve null en caso de error.
        }
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

        // Verificar si ya hay una visita de esta IP hoy
        $sql = "SELECT id, visitas FROM visitantes WHERE ip = :ip AND fecha = :hoy";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute(['ip' => $ip, 'hoy' => $hoy]);
        $registro = $stmt->fetch();

        if ($registro) {
            // Ya existe una visita hoy para esta IP → actualizar contador
            $nuevoContador = $registro['visitas'] + 1;
            $update = $this->conexion->prepare("UPDATE visitantes SET visitas = :visitas WHERE id = :id");
            $update->execute(['visitas' => $nuevoContador, 'id' => $registro['id']]);
        } else {
            // No existe una visita de esta IP hoy → insertar nuevo registro
            $insert = $this->conexion->prepare("INSERT INTO visitantes (ip, fecha, visitas) VALUES (:ip, :hoy, 1)");
            $insert->execute(['ip' => $ip, 'hoy' => $hoy]);
        }

        // Contar todas las visitas de hoy (sumando el campo visitas)
        $sqlTotal = "SELECT SUM(visitas) as total FROM visitantes WHERE fecha = :hoy";
        $stmtTotal = $this->conexion->prepare($sqlTotal);
        $stmtTotal->execute(['hoy' => $hoy]);
        $row = $stmtTotal->fetch();
        return $row && $row['total'] !== null ? intval($row['total']) : 0;
    }

    public function obtenerConexion()
    {
        return $this->conexion;
    }

    // Obtener comentarios de una noticia con nombre del usuario
    public function obtenerComentariosDeNoticia($idNoticia) {
        $sql = "SELECT c.*, u.nombre 
                FROM comentarios c 
                JOIN usuarios u ON c.id_usuario = u.id 
                WHERE c.id_noticia = :idNoticia 
                ORDER BY c.creado_en DESC";
        return $this->query($sql, ['idNoticia' => $idNoticia]);
    }

    // Insertar comentario
    public function agregarComentario($idNoticia, $idUsuario, $contenido) {
        return $this->insertSeguro('comentarios', [
            'id_noticia' => $idNoticia,
            'id_usuario' => $idUsuario,
            'contenido' => $contenido,
            'creado_en' => date('Y-m-d H:i:s')
        ]);
    }

    // Contar reacciones por tipo
    public function contarReacciones($idNoticia, $idTipoReaccion) {
        $sql = "SELECT COUNT(*) FROM likes WHERE id_noticia = :idNoticia AND id_tipo_reaccion = :idTipoReaccion";
        return $this->scalar($sql, ['idNoticia' => $idNoticia, 'idTipoReaccion' => $idTipoReaccion]);
    }

    // Verificar si el usuario ya reaccionó
    public function usuarioYaReacciono($idUsuario, $idNoticia, $idTipoReaccion) {
        $sql = "SELECT id FROM likes WHERE id_usuario = :idUsuario AND id_noticia = :idNoticia AND id_tipo_reaccion = :idTipoReaccion";
        $res = $this->query($sql, [
            'idUsuario' => $idUsuario,
            'idNoticia' => $idNoticia,
            'idTipoReaccion' => $idTipoReaccion
        ]);
        return !empty($res);
    }

    // Insertar reacción
    public function agregarReaccion($idUsuario, $idNoticia, $idTipoReaccion) {
        return $this->insertSeguro('likes', [
            'id_usuario' => $idUsuario,
            'id_noticia' => $idNoticia,
            'id_tipo_reaccion' => $idTipoReaccion,
            'creado_en' => date('Y-m-d H:i:s')
        ]);
    }

    public function obtenerTiposReaccion() {
        $sql = "SELECT * FROM tipos_reaccion";
        return $this->query($sql);
    }
    
}