<?php
session_start();
if (!isset($_SESSION['prof']) || $_SESSION['prof'] != 1) {
    header("Location: /login");
    exit();
}

require_once __DIR__ . "/../conexao.php";

$idProfessor = (int) $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();
$isAdmin = isset($_SESSION['admin']) && (int) $_SESSION['admin'] === 1;

// Determina modo: edição (GET id) ou criação
$editMode = isset($_GET['id']) && (int) $_GET['id'] > 0;
$idAlunoGet = $editMode ? (int) $_GET['id'] : 0;

// Processa POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create_professor') {
        if (!$isAdmin) {
            header("Location: /admin/alunos/adicionar?error=professor_forbidden");
            exit();
        }

        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $matricula = trim($_POST['matricula'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $matricula === '' || $senha === '') {
            header("Location: /admin/alunos/adicionar?error=professor_invalid");
            exit();
        }

        try {
            $stmtCheck = $db->prepare("SELECT id FROM usuarios WHERE email = :email OR matricula = :matricula");
            $stmtCheck->execute([':email' => $email, ':matricula' => $matricula]);
            if ($stmtCheck->fetch()) {
                header("Location: /admin/alunos/adicionar?error=professor_duplicate");
                exit();
            }

            $stmtProfessor = $db->prepare(
                "INSERT INTO usuarios (nome, email, matricula, senha, professor)
                 VALUES (:nome, :email, :matricula, :senha, 1)"
            );
            $stmtProfessor->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':matricula' => $matricula,
                ':senha' => password_hash($senha, PASSWORD_DEFAULT),
            ]);
            header("Location: /admin/alunos/adicionar?success=professor_created");
            exit();
        } catch (PDOException $e) {
            header("Location: /admin/alunos/adicionar?error=professor_save");
            exit();
        }
    }

    if (($action === 'create' || $action === 'edit')
        && isset($_POST['nome'], $_POST['email'], $_POST['matricula'], $_POST['turma'])) {

        $nome      = trim($_POST['nome']);
        $email     = trim($_POST['email']);
        $matricula = trim($_POST['matricula']);
        $turma     = (int) $_POST['turma'];

        $idAlunoPost = isset($_POST['id_aluno']) ? (int) $_POST['id_aluno'] : 0;
        $backUrl = $action === 'edit' && $idAlunoPost > 0
            ? '/admin/alunos/editar?id=' . $idAlunoPost
            : '/admin/alunos/adicionar';

        if ($nome === '' || $email === '' || $matricula === '' || $turma <= 0) {
            header("Location: {$backUrl}&error=invalid");
            exit();
        }

        try {
            $db->beginTransaction();

            // Garante que a turma pertence ao professor logado
            $stmtTurma = $db->prepare("SELECT id FROM turmas WHERE id = :id AND id_professor = :prof");
            $stmtTurma->execute([':id' => $turma, ':prof' => $idProfessor]);
            if (!$stmtTurma->fetch()) {
                $db->rollBack();
                header("Location: {$backUrl}&error=turma");
                exit();
            }

            if ($action === 'create') {
                $senha = password_hash($matricula, PASSWORD_DEFAULT);
                $stmtIns = $db->prepare("INSERT INTO usuarios (nome, email, matricula, senha) VALUES (:nome, :email, :matricula, :senha)");
                $stmtIns->execute([
                    ':nome'      => $nome,
                    ':email'     => $email,
                    ':matricula' => $matricula,
                    ':senha'     => $senha,
                ]);
                $idAluno = (int) $db->lastInsertId();
            } else {
                if ($idAlunoPost <= 0) {
                    $db->rollBack();
                    header("Location: /admin/alunos/adicionar?error=invalid");
                    exit();
                }
                $idAluno = $idAlunoPost;

                $stmtUpd = $db->prepare("UPDATE usuarios SET nome = :nome, email = :email, matricula = :matricula WHERE id = :id");
                $stmtUpd->execute([
                    ':nome'      => $nome,
                    ':email'     => $email,
                    ':matricula' => $matricula,
                    ':id'        => $idAluno,
                ]);

                // Remove vínculo de turma anterior (somente turmas deste professor)
                $stmtDelRel = $db->prepare("DELETE alt FROM alunos_turma alt
                                            INNER JOIN turmas t ON t.id = alt.id_turma
                                            WHERE alt.id_aluno = :idAluno AND t.id_professor = :idProf");
                $stmtDelRel->execute([':idAluno' => $idAluno, ':idProf' => $idProfessor]);
            }

            // Insere novo vínculo de turma
            $stmtRel = $db->prepare("INSERT INTO alunos_turma (id_aluno, id_turma) VALUES (:idAluno, :idTurma)");
            $stmtRel->execute([':idAluno' => $idAluno, ':idTurma' => $turma]);

            $db->commit();
            header("Location: /admin/alunos?success=" . ($action === 'create' ? 'created' : 'updated'));
            exit();
        } catch (PDOException $e) {
            if ($db->inTransaction()) $db->rollBack();
            header("Location: {$backUrl}&error=save");
            exit();
        }
    }
}

// Carrega turmas do professor
$stmtTurmas = $db->prepare("SELECT id, nome FROM turmas WHERE id_professor = :idProf ORDER BY nome");
$stmtTurmas->bindParam(':idProf', $idProfessor, PDO::PARAM_INT);
$stmtTurmas->execute();
$turmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

