// Seleciona todos os espaços faltando e opções
const faltando = document.querySelectorAll(".faltando");
const opcoes = document.querySelectorAll(".sugestoes span");
const mensagem = document.getElementById("mensagem");

// Adiciona efeito de destaque ao passar o mouse sobre os espaços vazios
faltando.forEach(espaco => {
  espaco.addEventListener("dragover", e => {
    e.preventDefault();
    espaco.classList.add("highlight");
  });
  
  espaco.addEventListener("dragleave", () => {
    espaco.classList.remove("highlight");
  });
});

// Permite arrastar
opcoes.forEach(opcao => {
  opcao.addEventListener("dragstart", e => {
    e.dataTransfer.setData("text", opcao.innerText);
    opcao.style.opacity = "0.5";
  });
  
  opcao.addEventListener("dragend", () => {
    opcao.style.opacity = "1";
  });
});

// Permite soltar no espaço correto
faltando.forEach(espaco => {
  espaco.addEventListener("dragover", e => e.preventDefault());

  espaco.addEventListener("drop", e => {
    e.preventDefault();
    const silaba = e.dataTransfer.getData("text");
    espaco.innerText = silaba;
    espaco.classList.remove("highlight");
    espaco.style.borderStyle = "solid";

    // Verifica se é a sílaba correta
    const familia = espaco.closest(".familia").dataset.correta.split(",");
    const acertou = familia.includes(silaba);

    // Salva o drop
    salvarJogada({
      jogo_slug:      'familia_silabica',
      movimento_tipo: 'drop_silaba',
      movimento:      { familia: espaco.closest(".familia").dataset.correta, silaba },
      correta:        acertou,
    });

    if (acertou) {
      espaco.style.background = "#c8e6c9"; // verde claro
      espaco.style.borderColor = "#4caf50";
      mensagem.innerText = "Muito bem! Você acertou!";
      mensagem.style.color = "#4caf50";
      mensagem.style.background = "#e8f5e9";
    } else {
      espaco.style.background = "#ffcdd2"; // vermelho claro
      espaco.style.borderColor = "#f44336";
      mensagem.innerText = "Ops! Tente outra sílaba.";
      mensagem.style.color = "#f44336";
      mensagem.style.background = "#ffebee";
    }
    
    // Verifica se todas as lacunas foram preenchidas
    verificarCompletude();
  });
});

// Função para verificar se todas as lacunas foram preenchidas
function verificarCompletude() {
  const todosPreenchidos = Array.from(faltando).every(espaco => espaco.innerText !== "");
  
  if (todosPreenchidos) {
    const todasCorretas = Array.from(faltando).every(espaco => {
      const familia = espaco.closest(".familia").dataset.correta.split(",");
      return familia.includes(espaco.innerText);
    });
    
    if (todasCorretas) {
      setTimeout(() => {
        mensagem.innerText = "Parabéns! Você completou todas as famílias silábicas corretamente!";
        mensagem.style.color = "#4caf50";
        mensagem.style.background = "#e8f5e9";
      }, 500);
    }
  }
}

// Função para reiniciar a atividade
function reiniciarAtividade() {
  faltando.forEach(espaco => {
    espaco.innerText = "";
    espaco.style.background = "#eef2ff";
    espaco.style.borderColor = "#6A82FB";
    espaco.style.borderStyle = "dashed";
  });
  
  mensagem.innerText = "";
  mensagem.style.background = "transparent";
}