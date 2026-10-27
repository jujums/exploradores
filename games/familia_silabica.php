<?php
$pageTitle = "Familias Silabicas";
$pageBodyClass = "page-familia-silabica";
$layoutRole = "aluno";
if (!isset($basePath)) $basePath = '../';
require __DIR__ . "/../partials/layout_start.php";
?>

<div class="container">
    <div class="decoration decoration-1"></div>
    <div class="decoration decoration-2"></div>
    
    <h2>Famílias silábicas</h2>
    <p>Complete as famílias silábicas com as sílabas que faltam.</p>

    <!-- Área da atividade -->
    <div class="atividade">
      <!-- Cada linha de família silábica -->
      <div class="familia" data-correta="BA,BE,BI,BO,BU">
        <span>BA</span> – <span>BE</span> – <span>BI</span> – <span class="faltando"></span> – <span>BU</span>
      </div>

      <div class="familia" data-correta="CA,CE,CI,CO,CU">
        <span>CA</span> – <span>CE</span> – <span class="faltando"></span> – <span>CO</span> – <span>CU</span>
      </div>

      <div class="familia" data-correta="DA,DE,DI,DO,DU">
        <span>DA</span> – <span>DE</span> – <span>DI</span> – <span>DO</span> – <span class="faltando"></span>
      </div>

      <div class="familia" data-correta="FA,FE,FI,FO,FU">
        <span class="faltando"></span> – <span>FE</span> – <span>FI</span> – <span>FO</span> – <span>FU</span>
      </div>
    </div>

    <!-- Opções de sílabas para arrastar -->
    <h3>Arraste a sílaba correta:</h3>
    <div class="sugestoes">
      <span draggable="true">BO</span>
      <span draggable="true">CI</span>
      <span draggable="true">DU</span>
      <span draggable="true">BA</span>
      <span draggable="true">FA</span>
    </div>

    <!-- Mensagem de feedback -->
    <p id="mensagem"></p>
    
    <!-- Botão de reiniciar -->
    <button class="reiniciar" onclick="reiniciarAtividade()">Reiniciar Atividade</button>
  </div>

  <script>const ID_ATIVIDADE = <?= (int)($_GET['atividade'] ?? 0); ?>;</script>
  <script src="../statics/js/game_api.js"></script>
  <script src="../statics/js/familia_silabica.js"></script>

<?php require __DIR__ . "/../partials/layout_end.php"; ?>
