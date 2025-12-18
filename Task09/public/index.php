<?php
// Task09/public/index.php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/GameService.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

// CORS для фронтенда
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type');
});

// Preflight OPTIONS
$app->options('/{routes:.+}', function ($request, $response) {
    return $response;
});

// Корень → переадресация на index.html
$app->get('/', function (Request $request, Response $response) {
    return $response->withStatus(302)->withHeader('Location', '/index.html');
});

// REST API маршруты
$app->get('/games', function (Request $request, Response $response) {
    try {
        $service = new GameService();
        $games = $service->getAllGames();
        $payload = json_encode($games, JSON_UNESCAPED_UNICODE);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => 'Ошибка при получении игр']));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

$app->get('/games/{id}', function (Request $request, Response $response, $args) {
    try {
        $id = (int)$args['id'];
        $service = new GameService();
        $game = $service->getGameById($id);
        if (!$game) {
            $response->getBody()->write(json_encode(['error' => 'Игра не найдена']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        $payload = json_encode($game, JSON_UNESCAPED_UNICODE);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => 'Ошибка загрузки игры']));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

$app->post('/games', function (Request $request, Response $response) {
    $data = json_decode($request->getBody(), true);
    if (!$data || !isset($data['player_name']) || !isset($data['secret_number'])) {
        $response->getBody()->write(json_encode(['error' => 'Требуются player_name и secret_number']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    try {
        $service = new GameService();
        $id = $service->createGame($data['player_name'], $data['secret_number']);
        $payload = json_encode(['id' => $id], JSON_UNESCAPED_UNICODE);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => 'Не удалось создать игру']));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

$app->post('/step/{id}', function (Request $request, Response $response, $args) {
    $id = (int)$args['id'];
    $data = json_decode($request->getBody(), true);
    if (!$data || !isset($data['attempt_number']) || !isset($data['guess']) || !isset($data['result'])) {
        $response->getBody()->write(json_encode(['error' => 'Недостаточно данных']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    try {
        $service = new GameService();
        $outcome = $data['outcome'] ?? 'in_progress';
        $success = $service->addStep($id, $data['attempt_number'], $data['guess'], $data['result'], $outcome);
        if (!$success) {
            $response->getBody()->write(json_encode(['error' => 'Игра не найдена']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        $payload = json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => 'Ошибка сохранения хода']));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

$app->run();