<?php
$pageTitle = "Configuracoes";
$pageBodyClass = "page-configuracaoa";
$layoutRole = "aluno";
require __DIR__ . "/partials/layout_start.php";
?>

<!-- Barra de navegação -->
  <!-- Seção de Perfil -->
  <section class="perfil">
    <h3>Perfil</h3>
    <div class="perfil-box">
      <img src="statics/imgs/icon.alunos.png" alt="Avatar">
      <div class="dados">
        <p><strong>Email:</strong></p>
    
      </div>
    </div>
  </section>

  <!-- Configurações Gerais -->
  <section class="config-gerais">
    <h3>Configurações Gerais</h3>
    <div class="opcoes">
      <div class="item">
        <img src="statics/imgs/lingua.png" alt="Língua">
        <p>Língua<br>Português Brasil</p>
      </div>
      <div class="item">
        <button type="button" class="config-theme-toggle" id="theme-toggle" aria-label="Ativar modo escuro" aria-pressed="false" title="Ativar modo escuro">
          <span class="config-theme-icon" id="theme-toggle-icon" aria-hidden="true">☾</span>
          <span class="config-theme-label" id="theme-toggle-label">Modo escuro</span>
        </button>
      </div>
    </div>
    
    <!-- Logos abaixo das opções -->
    <div class="logos-container">
      <div class="logos">
        <img src="statics/imgs/escola.png" alt="Escola" class="logo">
        <img src="statics/imgs/tecnico.png" alt="Técnico" class="logo">
      </div>
    </div>
  </section>

<?php require __DIR__ . "/partials/layout_end.php"; ?>
