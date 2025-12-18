<?php

class Database {
    private $pdo;

    public function __construct() {
        $dbPath = __DIR__ . '/../db/games.db';
        try {
            $this->pdo = new PDO("sqlite:$dbPath");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Не удалось подключиться к базе данных: " . $e->getMessage());
        }
    }

    public function prepare($sql) {
        return $this->pdo->prepare($sql);
    }

    public function executeUpdate($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Декодируем JSON в attempts.result
        foreach ($rows as &$row) {
            if (isset($row['result'])) {
                $row['result'] = json_decode($row['result'], true);
            }
        }
        return $rows;
    }

    public function fetchOne($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && isset($row['result'])) {
            $row['result'] = json_decode($row['result'], true);
        }
        return $row;
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}