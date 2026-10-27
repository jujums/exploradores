<?php
session_start();
if (!isset($_SESSION['prof']) || $_SESSION['prof'] != 1) {
    header("Location: /login");
    exit();
}

$idProfessor = (int) $_SESSION['user_id'];

require_once __DIR__ . "/../conexao.php";
$database = new Database();
$db = $database->getConnection();

// Exclusão de turma
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'delete'
    && isset($_POST['id_turma'])) {

    $idTurma = (int) $_POST['id_turma'];
    try {
        $sqlDelete = "DELETE FROM turmas WHERE id = :idTurma AND id_professor = :idProf";
        $stmtDelete = $db->prepare($sqlDelete);
        $stmtDelete->bindParam(':idTurma', $idTurma, PDO::PARAM_INT);
        $stmtDelete->bindParam(':idProf', $idProfessor, PDO::PARAM_INT);
        $stmtDelete->execute();
        header("Location: /admin/turmas?deleted=1");
        exit();
    } catch (PDOException $e) {
        header("Location: /admin/turmas?error=delete_blocked");
        exit();
    }
}

$sql = "SELECT id, nome FROM turmas WHERE id_professor = :idProf ORDER BY nome";
$stmt = $db->prepare($sql);
$stmt->bindParam(':idProf', $idProfessor, PDO::PARAM_INT);
$stmt->execute();
$turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Turmas";
require __DIR__ . "/partials/layout_start.php";
?>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Turma cadastrada com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Turma atualizada com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Turma excluída com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'delete_blocked'): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        Não foi possível excluir a turma. Verifique se existem alunos vinculados.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0">Turmas Cadastradas</h2>
    <a href="/admin/turmas/adicionar" class="btn btn-primary">Nova Turma</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Nome</th>
                        <th class="text-end pe-3" style="width: 200px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($turmas)): ?>
                        <tr>
                            <td colspan="2" class="text-center text-muted py-4">Nenhuma turma cadastrada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($turmas as $turma): ?>
                            <tr>
                                <td class="ps-3"><?php echo htmlspecialchars($turma['nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-2">
                                        <a href="/admin/turmas/editar?id=<?php echo (int) $turma['id']; ?>"
                                           class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen-to-square"></i></a>

                                        <form action="" method="post"
                                              onsubmit="return confirm('Deseja realmente excluir esta turma?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_turma" value="<?php echo (int) $turma['id']; ?>">
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

    if ($action === 'create' && isset($_POST['nome_turma'])) {
        $novoNome = trim($_POST['nome_turma']);
        if ($novoNome !== '') {
            $sqlCreate = "INSERT INTO turmas (nome, id_professor) VALUES (:nome, :idProf)";
            $stmtCreate = $db->prepare($sqlCreate);
            $stmtCreate->bindParam(':nome', $novoNome);
            $stmtCreate->bindParam(':idProf', $idProfessor, PDO::PARAM_INT);
            $stmtCreate->execute();
            header("Location: /admin/turmas?success=1");
            exit();
        }
        header("Location: /admin/turmas?error=empty_name");
        exit();
    }

    if ($action === 'edit' && isset($_POST['id_turma'], $_POST['nome_turma'])) {
        $idTurma = (int) $_POST['id_turma'];
        $novoNome = trim($_POST['nome_turma']);
        if ($novoNome !== '') {
            $sqlEdit = "UPDATE turmas SET nome = :nome WHERE id = :idTurma AND id_professor = :idProf";
            $stmtEdit = $db->prepare($sqlEdit);
            $stmtEdit->bindParam(':nome', $novoNome);
            $stmtEdit->bindParam(':idTurma', $idTurma, PDO::PARAM_INT);
            $stmtEdit->bindParam(':idProf', $idProfessor, PDO::PARAM_INT);
            $stmtEdit->execute();
            header("Location: /admin/turmas?updated=1");
            exit();
        }
        header("Location: /admin/turmas?error=empty_name");
        exit();
    }

    if ($action === 'delete' && isset($_POST['id_turma'])) {
        $idTurma = (int) $_POST['id_turma'];
        try {
            $sqlDelete = "DELETE FROM turmas WHERE id = :idTurma AND id_professor = :idProf";
            $stmtDelete = $db->prepare($sqlDelete);
            $stmtDelete->bindParam(':idTurma', $idTurma, PDO::PARAM_INT);
            $stmtDelete->bindParam(':idProf', $idProfessor, PDO::PARAM_INT);
            $stmtDelete->execute();
            header("Location: /admin/turmas?deleted=1");
            exit();
        } catch (PDOException $e) {
            header("Location: /admin/turmas?error=delete_blocked");
            exit();
        }
    }
}

$sql = "SELECT id, nome FROM turmas WHERE id_professor = :idProf ORDER BY nome";
$stmt = $db->prepare($sql);
$stmt->bindParam(':idProf', $idProfessor, PDO::PARAM_INT);
$stmt->execute();
$turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Turmas";
require __DIR__ . "/partials/layout_start.php";
?>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Turma cadastrada com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Turma atualizada com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Turma excluída com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'empty_name'): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        Informe um nome válido para a turma.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'delete_blocked'): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        Não foi possível excluir a turma. Verifique se existem alunos vinculados.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>

<!-- Cabeçalho + botão -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0">Turmas Cadastradas</h2>
    <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#novaTurmaForm" aria-expanded="false" aria-controls="novaTurmaForm">
        Nova Turma
    </button>
</div>

<!-- Formulário de cadastro (colapsável) -->
<div class="collapse mb-4" id="novaTurmaForm">
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="" method="post" class="row g-3">
                <input type="hidden" name="action" value="create">
                <div class="col-12 col-md-9">
                    <label for="novo_nome_turma" class="form-label">Nome da Turma</label>
                    <input type="text" class="form-control" id="novo_nome_turma" name="nome_turma" required>
                </div>
                <div class="col-12 col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tabela de turmas -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Nome</th>
                        <th class="text-end pe-3" style="width: 200px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($turmas)): ?>
                        <tr>
                            <td colspan="2" class="text-center text-muted py-4">Nenhuma turma cadastrada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($turmas as $turma): ?>
                            <tr>
                                <td class="ps-3"><?php echo htmlspecialchars($turma['nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-2">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarTurma"
                                            data-id="<?php echo (int) $turma['id']; ?>"
                                            data-nome="<?php echo htmlspecialchars($turma['nome'], ENT_QUOTES, 'UTF-8'); ?>"
                                            onclick="preencherModalTurma(this)"
                                        >Editar</button>

                                        <form action="" method="post" onsubmit="return confirm('Deseja realmente excluir esta turma?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_turma" value="<?php echo (int) $turma['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
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

<!-- Modal de edição -->
<div class="modal fade" id="modalEditarTurma" tabindex="-1" aria-labelledby="modalEditarTurmaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="" method="post">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id_turma" id="editIdTurma" value="">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="modalEditarTurmaLabel">Editar Turma</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <label for="editNomeTurma" class="form-label">Nome da Turma</label>
                    <input type="text" class="form-control" id="editNomeTurma" name="nome_turma" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function preencherModalTurma(btn) {
    document.getElementById('editIdTurma').value = btn.dataset.id;
    document.getElementById('editNomeTurma').value = btn.dataset.nome;
}
</script>

<?php require __DIR__ . "/partials/layout_end.php"; ?>