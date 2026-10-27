<?php
session_start();
if (!isset($_SESSION['prof']) || $_SESSION['prof'] != 1) {
    header("Location: /login");
    exit();
}

require_once __DIR__ . '/../conexao.php';
$database = new Database();
$db = $database->getConnection();

$idProfessor = (int) $_SESSION['user_id'];

// Exclusão de vinculação
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'delete'
    && isset($_POST['id_turma'], $_POST['id_atividade'])) {

    $idTurma     = (int) $_POST['id_turma'];
    $idAtividade = (int) $_POST['id_atividade'];

    // Garante que a turma pertence ao professor antes de deletar
    $stmtCheck = $db->prepare("SELECT id FROM turmas WHERE id = :id AND id_professor = :prof");
    $stmtCheck->execute([':id' => $idTurma, ':prof' => $idProfessor]);
    if ($stmtCheck->fetch()) {
        $sqlDel = "DELETE FROM atividades_turma WHERE id_turma = :idTurma AND id_atividade = :idAtividade";
        $stmtDel = $db->prepare($sqlDel);
        $stmtDel->execute([':idTurma' => $idTurma, ':idAtividade' => $idAtividade]);
    }
    header("Location: /admin/atividades?deleted=1");
    exit();
}

// Busca vinculações do professor
$sqlVinc = "SELECT alt.id_turma, alt.id_atividade, alt.dt_inicio, alt.dt_fim,
                   t.nome AS nome_turma, a.nome AS nome_atividade
            FROM atividades_turma alt
            INNER JOIN turmas t ON t.id = alt.id_turma
            INNER JOIN atividades a ON a.id = alt.id_atividade
            WHERE t.id_professor = :idProf
            ORDER BY t.nome, a.nome";
$stmtVinc = $db->prepare($sqlVinc);
$stmtVinc->bindParam(':idProf', $idProfessor, PDO::PARAM_INT);
$stmtVinc->execute();
$vinculacoes = $stmtVinc->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Atividades por Turma";
require __DIR__ . "/partials/layout_start.php";
?>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Atividades vinculadas com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Vinculação atualizada com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Vinculação removida com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php $e = htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8');
              echo $e === 'turma' ? 'Turma inválida ou sem permissão.' : 'Não foi possível salvar. Tente novamente.'; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0">Atividades por Turma</h2>
    <a href="/admin/atividades/vincular" class="btn btn-primary">Vincular Atividade</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Turma</th>
                        <th>Atividade</th>
                        <th>Início</th>
                        <th>Prazo</th>
                        <th class="text-end pe-3" style="width: 200px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($vinculacoes)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Nenhuma atividade vinculada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($vinculacoes as $v): ?>
                            <tr>
                                <td class="ps-3"><?php echo htmlspecialchars($v['nome_turma'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($v['nome_atividade'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($v['dt_inicio'])); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($v['dt_fim'])); ?></td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-2">
                                        <a href="/admin/atividades/editar?turma=<?php echo (int) $v['id_turma']; ?>&amp;atividade=<?php echo (int) $v['id_atividade']; ?>"
                                           class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen-to-square"></i></a>

                                        <form action="" method="post"
                                              onsubmit="return confirm('Remover esta vinculação?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_turma" value="<?php echo (int) $v['id_turma']; ?>">
                                            <input type="hidden" name="id_atividade" value="<?php echo (int) $v['id_atividade']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . "/partials/layout_end.php"; ?>

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create'
        && isset($_POST['turma'], $_POST['atividade'], $_POST['dt_inicio'], $_POST['dt_fim'])) {

        $turmaId  = (int) $_POST['turma'];
        $dtInicio = $_POST['dt_inicio'];
        $dtFim    = $_POST['dt_fim'];

        // Garante que a turma pertence ao professor
        $stmtCheck = $db->prepare("SELECT id FROM turmas WHERE id = :id AND id_professor = :prof");
        $stmtCheck->execute([':id' => $turmaId, ':prof' => $idProfessor]);
        if (!$stmtCheck->fetch()) {
            header("Location: /admin/atividades?error=turma");
            exit();
        }

        $atividadeIds = is_array($_POST['atividade']) ? $_POST['atividade'] : [$_POST['atividade']];

        try {
            $db->beginTransaction();
            $sqlIns = "INSERT INTO atividades_turma (id_turma, id_atividade, dt_inicio, dt_fim)
                       VALUES (:idTurma, :idAtividade, :dtInicio, :dtFim)";
            $stmtIns = $db->prepare($sqlIns);
            foreach ($atividadeIds as $atividadeId) {
                $stmtIns->execute([
                    ':idTurma'     => $turmaId,
                    ':idAtividade' => (int) $atividadeId,
                    ':dtInicio'    => $dtInicio,
                    ':dtFim'       => $dtFim,
                ]);
            }
            $db->commit();
            header("Location: /admin/atividades?success=1");
            exit();
        } catch (PDOException $e) {
            if ($db->inTransaction()) $db->rollBack();
            header("Location: /admin/atividades?error=save");
            exit();
        }
    }

    if ($action === 'delete' && isset($_POST['id_turma'], $_POST['id_atividade'])) {
        $idTurma    = (int) $_POST['id_turma'];
        $idAtividade = (int) $_POST['id_atividade'];

        // Garante que a turma pertence ao professor antes de deletar
        $stmtCheck = $db->prepare("SELECT id FROM turmas WHERE id = :id AND id_professor = :prof");
        $stmtCheck->execute([':id' => $idTurma, ':prof' => $idProfessor]);
        if ($stmtCheck->fetch()) {
            $sqlDel = "DELETE FROM atividades_turma WHERE id_turma = :idTurma AND id_atividade = :idAtividade";
            $stmtDel = $db->prepare($sqlDel);
            $stmtDel->execute([':idTurma' => $idTurma, ':idAtividade' => $idAtividade]);
        }
        header("Location: /admin/atividades?deleted=1");
        exit();
    }
}

