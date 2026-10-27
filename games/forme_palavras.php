<?php
$pageTitle     = "Forme a Palavra";
$pageBodyClass = "page-forme-palavras";
$layoutRole    = "aluno";
if (!isset($basePath)) $basePath = '../';
$pageHeadExtra = '<link rel="stylesheet" href="' . $basePath . 'statics/css/forme_palavras.css">';
require __DIR__ . "/../partials/layout_start.php";
?>

<div class="container">
  <h1>Qual é a palavra?</h1>
  <div id="emoji-display">❓</div>
  <div id="word-container"></div>
  <div id="letters-container"></div>
  <div id="feedback-message"></div>
</div>

<script>const ID_ATIVIDADE = <?= (int)($_GET['atividade'] ?? 0); ?>;</script>
<script src="../statics/js/game_api.js"></script>
<script src="../statics/js/forme_palavras.js"></script>

<?php require __DIR__ . "/../partials/layout_end.php"; ?>
