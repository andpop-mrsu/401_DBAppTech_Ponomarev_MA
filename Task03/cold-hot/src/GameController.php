<?php

namespace ColdHot;

class GameController
{
    private GameModel $model;
    private GameView $view;

    public function __construct(GameModel $model, GameView $view)
    {
        $this->model = $model;
        $this->view = $view;
    }

    public function startNewGame(): void
    {
        $this->view->showWelcomeMessage();
        $this->view->showDatabaseMessage();

        while (!$this->model->isGameWon()) {
            try {
                $guess = $this->view->promptForGuess();

                if ($guess === 'quit' || $guess === 'exit') {
                    $this->view->showGoodbye();
                    break;
                }

                $hints = $this->model->makeGuess($guess);
                $attempts = $this->model->getAttempts();
                $lastAttempt = end($attempts);

                $this->view->showAttempt(
                    $lastAttempt['number'],
                    $lastAttempt['guess'],
                    $lastAttempt['hints']
                );

            } catch (\InvalidArgumentException $e) {
                $this->view->showError($e->getMessage());
            }
        }

        if ($this->model->isGameWon()) {
            $this->view->showWinMessage(
                $this->model->getSecretNumber(),
                $this->model->getCurrentAttemptNumber()
            );
        }
    }
}