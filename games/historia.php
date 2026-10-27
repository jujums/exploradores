<?php
$pageTitle = "Historia";
$pageBodyClass = "page-historia";
$layoutRole = "aluno";
if (!isset($basePath)) $basePath = '../';
require __DIR__ . "/../partials/layout_start.php";
?>

<div class="container">
    <div class="left-column">
      <h1>História: O Leão Leitor</h1>
      
      <div class="story-box">
        <p>Leonardo, um leão, adora ler.<br>
        Na escola dos animais ele é um bom aluno.<br>
        Um dia Leonardo leu tanto que esqueceu de comer.<br>
        O coitado do Leão foi com fome para a escola.</p>
      </div>

      <div class="illustration">
        <img src="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>statics/imgs/leao2.png" alt="Leão lendo">
      </div>
    </div>

    <div class="right-column">
      <div class="questions-grid">
        <div class="question-card">
          <p>1 – O animal que aparece no texto é:</p>
          <div class="options">
            <label class="option">
              <input type="radio" name="q1" value="Borboleta"> Borboleta
            </label>
            <label class="option">
              <input type="radio" name="q1" value="Leão"> Leão
            </label>
            <label class="option">
              <input type="radio" name="q1" value="Porco"> Porco
            </label>
            <label class="option">
              <input type="radio" name="q1" value="Pássaro"> Pássaro
            </label>
          </div>
        </div>

        <div class="question-card">
          <p>2 – O nome do leão é:</p>
          <div class="options">
            <label class="option">
              <input type="radio" name="q2" value="Leonardo"> Leonardo
            </label>
            <label class="option">
              <input type="radio" name="q2" value="Lucas"> Lucas
            </label>
          </div>
        </div>

        <div class="question-card">
          <p>3 – De acordo com o texto, Leonardo é:</p>
          <div class="options">
            <label class="option">
              <input type="radio" name="q3" value="Um leão bravo"> Um leão bravo
            </label>
            <label class="option">
              <input type="radio" name="q3" value="Um bom amigo"> Um bom amigo
            </label>
            <label class="option">
              <input type="radio" name="q3" value="Um bom aluno"> Um bom aluno
            </label>
          </div>
        </div>
      </div>

      <div class="button-container">
        <button class="check-button" onclick="verificar()">Conferir Respostas</button>
        <div class="result" id="resultado"></div>
      </div>
    </div>
  </div>

  <script>const ID_ATIVIDADE = <?= (int)($_GET['atividade'] ?? 0); ?>;</script>
  <script src="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>statics/js/game_api.js"></script>
  <script src="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>statics/js/historia.js"></script>

<?php require __DIR__ . "/../partials/layout_end.php"; ?>
