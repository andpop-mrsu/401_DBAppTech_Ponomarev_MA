<?php

namespace ColdHot;

class GameView
{
    public function showWelcomeMessage(): void
    {
        echo "🎮 Игра 'Холодно-Горячо' 🎮\n";
        echo "========================\n";
        echo "Попробуйте угадать трехзначное число с уникальными цифрами.\n";
        echo "Подсказки:\n";
        echo "  • Холодно - нет правильных цифр\n";
        echo "  • Тепло   - есть цифра, но не на своем месте\n";
        echo "  • Горячо  - цифра на своем месте\n\n";
    }

    public function showAttempt(int $attemptNumber, string $guess, array $hints): void
    {
        $hintsString = implode(' ', $hints);
        printf("Попытка %d: %s → %s\n", $attemptNumber, $guess, $hintsString);
    }

    public function showWinMessage(string $secretNumber, int $attempts): void
    {
        echo "\n🎉 Поздравляем! Вы угадали число {$secretNumber}!\n";
        echo "Количество попыток: {$attempts}\n\n";
    }

    public function showDatabaseMessage(): void
    {
        echo "⚠️  Внимание: игра пока не сохраняется в базе данных\n\n";
    }

    public function showListMessage(): void
    {
        echo "📋 Режим просмотра списка игр\n";
        echo "⚠️  База данных пока не подключена\n\n";
    }

    public function showReplayMessage(int $gameId): void
    {
        echo "🔄 Режим повтора игры #{$gameId}\n";
        echo "⚠️  База данных пока не подключена\n\n";
    }

    public function showHelp(): void
    {
        echo "Использование: cold-hot [ПАРАМЕТРЫ]\n\n";
        echo "Параметры:\n";
        echo "  -n, --new           Новая игра (по умолчанию)\n";
        echo "  -l, --list          Список всех сохраненных игр\n";
        echo "  -r ID, --replay=ID  Повтор игры с идентификатором ID\n";
        echo "  -h, --help          Показать эту справку\n\n";
        echo "Примеры:\n";
        echo "  cold-hot              # Новая игра\n";
        echo "  cold-hot --new        # Новая игра\n";
        echo "  cold-hot --list       # Список игр\n";
        echo "  cold-hot --replay=5   # Повтор игры #5\n";
    }

    public function promptForGuess(): string
    {
        echo "Введите ваше предположение (3 цифры): ";
        return trim(fgets(STDIN));
    }

    public function showError(string $message): void
    {
        echo "❌ Ошибка: {$message}\n";
    }

    public function showGoodbye(): void
    {
        echo "Спасибо за игру! До свидания!\n";
    }
}