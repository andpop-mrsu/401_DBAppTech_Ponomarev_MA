<?php

class Database {
    private $pdo;

    public function __construct() {
        $dbPath = __DIR__ . '/../db/games.db';
        if (!file_exists($dbPath)) {
            throw new Exception("База данных не найдена: $dbPath");
        }
        try {
            $this->pdo = new PDO("sqlite:$dbPath");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("SQLite ошибка: " . $e->getMessage());
        }
    }

    public function prepare($sql) { return $this->pdo->prepare($sql); }
    public function fetchAll($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function fetchOne($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function lastInsertId() { return $this->pdo->lastInsertId(); }
}