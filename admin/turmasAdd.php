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

$editMode = isset($_GET['id']) && (int) $_GET['id'] > 0;
$idTurma  = $editMode ? (int) $_GET['id'] : null;
$nomeTurma = '';
$errorMsg  = '';

// Busca os dados atuais no modo edição
if ($editMode) {
    $sqlFetch = "SELECT nome FROM turmas WHERE id = :id AND id_professor = :idProf";
    $stmtFetch = $db->prepare($sqlFetch);
    $stmtFetch->bindParam(':id', $idTurma, PDO::PARAM_INT);
    $stmtFetch->bindParam(':idProf', $idProfessor, PDO::PARAM_INT);
    $stmtFetch->execute();
    $turma = $stmtFetch->fetch(PDO::FETCH_ASSOC);
    if (!$turma) {
        header("Location: /admin/turmas");
        exit();
    }
    $nomeTurma = $turma['nome'];
}

// Processamento do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $nomeTurmaPost = trim($_POST['nome_turma'] ?? '');

    if ($nomeTurmaPost === '') {
        $errorMsg = "O nome da turma é obrigatório.";
    } elseif ($_POST['action'] === 'create') {
        $sqlInsert = "INSERT INTO turmas (nome, id_professor) VALUES (:nome, :idProf)";
        $stmtInsert = $db->prepare($sqlInsert);
        $stmtInsert->bindParam(':nome', $nomeTurmaPost);
        $stmtInsert->bindParam(':idProf', $idProfessor, PDO::PARAM_INT);
        $stmtInsert->execute();
        header("Location: /admin/turmas?success=1");
        exit();
    } elseif ($_POST['action'] === 'edit') {
        $idTurmaPost = (int) ($_POST['id_turma'] ?? 0);
        $sqlUpdate = "UPDATE turmas SET nome = :nome
                      WHERE id = :id AND id_professor = :idProf";
        $stmtUpdate = $db->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':nome', $nomeTurmaPost);
        $stmtUpdate->bindParam(':id', $idTurmaPost, PDO::PARAM_INT);
        $stmtUpdate->bindParam(':idProf', $idProfessor, PDO::PARAM_INT);
        $stmtUpdate->execute();
        header("Location: /admin/turmas?updated=1");
        exit();
    }
}

$pageTitle = $editMode ? "Editar Turma" : "Nova Turma";
require __DIR__ . "/partials/layout_start.php";
?>

<?php if ($errorMsg): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0"><?php echo $editMode ? 'Editar Turma' : 'Nova Turma'; ?></h2>
    <a href="/admin/turmas" class="btn btn-outline-secondary">Voltar</a>
</div>

<div class="card shadow-sm" style="max-width: 640px;">
    <div class="card-body">
        <form action="" method="post" class="row g-3">
            <input type="hidden" name="action" value="<?php echo $editMode ? 'edit' : 'create'; ?>">
            <?php if ($editMode): ?>
                <input type="hidden" name="id_turma" value="<?php echo $idTurma; ?>">
            <?php endif; ?>

            <div class="col-12">
                <label for="nome_turma" class="form-label">Nome da Turma</label>
                <input type="text" class="form-control" id="nome_turma" name="nome_turma"
                       value="<?php echo htmlspecialchars($nomeTurma, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <?php echo $editMode ? 'Salvar Alterações' : 'Cadastrar'; ?>
                </button>
                <a href="/admin/turmas" class="btn btn-outline-secondary ms-2">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . "/partials/layout_end.php"; ?>