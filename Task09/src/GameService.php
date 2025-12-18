<?php
// Task09/src/GameService.php

require_once __DIR__ . '/Database.php';

class GameService {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllGames() {
        return $this->db->fetchAll("SELECT * FROM games ORDER BY date DESC");
    }

    public function getGameById($id) {
        $game = $this->db->fetchOne("SELECT * FROM games WHERE id = ?", [$id]);
        if (!$game) return null;
        $attempts = $this->db->fetchAll("SELECT * FROM attempts WHERE game_id = ? ORDER BY attempt_number", [$id]);
        $game['attempts'] = $attempts;
        return $game;
    }

    public function createGame($playerName, $secretNumber) {
        $stmt = $this->db->prepare("INSERT INTO games (date, player_name, secret_number, outcome) VALUES (?, ?, ?, 'in_progress')");
        $date = date('c');
        $stmt->execute([$date, $playerName, $secretNumber]);
        return $this->db->lastInsertId();
    }

    public function addStep($gameId, $attemptNumber, $guess, $result, $outcome = 'in_progress') {
        // Проверяем существование игры
        if (!$this->db->fetchOne("SELECT id FROM games WHERE id = ?", [$gameId])) {
            return false;
        }
        $stmt = $this->db->prepare("INSERT INTO attempts (game_id, attempt_number, guess, result) VALUES (?, ?, ?, ?)");
        $stmt->execute([$gameId, $attemptNumber, $guess, json_encode($result, JSON_UNESCAPED_UNICODE)]);
        if ($outcome !== 'in_progress') {
            $this->db->prepare("UPDATE games SET outcome = ? WHERE id = ?")->execute([$outcome, $gameId]);
        }
        return true;
    }
}