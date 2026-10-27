<?php
$pageTitle = "Ligar imagem a palavra";
$pageBodyClass = "page-ligar-imagem";
$layoutRole = "aluno";
if (!isset($basePath)) $basePath = '../';
require __DIR__ . "/../partials/layout_start.php";
?>

<div class="sheet" role="main">
    <h1>Ligue a imagem à palavra</h1>
    <p class="lead">Clique primeiro no desenho e depois na palavra correspondente. Faça todas as ligações e depois pressione "Verificar".</p>

    <div class="connector-wrap" id="wrap">
      <div class="cols">
        <div class="left" id="imagesCol" aria-label="imagens">
          <div class="card" data-id="house" role="button" tabindex="0"><div class="emoji">🏠</div></div>
          <div class="card" data-id="sun" role="button" tabindex="0"><div class="emoji">☀️</div></div>
          <div class="card" data-id="balloon" role="button" tabindex="0"><div class="emoji">🎈</div></div>
          <div class="card" data-id="fish" role="button" tabindex="0"><div class="emoji">🐟</div></div>
        </div>

        <div style="flex:0 0 24px"></div>

        <div class="right" id="wordsCol" aria-label="palavras">
          <div class="word" data-id="house">Casa</div>
          <div class="word" data-id="sun">Sol</div>
          <div class="word" data-id="balloon">Balão</div>
          <div class="word" data-id="fish">Peixe</div>
        </div>
      </div>

      <svg class="connectors" id="svg"></svg>
    </div>

    <div class="controls">
      <button id="check">Verificar</button>
      <button class="ghost" id="reset">Limpar</button>
      <div class="status" id="status"></div>
    </div>
  </div>

  <script>const ID_ATIVIDADE = <?= (int)($_GET['atividade'] ?? 0); ?>;</script>
  <script src="../statics/js/game_api.js"></script>
  <script src="../statics/js/ligar_imagem.js"></script>

<?php require __DIR__ . "/../partials/layout_end.php"; ?>
