// Embaralhar as palavras
function shuffleWords() {
  const wordsCol = document.getElementById('wordsCol');
  const words = Array.from(wordsCol.children);
  
  // Embaralhar a array
  for (let i = words.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    wordsCol.appendChild(words[j]);
  }
}

// Chamar a função quando a página carregar
document.addEventListener('DOMContentLoaded', shuffleWords);

const images = Array.from(document.querySelectorAll('#imagesCol .card'));
const words = Array.from(document.querySelectorAll('#wordsCol .word'));
const svg = document.getElementById('svg');
const status = document.getElementById('status');
let first = null;
const matches = new Map();

function centerOf(el) {
  const wrap = document.getElementById('wrap');
  const rWrap = wrap.getBoundingClientRect();
  const r = el.getBoundingClientRect();
  return {
    x: (r.left + r.right) / 2 - rWrap.left,
    y: (r.top + r.bottom) / 2 - rWrap.top
  };
}

function drawLines() {
  svg.innerHTML = '';
  for (const [img, w] of matches.entries()) {
    const elImg = document.querySelector(`[data-id='${img}']`);
    const elW = document.querySelector(`#wordsCol [data-id='${w}']`);
    if (!elImg || !elW) continue;
    const p1 = centerOf(elImg);
    const p2 = centerOf(elW);
    const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    const dx = Math.abs(p2.x - p1.x) * 0.4;
    const d = `M ${p1.x} ${p1.y} C ${p1.x + dx} ${p1.y} ${p2.x - dx} ${p2.y} ${p2.x} ${p2.y}`;
    path.setAttribute('d', d);
    path.setAttribute('stroke', '#ff6b8b');
    path.setAttribute('stroke-width', '6');
    path.setAttribute('fill', 'none');
    path.setAttribute('stroke-linecap', 'round');
    svg.appendChild(path);
  }
}

function clearSelection() {
  first = null;
  images.forEach(i => i.classList.remove('dim', 'selected'));
  words.forEach(w => w.classList.remove('dim', 'selected'));
}

function markMatched() {
  images.forEach(i => {
    const id = i.dataset.id;
    if (matches.has(id)) {
      i.classList.add('matched');
      i.classList.remove('dim');
    } else {
      i.classList.remove('matched');
    }
  });
  words.forEach(w => {
    const id = w.dataset.id;
    const taken = Array.from(matches.values()).includes(id);
    if (taken) {
      w.classList.add('matched');
    } else {
      w.classList.remove('matched');
    }
  });
}

images.forEach(img => {
  img.addEventListener('click', () => {
    if (first && first.type === 'img' && first.el === img) {
      clearSelection();
      return;
    }
    if (first && first.type === 'word') {
      const imgId = img.dataset.id;
      const wordId = first.el.dataset.id;
      matches.set(imgId, wordId);
      drawLines();
      markMatched();
      clearSelection();
      status.textContent = '';
      return;
    }
    first = { type: 'img', el: img };
    images.forEach(i => i.classList.add('dim'));
    img.classList.remove('dim');
    img.classList.add('selected');
    words.forEach(w => w.classList.remove('dim'));
  });
});

words.forEach(w => {
  w.addEventListener('click', () => {
    if (first && first.type === 'word' && first.el === w) {
      clearSelection();
      return;
    }
    if (first && first.type === 'img') {
      const imgId = first.el.dataset.id;
      const wordId = w.dataset.id;
      matches.set(imgId, wordId);
      drawLines();
      markMatched();
      clearSelection();
      status.textContent = '';
      return;
    }
    first = { type: 'word', el: w };
    words.forEach(x => x.classList.add('dim'));
    w.classList.remove('dim');
    w.classList.add('selected');
    images.forEach(i => i.classList.remove('dim'));
  });
});

document.getElementById('check').addEventListener('click', () => {
  const correct = {
    house:   'house',
    sun:     'sun',
    balloon: 'balloon',
    fish:    'fish'
  };
  let right = 0;
  let total = Object.keys(correct).length;
  for (const key of Object.keys(correct)) {
    const acertou = matches.get(key) === correct[key];
    if (acertou) right++;

    // Salva cada par verificado
    salvarJogada({
      jogo_slug:      'ligar_imagem',
      movimento_tipo: 'ligar_item',
      movimento:      { imagem: key, palavra: matches.get(key) || null, esperada: correct[key] },
      correta:        acertou,
    });
  }
  status.textContent = `Acertos: ${right} / ${total}`;
  status.style.color = right === total ? '#1c1c1cff' : '#1e1e1eff';

  if (right === total) {
    images.forEach(i => i.classList.add('matched'));
    words.forEach(w => w.classList.add('matched'));
    status.style.background = 'linear-gradient(135deg, #20c997 0%, #e3fff7ff 100%)';
  } else {
    status.style.background = 'linear-gradient(135deg, #ff6b8b 0%, #ffceb3ff 100%)';
  }
});

document.getElementById('reset').addEventListener('click', () => {
  matches.clear();
  svg.innerHTML = '';
  clearSelection();
  markMatched();
  status.textContent = '';
  status.style.background = 'linear-gradient(135deg, #ff6b8b 0%, #ff8e53 100%)';
  
  // Reembaralhar as palavras ao limpar
  shuffleWords();
});

window.addEventListener('resize', () => {
  drawLines();
});

// Inicializa
markMatched();