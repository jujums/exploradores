const levels = [
    { word: "MACA",  hint: "🍎", displayWord: "MAÇÃ"  },
    { word: "GATO",  hint: "🐱", displayWord: "GATO"  },
    { word: "CARRO", hint: "🚗", displayWord: "CARRO" },
    { word: "BOLO",  hint: "🎂", displayWord: "BOLO"  },
    { word: "SOL",   hint: "☀️", displayWord: "SOL"   }
];

let currentLevelIndex = 0;
let currentWord    = "";
let displayWord    = "";
let guessedLetters = [];
let wrongGuesses   = 0;
let tentativaNum   = 0;
const maxErrors    = 6;

const canvas       = document.getElementById("hangman-canvas");
const ctx          = canvas.getContext("2d");
const hintDisplay  = document.getElementById("hint-display");
const wordContainer = document.getElementById("word-container");
const keyboard     = document.getElementById("keyboard");
const messageArea  = document.getElementById("message-area");
const restartBtn   = document.getElementById("restart-btn");

function drawGallows() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.lineWidth  = 4;
    ctx.strokeStyle = "#2C3E50";
    ctx.beginPath();
    ctx.moveTo(20, 230); ctx.lineTo(180, 230);
    ctx.moveTo(50, 230); ctx.lineTo(50, 20);
    ctx.lineTo(120, 20); ctx.lineTo(120, 50);
    ctx.stroke();
}

function drawHangmanPart(part) {
    ctx.lineWidth   = 3;
    ctx.strokeStyle = "#2C3E50";
    ctx.beginPath();
    if      (part === 1) { ctx.arc(120, 70, 20, 0, Math.PI * 2); }
    else if (part === 2) { ctx.moveTo(120, 90);  ctx.lineTo(120, 160); }
    else if (part === 3) { ctx.moveTo(120, 110); ctx.lineTo(90, 140);  }
    else if (part === 4) { ctx.moveTo(120, 110); ctx.lineTo(150, 140); }
    else if (part === 5) { ctx.moveTo(120, 160); ctx.lineTo(90, 200);  }
    else if (part === 6) { ctx.moveTo(120, 160); ctx.lineTo(150, 200); }
    ctx.stroke();
}

function createKeyboard() {
    keyboard.innerHTML = "";
    "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("").forEach(letter => {
        const btn = document.createElement("button");
        btn.classList.add("key-btn");
        btn.innerText = letter;
        btn.onclick   = () => handleGuess(letter, btn);
        keyboard.appendChild(btn);
    });
}

function updateWordDisplay() {
    wordContainer.innerHTML = "";
    let wordCompleted = true;

    for (let i = 0; i < currentWord.length; i++) {
        const letter      = currentWord[i];
        const displayLetter = displayWord[i];
        const slot = document.createElement("div");
        slot.classList.add("letter-slot");

        if (guessedLetters.includes(letter)) {
            slot.innerText = displayLetter;
        } else {
            wordCompleted = false;
        }
        wordContainer.appendChild(slot);
    }

    if (wordCompleted) handleWin();
}

function handleGuess(letter, btn) {
    btn.disabled = true;
    guessedLetters.push(letter);
    tentativaNum++;

    const acertou = currentWord.includes(letter);

    // ── Salva a jogada no banco ──────────────────────────────────────────
    salvarJogada({
        jogo_slug:       'forca',
        fase:            currentWord,
        numero_tentativa: tentativaNum,
        movimento_tipo:  'chutar_letra',
        movimento:       { letra: letter, palavra: currentWord },
        correta:         acertou,
    });
    // ────────────────────────────────────────────────────────────────────

    if (acertou) {
        btn.classList.add("correct");
        updateWordDisplay();
    } else {
        btn.classList.add("wrong");
        wrongGuesses++;
        drawHangmanPart(wrongGuesses);
        if (wrongGuesses >= maxErrors) handleLose();
    }
}

function handleWin() {
    messageArea.style.color = "#2ECC71";
    messageArea.innerText   = "Muito bem! Você acertou! 🎉";
    endGame();
}

function handleLose() {
    messageArea.style.color = "#E74C3C";
    messageArea.innerText   = `Puxa, a palavra era: ${displayWord}`;
    endGame();
    const slots = wordContainer.children;
    for (let i = 0; i < currentWord.length; i++) {
        if (!guessedLetters.includes(currentWord[i])) {
            slots[i].innerText    = displayWord[i];
            slots[i].style.color  = "#E74C3C";
        }
    }
}

function endGame() {
    document.querySelectorAll(".key-btn").forEach(k => k.disabled = true);
    restartBtn.style.display = "block";
}

function startLevel() {
    const level  = levels[currentLevelIndex];
    currentWord  = level.word;
    displayWord  = level.displayWord;
    tentativaNum = 0;

    hintDisplay.innerText        = level.hint;
    guessedLetters               = [];
    wrongGuesses                 = 0;
    messageArea.innerText        = "";
    restartBtn.style.display     = "none";
    restartBtn.innerText         = "Próxima Palavra ➔";

    drawGallows();
    createKeyboard();
    updateWordDisplay();

    currentLevelIndex++;
    if (currentLevelIndex >= levels.length) {
        currentLevelIndex    = 0;
        restartBtn.innerText = "Jogar Tudo de Novo 🔄";
    }
}

startLevel();
