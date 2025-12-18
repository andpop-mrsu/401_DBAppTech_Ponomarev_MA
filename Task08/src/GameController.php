<?php
require_once __DIR__ . '/Database.php';

class GameController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllGames() {
        try {
            $games = $this->db->fetchAll("SELECT * FROM games ORDER BY date DESC");
            echo json_encode($games, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка при получении игр']);
        }
    }

    public function getGameById($id) {
        try {
            $game = $this->db->fetchOne("SELECT * FROM games WHERE id = ?", [$id]);
            if (!$game) {
                http_response_code(404);
                echo json_encode(['error' => 'Игра не найдена']);
                return;
            }
            $attempts = $this->db->fetchAll("SELECT * FROM attempts WHERE game_id = ? ORDER BY attempt_number", [$id]);
            $game['attempts'] = $attempts;
            echo json_encode($game, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка при загрузке игры']);
        }
    }

    public function createGame() {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['player_name']) || !isset($input['secret_number'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Требуются player_name и secret_number']);
            return;
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO games (date, player_name, secret_number, outcome) VALUES (?, ?, ?, 'in_progress')");
            $date = date('c'); // ISO 8601
            $stmt->execute([$date, $input['player_name'], $input['secret_number']]);
            $id = $this->db->lastInsertId();
            echo json_encode(['id' => $id], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Не удалось создать игру']);
        }
    }

    public function addStep($gameId) {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['attempt_number']) || !isset($input['guess']) || !isset($input['result'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Требуются attempt_number, guess, result']);
            return;
        }

        try {
            // Убедимся, что игра существует
            $game = $this->db->fetchOne("SELECT * FROM games WHERE id = ?", [$gameId]);
            if (!$game) {
                http_response_code(404);
                echo json_encode(['error' => 'Игра не найдена']);
                return;
            }

            $stmt = $this->db->prepare("INSERT INTO attempts (game_id, attempt_number, guess, result) VALUES (?, ?, ?, ?)");
            $stmt->execute([$gameId, $input['attempt_number'], $input['guess'], json_encode($input['result'], JSON_UNESCAPED_UNICODE)]);

            // Обновляем outcome, если нужно
            if (isset($input['outcome'])) {
                $this->db->executeUpdate("UPDATE games SET outcome = ? WHERE id = ?", [$input['outcome'], $gameId]);
            }

            echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Не удалось сохранить ход']);
        }
    }
}