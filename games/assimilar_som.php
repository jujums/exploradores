<?php
$pageTitle = "Relacionar Som ao Animal";
$pageBodyClass = "page-assimilar-som";
$layoutRole = "aluno";
if (!isset($basePath)) $basePath = '../';
require __DIR__ . "/../partials/layout_start.php";
?>

<div class="container">
        <h1>Relacione o Som ao Animal</h1>
        <div class="instructions">
            <p>Ouça o som e clique na imagem do animal correspondente utilizando o mouse.</p>
        </div>
        
        <div class="audio-container">
            <button id="play-sound" class="play-button">▶️ Reproduzir Som</button>
            <audio id="animal-sound" preload="auto"></audio>
        </div>
        
        <div class="score">
            <p>Acertos: <span id="score-value">0</span></p>
        </div>
        
        <div class="animals-container">
            <!-- As imagens foram inseridas dinamicamente pelo JavaScript -->
        </div>
        
        <div class="feedback">
            <p id="feedback-message"></p>
        </div>
        
        <button id="next-question" class="next-button">Próxima Pergunta</button>
    </div>
    
  <script>const ID_ATIVIDADE = <?= (int)($_GET['atividade'] ?? 0); ?>;</script>
    <script src="../statics/js/game_api.js"></script>
  <script src="../statics/js/assimilar_som.js"></script>

<?php require __DIR__ . "/../partials/layout_end.php"; ?>
