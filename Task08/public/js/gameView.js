class GameView {
    constructor() {
        this.screens = {
            welcome: document.getElementById('welcomeScreen'),
            game: document.getElementById('gameScreen'),
            list: document.getElementById('listScreen')
        };

        this.elements = {
            playerForm: document.getElementById('playerForm'),
            playerName: document.getElementById('playerName'),
            currentPlayer: document.getElementById('currentPlayer'),
            attemptsCount: document.getElementById('attemptsCount'),
            secretNumber: document.getElementById('secretNumber'),
            guessInput: document.getElementById('guessInput'),
            submitGuess: document.getElementById('submitGuess'),
            attemptsList: document.getElementById('attemptsList'),
            gamesList: document.getElementById('gamesList'),

            // Модальные окна
            winModal: document.getElementById('winModal'),
            winSecretNumber: document.getElementById('winSecretNumber'),
            winAttemptsCount: document.getElementById('winAttemptsCount'),
            newGameAfterWin: document.getElementById('newGameAfterWin'),
            closeWinModal: document.getElementById('closeWinModal'),

            lossModal: document.getElementById('lossModal'),
            lossSecretNumber: document.getElementById('lossSecretNumber'),
            lossAttemptsCount: document.getElementById('lossAttemptsCount'),
            newGameAfterLoss: document.getElementById('newGameAfterLoss'),
            closeLossModal: document.getElementById('closeLossModal')
        };

        this.isGameActive = false;
        this.initializeEventListeners();
    }

    initializeEventListeners() {
        // Форма игрока
        this.elements.playerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const playerName = this.elements.playerName.value.trim();
            if (playerName) {
                this.onStartGame(playerName);
            }
        });

        // Ввод догадки
        this.elements.submitGuess.addEventListener('click', () => this.submitGuess());
        this.elements.guessInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.submitGuess();
            }
        });

        // Ограничение ввода только цифр
        this.elements.guessInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });

        // Навигация
        document.getElementById('newGameBtn').addEventListener('click', () => this.showScreen('welcome'));
        document.getElementById('listGamesBtn').addEventListener('click', () => this.showGamesList());
        document.getElementById('backFromList').addEventListener('click', () => this.showScreen('welcome'));

        // Модальное окно победы
        this.elements.newGameAfterWin.addEventListener('click', () => {
            this.hideWinModal();
            this.showScreen('welcome');
        });

        this.elements.closeWinModal.addEventListener('click', () => {
            this.hideWinModal();
            this.showScreen('welcome');
        });

        // Модальное окно поражения
        this.elements.newGameAfterLoss.addEventListener('click', () => {
            this.hideLossModal();
            this.showScreen('welcome');
        });

        this.elements.closeLossModal.addEventListener('click', () => {
            this.hideLossModal();
            this.showScreen('welcome');
        });

        // Закрытие модальных окон по клику вне их
        this.elements.winModal.addEventListener('click', (e) => {
            if (e.target === this.elements.winModal) {
                this.hideWinModal();
                this.showScreen('welcome');
            }
        });

        this.elements.lossModal.addEventListener('click', (e) => {
            if (e.target === this.elements.lossModal) {
                this.hideLossModal();
                this.showScreen('welcome');
            }
        });

        document.getElementById('backFromGame').addEventListener('click', () => {
            this.showGamesList();
        });
    }

    showScreen(screenName) {
        // Скрыть все экраны
        Object.values(this.screens).forEach(screen => {
            screen.classList.remove('active');
        });

        // Показать выбранный экран
        this.screens[screenName].classList.add('active');

        // Сбросить форму при показе welcome экрана
        if (screenName === 'welcome') {
            this.elements.playerForm.reset();
            this.isGameActive = false;
        }
    }

    onStartGame(callback) {
        this.onStartGame = callback;
    }

    onMakeGuess(callback) {
        this.onMakeGuess = callback;
    }

    onShowGamesList(callback) {
        this.onShowGamesList = callback;
    }

    startGame(playerName) {
        this.elements.currentPlayer.textContent = playerName;
        this.elements.attemptsCount.textContent = '0';
        this.elements.secretNumber.textContent = '???';
        this.elements.attemptsList.innerHTML = '';
        this.elements.guessInput.value = '';
        this.elements.guessInput.disabled = false;
        this.elements.submitGuess.disabled = false;
        this.isGameActive = true;
        this.elements.guessInput.focus();

        this.showScreen('game');
    }

    submitGuess() {
        if (!this.isGameActive) return;

        const guess = this.elements.guessInput.value;
        if (guess.length === 3) {
            this.onMakeGuess(guess);
            this.elements.guessInput.value = '';
        }
    }

    updateGameState(attempts) {
        if (!this.isGameActive) return;

        this.elements.attemptsCount.textContent = attempts.length;

        // Обновляем список попыток
        this.elements.attemptsList.innerHTML = '';
        attempts.forEach(attempt => {
            const attemptElement = this.createAttemptElement(attempt);
            this.elements.attemptsList.appendChild(attemptElement);
        });

        // Прокручиваем к последней попытке
        this.elements.attemptsList.scrollTop = this.elements.attemptsList.scrollHeight;
    }

    createAttemptElement(attempt) {
        const div = document.createElement('div');
        div.className = 'attempt';

        const hintsHtml = attempt.result.map(hint =>
            `<span class="hint ${hint}">${this.getHintText(hint)}</span>`
        ).join(' ');

        div.innerHTML = `
            <span class="attempt-number">${attempt.number}.</span>
            <span class="attempt-guess">${attempt.guess}</span>
            <div class="hints">${hintsHtml}</div>
        `;

        return div;
    }

    getHintText(hint) {
        const hintsMap = {
            'cold': 'Холодно',
            'warm': 'Тепло',
            'hot': 'Горячо'
        };
        return hintsMap[hint];
    }

    showWin(attempts, secretNumber) {
        this.endGame();
        this.elements.secretNumber.textContent = secretNumber;
        this.elements.winSecretNumber.textContent = secretNumber;
        this.elements.winAttemptsCount.textContent = attempts;
        this.showWinModal();
    }

    showLoss(attempts, secretNumber) {
        this.endGame();
        this.elements.secretNumber.textContent = secretNumber;
        this.elements.lossSecretNumber.textContent = secretNumber;
        this.elements.lossAttemptsCount.textContent = attempts;
        this.showLossModal();
    }

    showWinModal() {
        this.elements.winModal.classList.remove('hidden');
    }

    hideWinModal() {
        this.elements.winModal.classList.add('hidden');
    }

    showLossModal() {
        this.elements.lossModal.classList.remove('hidden');
    }

    hideLossModal() {
        this.elements.lossModal.classList.add('hidden');
    }

    endGame() {
        this.isGameActive = false;
        this.elements.guessInput.disabled = true;
        this.elements.submitGuess.disabled = true;
    }

    showError(message) {
        alert(message);
    }

    async showGamesList() {
        try {
            const games = await this.onShowGamesList();

            if (!games || games.length === 0) {
                this.elements.gamesList.innerHTML = '<p class="text-center">Нет сохраненных игр</p>';
                return;
            }

            this.elements.gamesList.innerHTML = games.map(game => `
            <div class="game-item ${game.outcome === 'won' ? 'won' : 'lost'}" 
                 onclick="gameController.replayGame(${game.id})">
                <strong>Игра #${game.id}</strong> - ${new Date(game.date).toLocaleString()}<br>
                Игрок: ${game.player_name} | Число: ${game.secret_number}<br>
                Результат: ${game.outcome === 'won' ? '🎉 Победа' : '💔 Поражение'} | 
                Попыток: ${game.attempts ? game.attempts.length : 0}
            </div>
        `).join('');

            this.showScreen('list');
        } catch (error) {
            console.error('Ошибка при загрузке списка игр:', error);
            this.elements.gamesList.innerHTML = '<p class="text-center">Ошибка при загрузке списка игр</p>';
        }
    }

    showReplay(gameData) {
        this.elements.currentPlayer.textContent = gameData.player_name;
        this.elements.attemptsCount.textContent = gameData.attempts.length;
        this.elements.secretNumber.textContent = gameData.secret_number;
        this.elements.attemptsList.innerHTML = '';

        // Показываем все попытки
        gameData.attempts.forEach(attempt => {
            const attemptElement = this.createAttemptElement(attempt);
            this.elements.attemptsList.appendChild(attemptElement);
        });

        // Делаем поле ввода неактивным для replay
        this.elements.guessInput.disabled = true;
        this.elements.submitGuess.disabled = true;
        this.isGameActive = false;

        // Добавляем индикатор режима просмотра
        const gameHeader = document.querySelector('.game-header h2');
        if (gameHeader) {
            gameHeader.innerHTML = `Просмотр игры: <span id="currentPlayer">${gameData.player_name}</span> <small>(Режим просмотра)</small>`;
        }

        this.showScreen('game');
    }
}