<?php
$pageTitle     = "Descubra a Palavra";
$pageBodyClass = "page-forca";
$layoutRole    = "aluno";
if (!isset($basePath)) $basePath = '../';
$pageHeadExtra = '<link rel="stylesheet" href="' . $basePath . 'statics/css/forca.css">';
require __DIR__ . "/../partials/layout_start.php";
?>

<div class="container">
  <h1>Descubra a Palavra</h1>

  <div id="game-area">
    <canvas id="hangman-canvas" width="200" height="250"></canvas>
    <div id="hint-display">❓</div>
  </div>

  <div id="word-container"></div>
  <div id="keyboard"></div>
  <div id="message-area"></div>
  <button id="restart-btn" onclick="startLevel()">Próxima Palavra ➔</button>
</div>

<script>const ID_ATIVIDADE = <?= (int)($_GET['atividade'] ?? 0); ?>;</script>
<script src="../statics/js/game_api.js"></script>
<script src="../statics/js/forca.js"></script>

<?php require __DIR__ . "/../partials/layout_end.php"; ?>