// Busca vinculações do professor
$sqlVinc = "SELECT at.id_turma, at.id_atividade, at.dt_inicio, at.dt_fim,
                   t.nome AS nome_turma, a.nome AS nome_atividade
            FROM atividades_turma at
            INNER JOIN turmas t ON t.id = at.id_turma
            INNER JOIN atividades a ON a.id = at.id_atividade
            WHERE t.id_professor = :idProf
            ORDER BY t.nome, a.nome";
$stmtVinc = $db->prepare($sqlVinc);
$stmtVinc->bindParam(':idProf', $idProfessor, PDO::PARAM_INT);
$stmtVinc->execute();
$vinculacoes = $stmtVinc->fetchAll(PDO::FETCH_ASSOC);

// Turmas do professor (para o form)
$stmtTurmas = $db->prepare("SELECT id, nome FROM turmas WHERE id_professor = :idProf ORDER BY nome");
$stmtTurmas->bindParam(':idProf', $idProfessor, PDO::PARAM_INT);
$stmtTurmas->execute();
$turmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

// Todas as atividades disponíveis (para o form)
$stmtAtiv = $db->prepare("SELECT id, nome FROM atividades ORDER BY nome");
$stmtAtiv->execute();
$atividades = $stmtAtiv->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Atividades por Turma";
require __DIR__ . "/partials/layout_start.php";
?>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Atividades vinculadas com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Vinculação removida com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php $e = htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8');
              echo $e === 'turma' ? 'Turma inválida ou sem permissão.' : 'Não foi possível salvar. Tente novamente.'; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>

<!-- Cabeçalho + botão -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0">Atividades por Turma</h2>
    <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#novaVinculacaoForm" aria-expanded="false" aria-controls="novaVinculacaoForm">
        Vincular Atividade
    </button>
</div>

<!-- Formulário de vinculação (colapsável) -->
<div class="collapse mb-4" id="novaVinculacaoForm">
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="" method="post" class="row g-3">
                <input type="hidden" name="action" value="create">

                <div class="col-12 col-md-6">
                    <label for="turma" class="form-label">Turma</label>
                    <select class="form-select" name="turma" id="turma" required>
                        <option value="">Selecione a turma</option>
                        <?php foreach ($turmas as $turma): ?>
                            <option value="<?php echo (int) $turma['id']; ?>">
                                <?php echo htmlspecialchars($turma['nome'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label for="atividade" class="form-label">Atividades <span class="text-muted small">(Ctrl/Cmd para múltiplas)</span></label>
                    <select class="form-select" name="atividade[]" id="atividade" required multiple size="5">
                        <?php foreach ($atividades as $atv): ?>
                            <option value="<?php echo (int) $atv['id']; ?>">
                                <?php echo htmlspecialchars($atv['nome'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label for="dt_inicio" class="form-label">Disponível a partir de</label>
                    <input type="datetime-local" class="form-control" name="dt_inicio" id="dt_inicio" required>
                </div>

                <div class="col-12 col-md-6">
                    <label for="dt_fim" class="form-label">Data limite de entrega</label>
                    <input type="datetime-local" class="form-control" name="dt_fim" id="dt_fim" required>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Vincular</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tabela de vinculações -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Turma</th>
                        <th>Atividade</th>
                        <th>Início</th>
                        <th>Prazo</th>
                        <th class="text-end pe-3" style="width: 100px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($vinculacoes)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Nenhuma atividade vinculada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($vinculacoes as $v): ?>
                            <tr>
                                <td class="ps-3"><?php echo htmlspecialchars($v['nome_turma'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($v['nome_atividade'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($v['dt_inicio'])); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($v['dt_fim'])); ?></td>
                                <td class="text-end pe-3">
                                    <form action="" method="post" onsubmit="return confirm('Remover esta vinculação?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id_turma" value="<?php echo (int) $v['id_turma']; ?>">
                                        <input type="hidden" name="id_atividade" value="<?php echo (int) $v['id_atividade']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Remover</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . "/partials/layout_end.php"; ?>