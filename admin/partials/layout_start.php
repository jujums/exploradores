<?php
if (!isset($pageTitle)) {
    $pageTitle = "Painel Administrativo";
}

$_adminUri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$isAdmin = isset($_SESSION['admin']) && (int)$_SESSION['admin'] === 1;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .sidebar-link.active {
            background-color: rgba(13, 110, 253, 0.12);
            color: #0d6efd;
            font-weight: 600;
        }
        .admin-theme-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        body.admin-layout.dark-mode {
            background-color: #111827 !important;
            color: #e5e7eb;
        }
        body.admin-layout.dark-mode aside,
        body.admin-layout.dark-mode main .card {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
            color: #e5e7eb !important;
        }
        body.admin-layout.dark-mode aside .nav-link:not(.text-danger),
        body.admin-layout.dark-mode main .text-muted,
        body.admin-layout.dark-mode main label {
            color: #d1d5db !important;
        }
        body.admin-layout.dark-mode main input,
        body.admin-layout.dark-mode main select,
        body.admin-layout.dark-mode main textarea {
            background-color: #111827;
            border-color: #4b5563;
            color: #f9fafb;
        }
        body.admin-layout.dark-mode .admin-theme-toggle {
            background-color: #374151;
            border-color: #6b7280;
            color: #f9fafb;
        }
    </style>
    <link rel="stylesheet" href="<?= $basePath ?>statics/css/all.min.css">
    <?php if (!empty($pageHeadExtra)) echo $pageHeadExtra; ?>
</head>
<body class="admin-layout bg-light <?= htmlspecialchars($pageBodyClass ?? '', ENT_QUOTES, 'UTF-8') ?>">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">Explorador Admin</span>
        <span class="navbar-text text-white-50 small">
            <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') : 'Professor'; ?>
        </span>
    </div>
</nav>

<div class="container-fluid">
    <div class="row min-vh-100">
        <aside class="col-12 col-md-3 col-lg-2 border-end bg-white p-3">
            <ul class="nav nav-pills flex-column gap-1">
                <li class="nav-item">
                    <a class="nav-link sidebar-link <?php echo $_adminUri === 'admin' ? 'active' : ''; ?>" href="/admin">Início</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link sidebar-link <?php echo (str_starts_with($_adminUri, 'admin/turmas')) ? 'active' : ''; ?>" href="/admin/turmas">Turmas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link sidebar-link <?php echo str_starts_with($_adminUri, 'admin/alunos') ? 'active' : ''; ?>" href="/admin/alunos">Alunos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link sidebar-link <?php echo str_starts_with($_adminUri, 'admin/atividades') ? 'active' : ''; ?>" href="/admin/atividades">Atividades por Turma</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link sidebar-link <?php echo str_starts_with($_adminUri, 'admin/desempenho') ? 'active' : ''; ?>" href="/admin/desempenho">Desempenho por Turma</a>
                </li>
                <?php if (!$isAdmin): ?>
                <li class="nav-item">
                    <a class="nav-link sidebar-link <?php echo str_starts_with($_adminUri, 'admin/graficos') ? 'active' : ''; ?>" href="/admin/graficos">Gráficos</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <button type="button" id="adminThemeToggle" class="btn btn-outline-secondary admin-theme-toggle w-100 text-start"
                            aria-pressed="false" aria-label="Ativar modo escuro">
                        <span id="adminThemeIcon" aria-hidden="true">☾</span>
                        <span id="adminThemeLabel">Modo escuro</span>
                    </button>
                </li>
                <?php if ($isAdmin): ?>
                <li class="nav-item">
                    <a class="nav-link sidebar-link <?php echo $_adminUri === 'admin/professores' ? 'active' : ''; ?>" href="/admin/professores">Cadastrar Professor</a>
                </li>
                <?php endif; ?>
                <li class="nav-item mt-2 pt-2 border-top">
                    <a class="nav-link text-danger" href="/sair">Sair</a>
                </li>
            </ul>
        </aside>

        <main class="col-12 col-md-9 col-lg-10 p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0"><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
            </div>
