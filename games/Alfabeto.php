<?php
$pageTitle = "Complete o Alfabeto";
$pageBodyClass = "page-alfabeto";
$layoutRole = "aluno";
if (!isset($basePath)) $basePath = '../';
require __DIR__ . "/../partials/layout_start.php";
?>

<div class="container">
    <h1>Complete o Alfabeto</h1>
    <p> Complete as letras que faltam no alfabato utilizando o teclado e o mouse!</p>
    <div class="alfabeto" id="alfabeto"></div>
    <button id="verificar">Verificar</button>
    <p id="resultado"></p>
  </div>
  <script>const ID_ATIVIDADE = <?= (int)($_GET['atividade'] ?? 0); ?>;</script>
  <script src="../statics/js/game_api.js"></script>
  <script src="../statics/js/Alfabeto.js"></script>

<?php require __DIR__ . "/../partials/layout_end.php"; ?>
