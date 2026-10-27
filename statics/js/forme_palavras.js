const levels = [
    { word: "GATO", emoji: "🐱" },
    { word: "BOLA", emoji: "⚽" },
    { word: "CASA", emoji: "🏠" },
    { word: "PATO", emoji: "🦆" },
    { word: "FOGO", emoji: "🔥" }
];

let currentLevelIndex = 0;
let currentWord   = "";
let spelledWord   = "";

const emojiDisplay       = document.getElementById("emoji-display");
const wordContainer      = document.getElementById("word-container");
const lettersContainer   = document.getElementById("letters-container");
const feedbackMessage    = document.getElementById("feedback-message");

function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    return array;
}

function loadLevel() {
    spelledWord           = "";
    feedbackMessage.innerText = "";
    wordContainer.innerHTML   = "";
    lettersContainer.innerHTML = "";

    const level   = levels[currentLevelIndex];
    currentWord   = level.word;
    emojiDisplay.innerText = level.emoji;

    for (let i = 0; i < currentWord.length; i++) {
        const slot = document.createElement("div");
        slot.classList.add("slot");
        wordContainer.appendChild(slot);
    }

    let letters = shuffleArray(currentWord.split(""));

    letters.forEach((letter, index) => {
        const btn = document.createElement("button");
        btn.classList.add("letter-btn");
        btn.innerText    = letter;
        btn.dataset.id   = index;
        btn.onclick      = () => handleLetterClick(letter, btn);
        lettersContainer.appendChild(btn);
    });
}

function handleLetterClick(letter, btnElement) {
    const expectedLetter = currentWord[spelledWord.length];

    if (letter === expectedLetter) {
        const slots = document.querySelectorAll(".slot");
        slots[spelledWord.length].innerText = letter;
        spelledWord += letter;
        btnElement.disabled = true;

        // ── Salva acerto ──────────────────────────────────────────────────
        salvarJogada({
            jogo_slug:      'forme_palavras',
            fase:           `nivel_${currentLevelIndex + 1}`,
            movimento_tipo: 'compor_palavra',
            movimento:      { palavra: currentWord, letra: letter, posicao: spelledWord.length - 1 },
            correta:        true,
        });
        // ─────────────────────────────────────────────────────────────────

        if (spelledWord === currentWord) handleWin();
    } else {
        btnElement.style.transform = "translateX(5px)";
        setTimeout(() => {
            btnElement.style.transform = "translateX(0)";
        }, 100);

        // ── Salva erro ────────────────────────────────────────────────────
        salvarJogada({
            jogo_slug:      'forme_palavras',
            fase:           `nivel_${currentLevelIndex + 1}`,
            movimento_tipo: 'compor_palavra',
            movimento:      { palavra: currentWord, letra: letter, esperada: expectedLetter },
            correta:        false,
        });
        // ─────────────────────────────────────────────────────────────────
    }
}

function handleWin() {
    feedbackMessage.innerText = "Parabéns! Muito bem! 🎉";

    setTimeout(() => {
        currentLevelIndex++;
        if (currentLevelIndex >= levels.length) {
            feedbackMessage.innerText = "Você completou tudo! 🏆";
            emojiDisplay.innerText    = "🌟";
            lettersContainer.innerHTML = "";
            currentLevelIndex = 0;
        } else {
            loadLevel();
        }
    }, 2000);
}

loadLevel();
