<?php

namespace ColdHot;

class ConsoleApplication
{
    private GameView $view;

    public function __construct()
    {
        $this->view = new GameView();
    }

    public function run(array $argv): void
    {
        $options = $this->parseCommandLineArgs($argv);

        if ($options['help']) {
            $this->view->showHelp();
            return;
        }

        if ($options['list']) {
            $this->view->showListMessage();
            return;
        }

        if ($options['replay'] !== null) {
            $this->view->showReplayMessage($options['replay']);
            return;
        }

        // Запуск новой игры
        $model = new GameModel();
        $controller = new GameController($model, $this->view);
        $controller->startNewGame();
    }

    private function parseCommandLineArgs(array $argv): array
    {
        $options = [
            'new' => false,
            'list' => false,
            'replay' => null,
            'help' => false
        ];

        // Убираем имя скрипта из аргументов
        array_shift($argv);

        foreach ($argv as $arg) {
            switch ($arg) {
                case '--new':
                case '-n':
                    $options['new'] = true;
                    break;
                case '--list':
                case '-l':
                    $options['list'] = true;
                    break;
                case '--help':
                case '-h':
                    $options['help'] = true;
                    break;
                default:
                    if (preg_match('/^--replay=(\d+)$/', $arg, $matches)) {
                        $options['replay'] = (int)$matches[1];
                    } elseif (preg_match('/^-r=(\d+)$/', $arg, $matches)) {
                        $options['replay'] = (int)$matches[1];
                    } elseif (preg_match('/^-r(\d+)$/', $arg, $matches)) {
                        $options['replay'] = (int)$matches[1];
                    }
                    break;
            }
        }

        // Если никакие параметры не указаны, используем режим новой игры
        if (!$options['new'] && !$options['list'] && $options['replay'] === null && !$options['help']) {
            $options['new'] = true;
        }

        return $options;
    }
}