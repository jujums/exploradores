<?php
if (!isset($pageTitle)) {
    $pageTitle = "Exploradores das Palavras";
}

if (!isset($pageBodyClass)) {
    $pageBodyClass = "page-homea";
}

if (!isset($layoutRole)) {
    $layoutRole = "aluno";
}

if (!isset($pageHeadExtra)) {
  $pageHeadExtra = "";
}

if (!isset($brandLogoFile)) {
    $brandLogoFile = $layoutRole === "aluno" ? "arco.png" : "tecnico.png";
}

if (!isset($brandLogoAlt)) {
    $brandLogoAlt = $layoutRole === "aluno"
        ? "Logo Exploradores das Palavras"
        : "Logo Tecnico";
}

// $basePath é usado apenas para assets (CSS/JS/imagens) em subpastas.
// Páginas na raiz usam '' (padrão); páginas em games/ definem $basePath = '../'.
if (!isset($basePath)) {
    $basePath = '';
}

// Base absoluta da aplicação — usada nos links de navegação
defined('APP_BASE') || define('APP_BASE', '');

// Slug ativo: último segmento do URI real (antes de qualquer query string)
$_reqPath   = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$activePage = basename($_reqPath); // ex: 'inicio', 'atividades', 'graficos'

$navAluno = [
    'inicio'        => 'Início',
    'atividades'    => 'Atividades',
    'progresso'     => 'Progresso',
    'sobre'         => 'Sobre',
    'configuracoes' => 'Configurações',
    'sair'          => 'Sair',
];

$navItems = $layoutRole === "professor" ? $navProfessor : $navAluno;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, "UTF-8"); ?></title>
  <link rel="stylesheet" href="<?= $basePath ?>statics/css/app.css">
  <?php echo $pageHeadExtra; ?>
</head>
<body class="<?php echo htmlspecialchars($pageBodyClass, ENT_QUOTES, "UTF-8"); ?> app-layout-enabled role-<?php echo htmlspecialchars($layoutRole, ENT_QUOTES, "UTF-8"); ?>">
  <header class="app-shell-header">
    <div class="app-shell-brand">
      <img src="<?= $basePath ?>statics/imgs/<?= htmlspecialchars($brandLogoFile, ENT_QUOTES, "UTF-8"); ?>" alt="<?= htmlspecialchars($brandLogoAlt, ENT_QUOTES, "UTF-8"); ?>" class="app-shell-logo">
      <div>
        <h1>Exploradores das Palavras</h1>
        <p><?php echo $layoutRole === "professor" ? "Painel do Professor" : "Ambiente do Aluno"; ?></p>
      </div>
    </div>
    <nav class="app-shell-nav" aria-label="Navegacao principal">
      <ul>
        <?php foreach ($navItems as $href => $label): ?>
          <li>
            <a href="<?= APP_BASE . '/' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"
               class="<?= $activePage === basename($href) ? 'is-active' : '' ?>">
              <?= $label ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <img src="<?= $basePath ?>statics/imgs/escola.png" alt="Logo Escola" class="app-shell-logo app-shell-logo-secondary">
  </header>
  <main class="app-shell-main">
    <div class="app-shell-content">
