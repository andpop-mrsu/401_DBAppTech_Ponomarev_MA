<?php

namespace ColdHot;

class GameModel
{
    private string $secretNumber;
    private array $attempts = [];
    private int $currentAttempt = 0;
    private bool $isWon = false;

    public function __construct()
    {
        $this->generateSecretNumber();
    }

    private function generateSecretNumber(): void
    {
        $digits = range(0, 9);

        do {
            shuffle($digits);
            $number = implode('', array_slice($digits, 0, 3));
        } while ($number[0] === '0');

        $this->secretNumber = $number;
    }

    public function makeGuess(string $guess): array
    {
        if (!$this->isValidGuess($guess)) {
            throw new \InvalidArgumentException('Неверный формат числа. Используйте 3 уникальные цифры, первая не 0.');
        }

        $this->currentAttempt++;
        $hints = $this->calculateHints($guess);

        $attempt = [
            'number' => $this->currentAttempt,
            'guess' => $guess,
            'hints' => $hints
        ];

        $this->attempts[] = $attempt;

        if ($guess === $this->secretNumber) {
            $this->isWon = true;
        }

        return $hints;
    }

    private function isValidGuess(string $guess): bool
    {
        // Проверяем, что строка состоит из 3 цифр
        if (!preg_match('/^\d{3}$/', $guess)) {
            return false;
        }

        // Проверяем, что первая цифра не 0
        if ($guess[0] === '0') {
            return false;
        }

        // Проверяем, что все цифры уникальны
        $digits = str_split($guess);
        return count($digits) === count(array_unique($digits));
    }

    private function calculateHints(string $guess): array
    {
        $hints = [];
        $secretDigits = str_split($this->secretNumber);
        $guessDigits = str_split($guess);

        // Проверяем точные совпадения (горячо)
        for ($i = 0; $i < 3; $i++) {
            if ($secretDigits[$i] === $guessDigits[$i]) {
                $hints[] = 'Горячо';
            }
        }

        // Проверяем цифры на других позициях (тепло)
        for ($i = 0; $i < 3; $i++) {
            if ($secretDigits[$i] !== $guessDigits[$i] && in_array($guessDigits[$i], $secretDigits, true)) {
                $hints[] = 'Тепло';
            }
        }

        // Если нет совпадений - холодно
        if (empty($hints)) {
            $hints[] = 'Холодно';
        }

        // Сортируем подсказки в алфавитном порядке
        sort($hints);

        return $hints;
    }

    public function isGameWon(): bool
    {
        return $this->isWon;
    }

    public function getSecretNumber(): string
    {
        return $this->secretNumber;
    }

    public function getAttempts(): array
    {
        return $this->attempts;
    }

    public function getCurrentAttemptNumber(): int
    {
        return $this->currentAttempt;
    }
}