// Se edição, carrega dados do aluno
$alunoEdit = null;
if ($editMode) {
    $stmtAlunoEdit = $db->prepare(
        "SELECT u.id, u.nome, u.email, u.matricula, t.id AS id_turma
         FROM usuarios u
         INNER JOIN alunos_turma alt ON alt.id_aluno = u.id
         INNER JOIN turmas t ON t.id = alt.id_turma
         WHERE u.id = :id AND t.id_professor = :prof"
    );
    $stmtAlunoEdit->execute([':id' => $idAlunoGet, ':prof' => $idProfessor]);
    $alunoEdit = $stmtAlunoEdit->fetch(PDO::FETCH_ASSOC);

    if (!$alunoEdit) {
        // Aluno não encontrado ou não pertence ao professor
        header("Location: /admin/alunos");
        exit();
    }
}

$pageTitle = $editMode ? "Editar Aluno" : "Adicionar Aluno";
$showProfessorForm = $isAdmin && (
    (isset($_GET['success']) && $_GET['success'] === 'professor_created')
    || (isset($_GET['error']) && str_starts_with($_GET['error'], 'professor_'))
);
require __DIR__ . "/partials/layout_start.php";
?>

<?php if (isset($_GET['success']) && $_GET['success'] === 'professor_created'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Professor cadastrado com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <?php
        $err = $_GET['error'];
        if ($err === 'invalid')     echo 'Preencha todos os campos obrigatórios.';
        elseif ($err === 'turma')   echo 'A turma selecionada não pertence ao seu cadastro.';
        elseif ($err === 'professor_invalid') echo 'Informe nome, e-mail válido, matrícula e senha do professor.';
        elseif ($err === 'professor_duplicate') echo 'Já existe um usuário com esse e-mail ou matrícula.';
        elseif ($err === 'professor_forbidden') echo 'Apenas administradores podem cadastrar professores.';
        elseif ($err === 'professor_save') echo 'Não foi possível cadastrar o professor. Tente novamente.';
        else                        echo 'Não foi possível salvar os dados do aluno.';
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="/admin/alunos" class="btn btn-outline-secondary btn-sm">&larr; Voltar</a>
    <h2 class="h5 mb-0"><?php echo $editMode ? 'Editar Aluno' : 'Adicionar Aluno'; ?></h2>
</div>

<?php if (!$editMode && $isAdmin): ?>
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo $showProfessorForm ? '' : 'active'; ?>" id="student-tab"
                    data-bs-toggle="tab" data-bs-target="#student-panel" type="button" role="tab"
                    aria-controls="student-panel" aria-selected="<?php echo $showProfessorForm ? 'false' : 'true'; ?>">
                Adicionar aluno
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo $showProfessorForm ? 'active' : ''; ?>" id="professor-tab"
                    data-bs-toggle="tab" data-bs-target="#professor-panel" type="button" role="tab"
                    aria-controls="professor-panel" aria-selected="<?php echo $showProfessorForm ? 'true' : 'false'; ?>">
                Adicionar professor
            </button>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade <?php echo $showProfessorForm ? '' : 'show active'; ?>" id="student-panel"
             role="tabpanel" aria-labelledby="student-tab">
<?php endif; ?>

<div class="card shadow-sm" style="max-width: 760px;">
    <div class="card-body">
        <form action="" method="post" class="row g-3">
            <input type="hidden" name="action" value="<?php echo $editMode ? 'edit' : 'create'; ?>">
            <?php if ($editMode): ?>
                <input type="hidden" name="id_aluno" value="<?php echo (int) $alunoEdit['id']; ?>">
            <?php endif; ?>

            <div class="col-12 col-md-6">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" required
                       value="<?php echo $editMode ? htmlspecialchars($alunoEdit['nome'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            </div>

            <div class="col-12 col-md-6">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" required
                       value="<?php echo $editMode ? htmlspecialchars($alunoEdit['email'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            </div>

            <div class="col-12 col-md-6">
                <label for="matricula" class="form-label">Matrícula</label>
                <input type="text" class="form-control" id="matricula" name="matricula" required
                       value="<?php echo $editMode ? htmlspecialchars($alunoEdit['matricula'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            </div>

            <div class="col-12 col-md-6">
                <label for="turma" class="form-label">Turma</label>
                <select class="form-select" name="turma" id="turma" required>
                    <option value="">Selecione a turma</option>
                    <?php foreach ($turmas as $t): ?>
                        <option value="<?php echo (int) $t['id']; ?>"
                            <?php echo ($editMode && (int) $t['id'] === (int) $alunoEdit['id_turma']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($t['nome'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (!$editMode): ?>
                <div class="col-12">
                    <p class="form-text text-muted mb-0">A senha inicial será a própria matrícula do aluno.</p>
                </div>
            <?php endif; ?>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <?php echo $editMode ? 'Salvar Alterações' : 'Cadastrar Aluno'; ?>
                </button>
                <a href="/admin/alunos" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php if (!$editMode && $isAdmin): ?>
        </div>
        <div class="tab-pane fade <?php echo $showProfessorForm ? 'show active' : ''; ?>" id="professor-panel"
             role="tabpanel" aria-labelledby="professor-tab">
            <div class="card shadow-sm" style="max-width: 760px;">
                <div class="card-body">
                    <form action="" method="post" class="row g-3">
                        <input type="hidden" name="action" value="create_professor">
                        <div class="col-12 col-md-6">
                            <label for="professor-name" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="professor-name" name="nome" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="professor-email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="professor-email" name="email" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="professor-enrollment" class="form-label">Matrícula</label>
                            <input type="text" class="form-control" id="professor-enrollment" name="matricula" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="professor-password" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="professor-password" name="senha" required>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-warning">Cadastrar professor</button>
                            <a href="/admin/alunos" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . "/partials/layout_end.php"; ?>
