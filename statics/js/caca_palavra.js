document.addEventListener('DOMContentLoaded', () => {
  const listItems = document.querySelectorAll('.lista-palavras .word-item');
  const allCells = Array.from(document.querySelectorAll('.grade span'));
  const contadorElement = document.getElementById('contador');
  const confettiElement = document.getElementById('confetti');
  
  let palavrasEncontradas = 0;

  // Mapeia cada palavra -> suas células na grade (na ordem) e indexa posição
  const wordsMap = {};
  listItems.forEach(li => {
    const w = li.dataset.word; // "gato", "cachorro", "passaro"
    const cells = Array.from(document.querySelectorAll(`.grade [data-word="${w}"]`));
    cells.forEach((c, i) => c.dataset.pos = String(i)); // posição dentro da palavra
    wordsMap[w] = { li, cells, length: cells.length };
  });

  // Estado de seleção atual
  let active = null; // { word, nextPos, cells[] }

  allCells.forEach(cell => {
    cell.addEventListener('click', () => onCellClick(cell));
  });

  function onCellClick(cell) {
    if (cell.classList.contains('found')) return; // já resolvida

    const word = cell.dataset.word;
    const pos = Number(cell.dataset.pos);

    // Se não há seleção ativa, só começa se clicar na 1ª letra (pos 0)
    if (!active) {
      if (pos !== 0) return flashWrong(cell);
      active = { word, nextPos: 0, cells: [] };
    }

    // Caso esteja selecionando outra palavra, reinicia
    if (active.word !== word) {
      resetActive();
      if (pos !== 0) return flashWrong(cell);
      active = { word, nextPos: 0, cells: [] };
    }

    // Precisa clicar exatamente a próxima posição esperada
    if (pos !== active.nextPos) {
      flashWrong(cell);
      resetActive();
      // se clicou no início de outra palavra, começa por ela
      if (pos === 0) {
        active = { word, nextPos: 0, cells: [] };
        acceptCell(cell);
      }
      return;
    }

    // Aceita a célula
    acceptCell(cell);

    // Completou?
    if (active.nextPos >= wordsMap[word].length) {
      markFound(word);
      resetActive();
      atualizarContador();
      
      // Verifica se todas as palavras foram encontradas
      if (palavrasEncontradas === listItems.length) {
        celebrarVitoria();
      }
    }
  }

  function acceptCell(cell) {
    cell.classList.add('selected');
    active.cells.push(cell);
    active.nextPos++;

    salvarJogada({
      jogo_slug:      'caca_palavra',
      movimento_tipo: 'clicar_letra',
      movimento:      {
        letra:   cell.textContent.trim(),
        posicao: Number(cell.dataset.pos),
        palavra: active.word,
      },
      correta: true,
    });
  }

  function resetActive() {
    if (!active) return;
    active.cells.forEach(c => c.classList.remove('selected'));
    active = null;
  }

  function flashWrong(cell) {
    cell.classList.add('shake');
    setTimeout(() => cell.classList.remove('shake'), 220);

    const mov = { letra: cell.textContent.trim() };
    const pos = Number(cell.dataset.pos);
    if (!isNaN(pos)) mov.posicao = pos;
    const w = cell.dataset.word;
    if (w && w !== 'aleatorio') mov.palavra_celula = w;

    salvarJogada({
      jogo_slug:      'caca_palavra',
      movimento_tipo: 'clicar_letra',
      movimento:      mov,
      correta:        false,
    });
  }

  function markFound(word) {
    // Marca na lista
    wordsMap[word].li.classList.add('found');
    // Marca todas as letras dessa palavra na grade
    wordsMap[word].cells.forEach(c => {
      c.classList.remove('selected');
      c.classList.add('found');
    });
    palavrasEncontradas++;
  }

  function atualizarContador() {
    contadorElement.textContent = palavrasEncontradas;
  }

  function celebrarVitoria() {
    // Criar efeito de confete
    confettiElement.style.display = 'block';
    
    // Adicionar mensagem de parabéns
    const mensagem = document.createElement('div');
    mensagem.innerHTML = '<h2 style="color: #ff6b6b; margin-top: 20px;">Parabéns! Você encontrou todas as palavras!</h2>';
    document.querySelector('.container').appendChild(mensagem);
    
    // Criar confetes
    criarConfetes();
  }

  function criarConfetes() {
    const colors = ['#ff6b6b', '#4ecdc4', '#ffe66d', '#ff9a8b', '#6b5b95', '#88d8b0'];
    
    for (let i = 0; i < 150; i++) {
      const confetti = document.createElement('div');
      confetti.style.position = 'fixed';
      confetti.style.width = '10px';
      confetti.style.height = '10px';
      confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
      confetti.style.borderRadius = '50%';
      confetti.style.left = Math.random() * 100 + 'vw';
      confetti.style.top = '-10px';
      confetti.style.zIndex = '1000';
      confetti.style.pointerEvents = 'none';
      
      const animation = confetti.animate([
        { transform: 'translateY(0) rotate(0deg)', opacity: 1 },
        { transform: `translateY(100vh) rotate(${Math.random() * 360}deg)`, opacity: 0 }
      ], {
        duration: Math.random() * 3000 + 2000,
        easing: 'cubic-bezier(0.1, 0.8, 0.2, 1)'
      });
      
      confettiElement.appendChild(confetti);
      
      animation.onfinish = () => {
        confetti.remove();
      };
    }
  }
});