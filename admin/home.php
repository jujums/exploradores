<?php

session_start();
if (!isset($_SESSION['prof']) || $_SESSION['prof'] != 1) {
    header("Location: /login");
    exit();
}

$pageTitle = "Painel Administrativo";
require __DIR__ . "/partials/layout_start.php";
?>

<div class="row g-3">
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5">Turmas</h2>
                <p class="text-muted mb-3">Gerencie as turmas do professor logado.</p>
                <a href="/admin/turmas" class="btn btn-primary">Abrir Turmas</a>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5">Alunos</h2>
                <p class="text-muted mb-3">Cadastre alunos e associe-os a uma turma.</p>
                <a href="/admin/alunos" class="btn btn-primary">Cadastrar Alunos</a>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5">Atividades por Turma</h2>
                <p class="text-muted mb-3">Vincule atividades com prazo às turmas.</p>
                <a href="/admin/atividades" class="btn btn-primary">Vincular Atividades</a>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['admin']) && (int)$_SESSION['admin'] === 1): ?>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card shadow-sm h-100 border-warning">
            <div class="card-body">
                <h2 class="h5">Cadastro de Professores</h2>
                <p class="text-muted mb-3">Cadastre e gerencie os professores do sistema.</p>
                <a href="/admin/professores" class="btn btn-warning">Abrir cadastro de professores</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . "/partials/layout_end.php"; ?>