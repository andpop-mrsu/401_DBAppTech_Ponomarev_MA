class GameController {
    constructor() {
        this.model = new GameModel();
        this.view = new GameView();
        this.maxAttempts = 10;
        this.isGameActive = false;

        this.initialize();
    }

    initialize() {
        // Устанавливаем обработчики событий
        this.view.onStartGame = (playerName) => this.startNewGame(playerName);
        this.view.onMakeGuess = (guess) => this.makeGuess(guess);
        this.view.onShowGamesList = () => this.getAllGames();
    }

    async startNewGame(playerName) {
        await this.model.startNewGame(playerName);
        this.isGameActive = true;
        this.view.startGame(playerName);
    }

    async makeGuess(guess) {
        if (!this.isGameActive) return;
        const result = this.model.makeGuess(guess);
        if (result.error) {
            this.view.showError(result.error);
            return;
        }

        const attempts = this.model.getAttempts();
        this.view.updateGameState(attempts);

        let outcome = 'in_progress';
        if (result.won) {
            this.isGameActive = false;
            outcome = 'won';
            this.view.showWin(attempts.length, this.model.getSecretNumber());
        } else if (attempts.length >= this.maxAttempts) {
            this.isGameActive = false;
            outcome = 'lost';
            this.view.showLoss(attempts.length, this.model.getSecretNumber());
        }

        // Сохраняем ход на сервере
        await this.model.saveStep(outcome);
    }

    async getAllGames() {
        return await this.model.getAllGames();
    }

    async replayGame(id) {
        const gameData = await this.model.loadGame(id);
        if (gameData) {
            this.view.showReplay(gameData);
        } else {
            alert('Игра не найдена!');
        }
    }

    async viewGame(id) {
        const gameData = await this.model.loadGame(id);
        if (gameData) {
            this.view.showReplay(gameData);
        } else {
            alert('Игра не найдена!');
        }
    }
}