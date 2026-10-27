// Animação das partículas no fundo
function criarParticulas() {
    const particlesContainer = document.createElement('div');
    particlesContainer.classList.add('particles');
    particlesContainer.style.position = 'fixed';
    particlesContainer.style.top = '0';
    particlesContainer.style.left = '0';
    particlesContainer.style.width = '100%';
    particlesContainer.style.height = '100%';
    particlesContainer.style.pointerEvents = 'none';
    particlesContainer.style.zIndex = '-1';
    
    document.body.appendChild(particlesContainer);

    const particleCount = 30;
    
    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        
        // Tamanho aleatório
        const size = Math.random() * 10 + 5;
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        particle.style.borderRadius = '50%';
        particle.style.backgroundColor = 'rgba(255, 255, 255, 0.5)';
        particle.style.position = 'absolute';
        
        // Posição inicial aleatória
        particle.style.left = `${Math.random() * 100}vw`;
        
        // Duração e atraso aleatórios para animação
        const duration = Math.random() * 20 + 10;
        const delay = Math.random() * 5;
        particle.style.animation = `float-particle ${duration}s linear ${delay}s infinite`;
        
        // Definir direção final aleatória
        const xEnd = Math.random() * 0.4 - 0.2;
        particle.style.setProperty('--x-end', xEnd);
        
        particlesContainer.appendChild(particle);
    }

    // Adicionar a animação CSS dinamicamente
    const style = document.createElement('style');
    style.textContent = `
        @keyframes float-particle {
            0% {
                transform: translateY(100vh) translateX(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) translateX(calc(100vw * var(--x-end)));
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}

// Inicializar partículas quando a página carregar
window.addEventListener('load', criarParticulas);

// Código original do alfabeto
const letras = "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("");
const container = document.getElementById("alfabeto");

// Criar inputs/letras fixas
letras.forEach((letra, i) => {
    if (i % 4 === 0) { 
        let span = document.createElement("span");
        span.textContent = letra;
        span.classList.add("fixo");
        container.appendChild(span);
    } else {
        let input = document.createElement("input");
        input.setAttribute("maxlength", "1");
        input.dataset.resposta = letra; // guarda a letra correta
        input.classList.add("letra");
        container.appendChild(input);
    }
});

// Verificação
document.getElementById("verificar").addEventListener("click", () => {
    let erros = 0;
    let acertos = 0;
    let tentativa = 0;

    document.querySelectorAll(".letra").forEach(input => {
        tentativa++;
        const acertou = input.value.toUpperCase() === input.dataset.resposta;

        // Salva cada entrada individualmente
        salvarJogada({
            jogo_slug:      'alfabeto',
            movimento_tipo: 'digitar_letra',
            movimento:      { posicao: tentativa, digitado: input.value.toUpperCase(), esperado: input.dataset.resposta },
            correta:        acertou,
        });

        if (acertou) {
            input.classList.add("correto");
            input.classList.remove("errado");
            acertos++;
        } else {
            input.classList.add("errado");
            input.classList.remove("correto");
            erros++;
        }
    });

    const resultado = document.getElementById("resultado");
    if (erros === 0) {
        resultado.textContent = "Todas as letras estão corretas, parabéns!!";
        resultado.style.color = "#28a745";
        
        // Efeito especial quando acerta tudo
        criarEfeitoCelebracao();
    } else {
        resultado.textContent = `Tem ${erros} letras erradas. Tente novamente!`;
        resultado.style.color = "red";
    }
});

// Efeito especial de celebração quando acerta tudo
function criarEfeitoCelebracao() {
    const celebrationContainer = document.createElement('div');
    celebrationContainer.style.position = 'fixed';
    celebrationContainer.style.top = '0';
    celebrationContainer.style.left = '0';
    celebrationContainer.style.width = '100%';
    celebrationContainer.style.height = '100%';
    celebrationContainer.style.pointerEvents = 'none';
    celebrationContainer.style.zIndex = '1000';
    
    document.body.appendChild(celebrationContainer);

    const confettiCount = 50;
    
    for (let i = 0; i < confettiCount; i++) {
        const confetti = document.createElement('div');
        confetti.style.width = '15px';
        confetti.style.height = '15px';
        confetti.style.position = 'absolute';
        confetti.style.borderRadius = '50%';
        
        // Cores aleatórias para o confetti
        const colors = ['#FFB6C1', '#FFD700', '#87CEEB', '#98FB98', '#DDA0DD'];
        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        
        // Posição inicial aleatória
        confetti.style.left = `${Math.random() * 100}vw`;
        confetti.style.top = '-20px';
        
        // Animação
        const duration = Math.random() * 2 + 1;
        const delay = Math.random() * 0.5;
        confetti.style.animation = `fall ${duration}s ease-in ${delay}s forwards`;
        
        celebrationContainer.appendChild(confetti);
    }

    // Adicionar animação do confetti
    const confettiStyle = document.createElement('style');
    confettiStyle.textContent = `
        @keyframes fall { 
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(confettiStyle);

    // Remover o confetti após a animação
    setTimeout(() => {
        celebrationContainer.remove();
        confettiStyle.remove();
    }, 3000);
}

// Focar automaticamente no primeiro input vazio
document.addEventListener('DOMContentLoaded', function() { // Quando a página carrega
    const primeiroInput = document.querySelector('.letra'); //Encontra o 1 input
    if (primeiroInput) { //se existir algum input
        setTimeout(() => { //da tempo das animações carregarem
            primeiroInput.focus(); //coloca cursor no input
        }, 500);
    }
});