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

// Cadastro de professores disponível somente para administradores.
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'create_professor') {

    if (!$isAdmin) {
        header("Location: /admin/alunos?error=forbidden");
        exit();
    }

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $matricula = trim($_POST['matricula'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $matricula === '' || $senha === '') {
        header("Location: /admin/alunos?error=professor_invalid");
        exit();
    }

    try {
        $stmtCheck = $db->prepare("SELECT id FROM usuarios WHERE email = :email OR matricula = :matricula");
        $stmtCheck->execute([':email' => $email, ':matricula' => $matricula]);
        if ($stmtCheck->fetch()) {
            header("Location: /admin/alunos?error=professor_duplicate");
            exit();
        }

        $stmtInsert = $db->prepare(
            "INSERT INTO usuarios (nome, email, matricula, senha, professor)
             VALUES (:nome, :email, :matricula, :senha, 1)"
        );
        $stmtInsert->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':matricula' => $matricula,
            ':senha' => password_hash($senha, PASSWORD_DEFAULT),
        ]);
        header("Location: /admin/alunos?success=professor_created");
        exit();
    } catch (PDOException $e) {
        header("Location: /admin/alunos?error=professor_create");
        exit();
    }
}

// Exclusão de aluno
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'delete'
    && isset($_POST['id_aluno'])) {

    $idAluno = (int) $_POST['id_aluno'];

    try {
        $db->beginTransaction();

        $sqlDeleteRel = "DELETE alt FROM alunos_turma alt
                         INNER JOIN turmas t ON t.id = alt.id_turma
                         WHERE alt.id_aluno = :idAluno AND t.id_professor = :idProf";
        $stmtDeleteRel = $db->prepare($sqlDeleteRel);
        $stmtDeleteRel->bindParam(':idAluno', $idAluno, PDO::PARAM_INT);
        $stmtDeleteRel->bindParam(':idProf', $idProfessor, PDO::PARAM_INT);
        $stmtDeleteRel->execute();

        $sqlDeleteAluno = "DELETE FROM usuarios WHERE id = :idAluno";
        $stmtDeleteAluno = $db->prepare($sqlDeleteAluno);
        $stmtDeleteAluno->bindParam(':idAluno', $idAluno, PDO::PARAM_INT);
        $stmtDeleteAluno->execute();

        $db->commit();
        header("Location: /admin/alunos?success=deleted");
        exit();
    } catch (PDOException $e) {
        if ($db->inTransaction()) $db->rollBack();
        header("Location: /admin/alunos?error=delete");
        exit();
    }
}

// Lista alunos do professor
$sqlAlunos = "SELECT u.id, u.nome, u.email, u.matricula, t.id AS id_turma, t.nome AS nome_turma
              FROM usuarios u
              INNER JOIN alunos_turma alt ON alt.id_aluno = u.id
              INNER JOIN turmas t ON t.id = alt.id_turma
              WHERE t.id_professor = :idProf
                AND (u.professor IS NULL OR u.professor = 0)
              ORDER BY t.nome, u.nome";
$stmtAlunos = $db->prepare($sqlAlunos);
$stmtAlunos->bindParam(':idProf', $idProfessor, PDO::PARAM_INT);
$stmtAlunos->execute();
$alunos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Alunos";
require __DIR__ . "/partials/layout_start.php";
?>

<?php if (isset($_GET['success']) && $_GET['success'] === 'created'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Aluno cadastrado com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['success']) && $_GET['success'] === 'updated'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Aluno atualizado com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Aluno excluído com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'delete'): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        Não foi possível excluir o aluno.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['success']) && $_GET['success'] === 'professor_created'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Professor cadastrado com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'professor_duplicate'): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        Já existe um usuário com esse e-mail ou matrícula.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'professor_invalid'): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        Confira os dados: preencha todos os campos e informe um e-mail válido.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'professor_create'): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        Não foi possível cadastrar o professor. Tente novamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'forbidden'): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        Apenas administradores podem cadastrar professores.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>

<?php if ($isAdmin): ?>
    <section class="card shadow-sm mb-4" aria-labelledby="teacherRegistrationTitle">
        <div class="card-body">
            <h2 class="h5 mb-1" id="teacherRegistrationTitle">Cadastro de professores</h2>
            <p class="text-muted mb-3">Preencha os dados para cadastrar um professor nesta página.</p>
            <form action="/admin/alunos" method="post" class="row g-3">
                <input type="hidden" name="action" value="create_professor">
                <div class="col-12 col-md-6">
                    <label for="teacherName" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="teacherName" name="nome" required>
                </div>
                <div class="col-12 col-md-6">
                    <label for="teacherEmail" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="teacherEmail" name="email" required>
                </div>
                <div class="col-12 col-md-6">
                    <label for="teacherEnrollment" class="form-label">Matrícula</label>
                    <input type="text" class="form-control" id="teacherEnrollment" name="matricula" required>
                </div>
                <div class="col-12 col-md-6">
                    <label for="teacherPassword" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="teacherPassword" name="senha" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-warning">Cadastrar professor</button>
                    <a href="/admin/professores" class="btn btn-outline-secondary">Gerenciar professores</a>
                </div>
            </form>
        </div>
    </section>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0">Alunos Cadastrados</h2>
    <a href="/admin/alunos/adicionar" class="btn btn-primary">Adicionar Aluno</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Nome</th>
                        <th>E-mail</th>
                        <th>Matrícula</th>
                        <th>Turma</th>
                        <th class="text-end pe-3" style="width: 200px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($alunos)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Nenhum aluno cadastrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($alunos as $aluno): ?>
                            <tr>
                                <td class="ps-3"><?php echo htmlspecialchars($aluno['nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($aluno['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($aluno['matricula'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($aluno['nome_turma'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-2">
                                        <a href="/admin/alunos/editar?id=<?php echo (int) $aluno['id']; ?>"
                                           class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen-to-square"></i></a>

                                        <form action="" method="post"
                                              onsubmit="return confirm('Deseja realmente excluir este aluno?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_aluno" value="<?php echo (int) $aluno['id']; ?>">
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
