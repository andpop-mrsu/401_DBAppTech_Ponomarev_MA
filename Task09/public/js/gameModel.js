// Task08/public/js/gameModel.js

class GameModel {
    constructor() {
        this.secretNumber = '';
        this.attempts = [];
        this.isWon = false;
        this.playerName = '';
        this.gameId = null;
    }

    // НЕТ init() — не нужно!
    // НЕТ IndexedDB!

    generateSecretNumber() {
        const digits = [...Array(10).keys()];
        for (let i = digits.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [digits[i], digits[j]] = [digits[j], digits[i]];
        }
        if (digits[0] === 0) [digits[0], digits[1]] = [digits[1], digits[0]];
        return digits.slice(0, 3).join('');
    }

    async startNewGame(playerName) {
        this.playerName = playerName;
        this.secretNumber = this.generateSecretNumber();
        this.attempts = [];
        this.isWon = false;

        // Отправляем запрос на сервер
        const response = await fetch('/games', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                player_name: playerName,
                secret_number: this.secretNumber
            })
        });

        if (!response.ok) throw new Error('Не удалось создать игру на сервере');
        const data = await response.json();
        this.gameId = data.id;
        return this.gameId;
    }

    makeGuess(guess) {
        if (!/^[0-9]{3}$/.test(guess)) {
            return { error: 'Введите ровно 3 цифры' };
        }
        if (new Set(guess).size !== 3) {
            return { error: 'Все цифры должны быть уникальными' };
        }

        const hints = this.getHints(guess);
        const attemptNumber = this.attempts.length + 1;
        const attempt = { number: attemptNumber, guess, result: hints };
        this.attempts.push(attempt);
        const won = guess === this.secretNumber;
        this.isWon = won;

        return { hints, won, attempt };
    }

    getHints(guess) {
        const secret = this.secretNumber.split('');
        const g = guess.split('');
        return g.map((digit, i) =>
            digit === secret[i] ? 'hot' :
                secret.includes(digit) ? 'warm' : 'cold'
        ).sort();
    }

    getSecretNumber() { return this.secretNumber; }
    getAttempts() { return this.attempts; }
    isGameWon() { return this.isWon; }

    // Отправка хода на сервер
    async saveStep(outcome = 'in_progress') {
        if (!this.gameId) return;

        const attempt = this.attempts[this.attempts.length - 1];
        if (!attempt) return;

        await fetch(`/step/${this.gameId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                attempt_number: attempt.number,
                guess: attempt.guess,
                result: attempt.result,
                outcome
            })
        });
    }

    // Загрузка списка игр
    async getAllGames() {
        const res = await fetch('/games');
        if (!res.ok) throw new Error('Ошибка загрузки игр');
        const games = await res.json();
        return games.map(g => ({
            ...g,
            attempts: g.attempts ? g.attempts.map(a => ({
                ...a,
                result: Array.isArray(a.result) ? a.result : JSON.parse(a.result)
            })) : []
        }));
    }

    // Загрузка одной игры
    async loadGame(id) {
        const res = await fetch(`/games/${id}`);
        if (!res.ok) throw new Error('Игра не найдена');
        const game = await res.json();
        if (game.attempts) {
            game.attempts = game.attempts.map(a => ({
                ...a,
                result: Array.isArray(a.result) ? a.result : JSON.parse(a.result)
            }));
        }
        return game;
    }
}