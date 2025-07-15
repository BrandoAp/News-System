<?php

class DatabaseManager
{
    private $conexion;
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

        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $key => $value) {
                $whereClauses[] = "{$key} = :{$key}_where";
                $params[":{$key}_where"] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
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
}