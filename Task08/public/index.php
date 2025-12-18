<?php
// Task08/public/index.php

// Определяем путь
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Разрешаем CORS для API
if (strpos($path, '/games') === 0 || strpos($path, '/step') === 0) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Content-Type: application/json; charset=utf-8");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }

    // Обработка API
    $segments = explode('/', trim($path, '/'));

    if ($segments[0] === 'games') {
        require_once __DIR__ . '/../src/GameController.php';
        $controller = new GameController();

        if (count($segments) === 1) {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $controller->getAllGames();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->createGame();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Метод не разрешён']);
            }
        } elseif (count($segments) === 2 && is_numeric($segments[1])) {
            $controller->getGameById((int)$segments[1]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Маршрут не найден']);
        }
    } elseif ($segments[0] === 'step' && count($segments) === 2 && is_numeric($segments[1])) {
        require_once __DIR__ . '/../src/GameController.php';
        $controller = new GameController();
        $controller->addStep((int)$segments[1]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'API не найден']);
    }
    exit;
}

// Иначе — отдаём статический фронтенд
if ($path === '/' || $path === '/index.html') {
    readfile(__DIR__ . '/index.html');
    return;
}

// Для других статических файлов — PHP-сервер сам их отдаст
// (например, /css/style.css, /js/app.js и т.д.)