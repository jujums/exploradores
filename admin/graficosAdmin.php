<?php
session_start();
if (!isset($_SESSION['prof']) || (int) $_SESSION['prof'] !== 1) {
    header("Location: /login");
    exit();
}

if (isset($_SESSION['admin']) && (int) $_SESSION['admin'] === 1) {
    header("Location: /admin");
    exit();
}

$idProfessor = (int) $_SESSION['user_id'];
require_once __DIR__ . '/../conexao.php';
$db = (new Database())->getConnection();
$stmtTurmas = $db->prepare('SELECT id, nome FROM turmas WHERE id_professor = :id_professor ORDER BY nome');
$stmtTurmas->execute([':id_professor' => $idProfessor]);
$turmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Gráficos por Turma";
$pageBodyClass = "page-graficos-admin";
$pageHeadExtra = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>';
require __DIR__ . "/partials/layout_start.php";
?>

<style>
  .chart-canvas-wrap { position: relative; height: 360px; }
  body.page-graficos-admin.dark-mode { background-color: #111827 !important; color: #e5e7eb; }
  body.page-graficos-admin.dark-mode main .card {
    background-color: #1f2937 !important;
    border-color: #374151 !important;
    color: #e5e7eb !important;
  }
  body.page-graficos-admin.dark-mode .text-muted,
  body.page-graficos-admin.dark-mode .form-label { color: #d1d5db !important; }
  body.page-graficos-admin.dark-mode .form-select {
    background-color: #111827;
    border-color: #4b5563;
    color: #f9fafb;
  }
</style>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <div class="row g-3 align-items-end">
      <div class="col-12 col-md-5">
        <label for="selectTurma" class="form-label fw-semibold">Turma</label>
        <select id="selectTurma" class="form-select">
          <?php if (empty($turmas)): ?>
            <option value="">Nenhuma turma cadastrada</option>
          <?php else: ?>
            <?php foreach ($turmas as $turma): ?>
              <option value="<?= (int) $turma['id'] ?>"><?= htmlspecialchars($turma['nome'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>
      <div class="col-12 col-md-5">
        <label for="selectAtividade" class="form-label fw-semibold">Jogo</label>
        <select id="selectAtividade" class="form-select">
          <option value="">Todos os jogos</option>
        </select>
      </div>
      <div class="col-12 col-md-2">
        <button id="applyFilter" class="btn btn-primary w-100" type="button">Aplicar</button>
      </div>
    </div>
  </div>
</div>

<div id="errorMessage" class="alert alert-warning" style="display:none;" role="alert"></div>
<div id="loading" class="text-center py-4" style="display:none;" role="status">Carregando dados da turma…</div>

<div id="dashboard" style="display:none;">
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card shadow-sm text-center h-100"><div class="card-body py-3">
        <div class="text-muted small mb-1">Total de alunos</div><div class="fs-2 fw-bold text-primary" id="totalAlunos">0</div>
      </div></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card shadow-sm text-center h-100"><div class="card-body py-3">
        <div class="text-muted small mb-1">Jogos realizados</div><div class="fs-2 fw-bold text-info" id="totalAtividades">0</div>
      </div></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card shadow-sm text-center h-100"><div class="card-body py-3">
        <div class="text-muted small mb-1">Taxa de acertos</div><div class="fs-2 fw-bold text-success" id="taxaAcertos">0%</div>
      </div></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card shadow-sm text-center h-100"><div class="card-body py-3">
        <div class="text-muted small mb-1">Taxa de erros</div><div class="fs-2 fw-bold text-danger" id="taxaErros">0%</div>
      </div></div>
    </div>
  </div>

</div>

<div class="card shadow-sm">
  <div class="card-body">
    <h2 class="h6 text-muted text-uppercase mb-3">Taxa de acertos por jogo</h2>
    <div class="chart-canvas-wrap">
      <canvas id="chartDesempenhoAtividades" aria-label="Taxa de acertos por jogo" role="img"></canvas>
      <p id="emptyChartMessage" class="text-center text-muted d-none" role="status"></p>
    </div>
  </div>
</div>

<script src="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>statics/js/graficos.js"></script>

<?php require __DIR__ . "/partials/layout_end.php"; ?>
