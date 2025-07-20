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
    public function LastInsertId(): int
{
    return (int)$this->conexion->lastInsertId();
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

    public function delete(string $tableName, array $conditions): bool
    {
        if (empty($conditions)) {
            // Por seguridad, no permitimos eliminaciones sin condiciones
            return false;
        }

        $where = [];
        $params = [];
        foreach ($conditions as $key => $value) {
            $where[] = "{$key} = :{$key}_where";
            $params[":{$key}_where"] = $value;
        }
        $whereSQL = implode(" AND ", $where);
        
        $sql = "DELETE FROM {$tableName} WHERE {$whereSQL}";

        try {
            $stmt = $this->conexion->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en DELETE en {$tableName}: " . $e->getMessage());
            return false;
        }
    }
    public function getPerpage(): int
    {
        return $this->perpage;
    }
    public function setPerpage(int $perpage): void
    {
        $this->perpage = $perpage;
    }




        
}