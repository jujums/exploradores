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

// Determina o modo: editar se ambos os parâmetros GET estiverem presentes
$editMode    = isset($_GET['turma'], $_GET['atividade'])
               && (int) $_GET['turma'] > 0
               && (int) $_GET['atividade'] > 0;
$idTurmaEdit = $editMode ? (int) $_GET['turma']     : null;
$idAtivEdit  = $editMode ? (int) $_GET['atividade'] : null;

$dtInicio       = '';
$dtFim          = '';
$nomeTurmaEdit  = '';
$nomeAtividEdit = '';
$errorMsg       = '';

// Carrega os dados atuais no modo edição
if ($editMode) {
    $sqlFetch = "SELECT alt.dt_inicio, alt.dt_fim, t.nome AS nome_turma, a.nome AS nome_atividade
                 FROM atividades_turma alt
                 INNER JOIN turmas t ON t.id = alt.id_turma
                 INNER JOIN atividades a ON a.id = alt.id_atividade
                 WHERE alt.id_turma = :idTurma
                   AND alt.id_atividade = :idAtiv
                   AND t.id_professor = :idProf";
    $stmtFetch = $db->prepare($sqlFetch);
    $stmtFetch->execute([
        ':idTurma' => $idTurmaEdit,
        ':idAtiv'  => $idAtivEdit,
        ':idProf'  => $idProfessor,
    ]);
    $row = $stmtFetch->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        header("Location: /admin/atividades");
        exit();
    }
    $dtInicio       = date('Y-m-d\TH:i', strtotime($row['dt_inicio']));
    $dtFim          = date('Y-m-d\TH:i', strtotime($row['dt_fim']));
    $nomeTurmaEdit  = $row['nome_turma'];
    $nomeAtividEdit = $row['nome_atividade'];
}

// Processamento do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $dtInicioPost = $_POST['dt_inicio'] ?? '';
    $dtFimPost    = $_POST['dt_fim']    ?? '';

    if ($dtInicioPost === '' || $dtFimPost === '') {
        $errorMsg = "Preencha as datas de início e fim.";
    } elseif ($_POST['action'] === 'create') {

        $turmaId      = (int) ($_POST['turma'] ?? 0);
        $atividadeIds = isset($_POST['atividade']) && is_array($_POST['atividade'])
                        ? $_POST['atividade']
                        : (isset($_POST['atividade']) ? [$_POST['atividade']] : []);

        if ($turmaId === 0 || empty($atividadeIds)) {
            $errorMsg = "Selecione a turma e pelo menos uma atividade.";
        } else {
            // Verifica ownership da turma
            $stmtCheck = $db->prepare("SELECT id FROM turmas WHERE id = :id AND id_professor = :prof");
            $stmtCheck->execute([':id' => $turmaId, ':prof' => $idProfessor]);
            if (!$stmtCheck->fetch()) {
                header("Location: /admin/atividades?error=turma");
                exit();
            }

            try {
                $db->beginTransaction();
                $sqlIns = "INSERT INTO atividades_turma (id_turma, id_atividade, dt_inicio, dt_fim)
                           VALUES (:idTurma, :idAtividade, :dtInicio, :dtFim)";
                $stmtIns = $db->prepare($sqlIns);
                foreach ($atividadeIds as $atividadeId) {
                    $stmtIns->execute([
                        ':idTurma'     => $turmaId,
                        ':idAtividade' => (int) $atividadeId,
                        ':dtInicio'    => $dtInicioPost,
                        ':dtFim'       => $dtFimPost,
                    ]);
                }
                $db->commit();
                header("Location: /admin/atividades?success=1");
                exit();
            } catch (PDOException $e) {
                if ($db->inTransaction()) $db->rollBack();
                $errorMsg = "Não foi possível salvar. Verifique se a atividade já está vinculada a esta turma.";
            }
        }

    } elseif ($_POST['action'] === 'edit') {

        $idTurmaPost = (int) ($_POST['id_turma']    ?? 0);
        $idAtivPost  = (int) ($_POST['id_atividade'] ?? 0);

        // Verifica ownership
        $stmtCheck = $db->prepare("SELECT id FROM turmas WHERE id = :id AND id_professor = :prof");
        $stmtCheck->execute([':id' => $idTurmaPost, ':prof' => $idProfessor]);
        if (!$stmtCheck->fetch()) {
            header("Location: /admin/atividades?error=turma");
            exit();
        }

        $sqlUpd = "UPDATE atividades_turma SET dt_inicio = :dtInicio, dt_fim = :dtFim
                   WHERE id_turma = :idTurma AND id_atividade = :idAtiv";
        $stmtUpd = $db->prepare($sqlUpd);
        $stmtUpd->execute([
            ':dtInicio' => $dtInicioPost,
            ':dtFim'    => $dtFimPost,
            ':idTurma'  => $idTurmaPost,
            ':idAtiv'   => $idAtivPost,
        ]);
        header("Location: /admin/atividades?updated=1");
        exit();
    }
}

// Dados para o formulário de criação
if (!$editMode) {
    $stmtTurmas = $db->prepare("SELECT id, nome FROM turmas WHERE id_professor = :idProf ORDER BY nome");
    $stmtTurmas->bindParam(':idProf', $idProfessor, PDO::PARAM_INT);
    $stmtTurmas->execute();
    $turmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

    $stmtAtiv = $db->prepare("SELECT id, nome FROM atividades ORDER BY nome");
    $stmtAtiv->execute();
    $atividades = $stmtAtiv->fetchAll(PDO::FETCH_ASSOC);
}

$pageTitle = $editMode ? "Editar Vinculação" : "Vincular Atividade";
require __DIR__ . "/partials/layout_start.php";
?>

<?php if ($errorMsg): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0"><?php echo $editMode ? 'Editar Vinculação' : 'Vincular Atividade'; ?></h2>
    <a href="/admin/atividades" class="btn btn-outline-secondary">Voltar</a>
</div>

<div class="card shadow-sm" style="max-width: 720px;">
    <div class="card-body">
        <form action="" method="post" class="row g-3">
            <input type="hidden" name="action" value="<?php echo $editMode ? 'edit' : 'create'; ?>">

            <?php if ($editMode): ?>
                <input type="hidden" name="id_turma"    value="<?php echo $idTurmaEdit; ?>">
                <input type="hidden" name="id_atividade" value="<?php echo $idAtivEdit; ?>">

                <div class="col-12 col-md-6">
                    <label class="form-label">Turma</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($nomeTurmaEdit, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Atividade</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($nomeAtividEdit, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                </div>

            <?php else: ?>

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

            <?php endif; ?>

            <div class="col-12 col-md-6">
                <label for="dt_inicio" class="form-label">Disponível a partir de</label>
                <input type="datetime-local" class="form-control" name="dt_inicio" id="dt_inicio"
                       value="<?php echo htmlspecialchars($dtInicio, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="col-12 col-md-6">
                <label for="dt_fim" class="form-label">Data limite de entrega</label>
                <input type="datetime-local" class="form-control" name="dt_fim" id="dt_fim"
                       value="<?php echo htmlspecialchars($dtFim, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <?php echo $editMode ? 'Salvar Alterações' : 'Vincular'; ?>
                </button>
                <a href="/admin/atividades" class="btn btn-outline-secondary ms-2">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . "/partials/layout_end.php"; ?>
