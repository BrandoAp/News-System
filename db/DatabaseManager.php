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

    public function obtenerConexion() {
        return $this->conexion;
    }

    /**
     * Elimina de forma segura registros de una tabla.
     * @param string $table Nombre de la tabla.
     * @param array $conditions Condiciones para la eliminación.
     * @return bool Resultado de la operación.
     */
    public function deleteSeguro(string $table, array $conditions): bool
    {
        if (empty($conditions)) {
            return false;
        }

        $whereClauses = [];
        $params = [];
        foreach ($conditions as $key => $value) {
            $whereClauses[] = "{$key} = :{$key}_where";
            $params[":{$key}_where"] = $value;
        }
        $whereSQL = implode(" AND ", $whereClauses);

        $sql = "DELETE FROM {$table} WHERE {$whereSQL}";

        try {
            $stmt = $this->conexion->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en DELETE seguro en {$table}: " . $e->getMessage());
            return false;
        }
    }
}

