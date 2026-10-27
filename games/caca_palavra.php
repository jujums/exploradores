<?php
$pageTitle = "Caca Palavras";
$pageBodyClass = "page-caca-palavra";
$layoutRole = "aluno";
if (!isset($basePath)) $basePath = '../';
require __DIR__ . "/../partials/layout_start.php";
?>

<div class="container">
    <!-- Título -->
    <h1>Caça Palavras</h1>
    
    <!-- Instrução -->
    <p>Encontre as palavras escondidas no quadro abaixo. Use o mouse para clicar em cada letra da palavra que achar e veja ela se formar!</p>
    
    <!-- Lista de palavras -->
    <div class="lista-palavras">
      <span class="word-item" data-word="gato">gato</span>
      <span class="word-item" data-word="cachorro">cachorro</span>
      <span class="word-item" data-word="passaro">pássaro</span>
      <span class="word-item" data-word="casa">casa</span>
      <span class="word-item" data-word="mesa">mesa</span>
      <span class="word-item" data-word="sapato">sapato</span>
      <span class="word-item" data-word="bola">bola</span>
      <span class="word-item" data-word="sol">sol</span>
      <span class="word-item" data-word="livro">livro</span>
      <span class="word-item" data-word="jogo">jogo</span>
      <span class="word-item" data-word="lua">lua</span>
      <span class="word-item" data-word="chuva">chuva</span>
      <span class="word-item" data-word="estrelas">estrelas</span>
      <span class="word-item" data-word="abraco">abraço</span>
      <span class="word-item" data-word="macas">maçãs</span>
    </div>

    <!-- Container especial para a grade -->
    <div class="grade-container">
      <!-- Grade do caça-palavras -->
      <div class="grade">
        <span data-word="gato">g</span>
        <span data-word="gato">a</span>
        <span data-word="gato">t</span>
        <span data-word="gato">o</span>
        <span data-word="cachorro">c</span>
        <span data-word="cachorro">a</span>
        <span data-word="cachorro">c</span>
        <span data-word="cachorro">h</span>
        <span data-word="cachorro">o</span>
        <span data-word="cachorro">r</span>
        <span data-word="cachorro">r</span>
        <span data-word="cachorro">o</span>
        <span data-word="aleatorio">l</span>
        <span data-word="aleatorio">b</span>
        <span data-word="aleatorio">d</span>
        <span data-word="aleatorio">f</span>
        <span data-word="aleatorio">o</span>
        <span data-word="passaro">p</span>
        <span data-word="passaro">á</span>
        <span data-word="passaro">s</span>
        <span data-word="passaro">s</span>
        <span data-word="passaro">a</span>
        <span data-word="passaro">r</span>
        <span data-word="passaro">o</span>
        <span data-word="aleatorio">r</span>
        <span data-word="aleatorio">a</span>
        <span data-word="aleatorio">p</span>
        <span data-word="aleatorio">s</span>
        <span data-word="aleatorio">v</span>
        <span data-word="casa">c</span>
        <span data-word="casa">a</span>
        <span data-word="casa">s</span>
        <span data-word="casa">a</span>
        <span data-word="aleatorio">k</span>
        <span data-word="aleatorio">u</span>
        <span data-word="aleatorio">j</span>
        <span data-word="aleatorio">t</span>
        <span data-word="aleatorio">o</span>
        <span data-word="mesa">m</span>
        <span data-word="mesa">e</span>
        <span data-word="mesa">s</span>
        <span data-word="mesa">a</span>
        <span data-word="aleatorio">ç</span>
        <span data-word="aleatorio">a</span>
        <span data-word="aleatorio">q</span>
        <span data-word="aleatorio">y</span>
        <span data-word="aleatorio">h</span>
        <span data-word="sapato">s</span>
        <span data-word="sapato">a</span>
        <span data-word="sapato">p</span>
        <span data-word="sapato">a</span>
        <span data-word="sapato">t</span>
        <span data-word="sapato">o</span>
        <span data-word="aleatorio">m</span>
        <span data-word="aleatorio">a</span>
        <span data-word="aleatorio">c</span>
        <span data-word="aleatorio">l</span>
        <span data-word="aleatorio">r</span>
        <span data-word="bola">b</span>
        <span data-word="bola">o</span>
        <span data-word="bola">l</span>
        <span data-word="bola">a</span>
        <span data-word="aleatorio">ç</span>
        <span data-word="aleatorio">j</span>
        <span data-word="aleatorio">g</span>
        <span data-word="aleatorio">e</span>
        <span data-word="sol">s</span>
        <span data-word="sol">o</span>
        <span data-word="sol">l</span>
        <span data-word="aleatorio">l</span>
        <span data-word="aleatorio">r</span>
        <span data-word="aleatorio">o</span>
        <span data-word="aleatorio">v</span>
        <span data-word="aleatorio">q</span>
        <span data-word="livro">l</span>
        <span data-word="livro">i</span>
        <span data-word="livro">v</span>
        <span data-word="livro">r</span>
        <span data-word="livro">o</span>
        <span data-word="aleatorio">o</span>
        <span data-word="aleatorio">d</span>
        <span data-word="aleatorio">b</span>
        <span data-word="aleatorio">l</span>
        <span data-word="aleatorio">p</span>
        <span data-word="jogo">j</span>
        <span data-word="jogo">o</span>
        <span data-word="jogo">g</span>
        <span data-word="jogo">o</span>
        <span data-word="aleatorio">r</span>
        <span data-word="aleatorio">v</span>
        <span data-word="lua">l</span>
        <span data-word="lua">u</span>
        <span data-word="lua">a</span>
        <span data-word="chuva">c</span>
        <span data-word="chuva">h</span>
        <span data-word="chuva">u</span>
        <span data-word="chuva">v</span>
        <span data-word="chuva">a</span>
        <span data-word="aleatorio">b</span>
        <span data-word="aleatorio">e</span>
        <span data-word="estrelas">e</span>
        <span data-word="estrelas">s</span>
        <span data-word="estrelas">t</span>
        <span data-word="estrelas">r</span>
        <span data-word="estrelas">e</span>
        <span data-word="estrelas">l</span>
        <span data-word="estrelas">a</span>
        <span data-word="estrelas">s</span>
        <span data-word="aleatorio">l</span>
        <span data-word="aleatorio">i</span>
        <span data-word="aleatorio">v</span>
        <span data-word="aleatorio">a</span>
        <span data-word="abraco">a</span>
        <span data-word="abraco">b</span>
        <span data-word="abraco">r</span>
        <span data-word="abraco">a</span>
        <span data-word="abraco">ç</span>
        <span data-word="abraco">o</span>
        <span data-word="aleatorio">m</span>
        <span data-word="aleatorio">e</span>
        <span data-word="macas">m</span>
        <span data-word="macas">a</span>
        <span data-word="macas">ç</span>
        <span data-word="macas">ã</span>
        <span data-word="macas">s</span>
        <span data-word="aleatorio">b</span>
      </div>
    </div>
    
    <div class="contador">Palavras encontradas: <span id="contador">0</span>/15</div>
  </div>

  <div class="confetti" id="confetti"></div>

  <script>const ID_ATIVIDADE = <?= (int)($_GET['atividade'] ?? 0); ?>;</script>
  <script src="../statics/js/game_api.js"></script>
  <script src="../statics/js/caca_palavra.js"></script>

<?php require __DIR__ . "/../partials/layout_end.php"; ?>
