<?php
session_start();
if (!isset($_SESSION['prof']) || $_SESSION['prof'] != 1) {
    header("Location: /login");
    exit();
}

if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    header("Location: /admin");
    exit();
}

require_once __DIR__ . "/../conexao.php";
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create'
        && isset($_POST['nome'], $_POST['email'], $_POST['matricula'], $_POST['senha'])) {

        $nome      = trim($_POST['nome']);
        $email     = trim($_POST['email']);
        $matricula = trim($_POST['matricula']);
        $senha     = password_hash($_POST['senha'], PASSWORD_DEFAULT);

        // Verifica duplicidade de e-mail ou matrícula
        $stmtCheck = $db->prepare("SELECT id FROM usuarios WHERE email = :email OR matricula = :matricula");
        $stmtCheck->execute([':email' => $email, ':matricula' => $matricula]);
        if ($stmtCheck->fetch()) {
            header("Location: /admin/professores?error=duplicate");
            exit();
        }

        $sql = "INSERT INTO usuarios (nome, email, matricula, senha, professor) VALUES (:nome, :email, :matricula, :senha, 1)";
        $stmt = $db->prepare($sql);
        $stmt->execute([':nome' => $nome, ':email' => $email, ':matricula' => $matricula, ':senha' => $senha]);
        header("Location: /admin/professores?success=1");
        exit();
    }

    if ($_POST['action'] === 'delete' && isset($_POST['id_prof'])) {
        $idProf = (int) $_POST['id_prof'];
        // Não permite excluir o próprio usuário logado
        if ($idProf !== (int) $_SESSION['user_id']) {
            $stmtDel = $db->prepare("DELETE FROM usuarios WHERE id = :id AND professor = 1");
            $stmtDel->execute([':id' => $idProf]);
        }
        header("Location: /admin/professores?deleted=1");
        exit();
    }
}

// Lista todos os professores
$stmtProfs = $db->prepare("SELECT id, nome, email, matricula FROM usuarios WHERE professor = 1 ORDER BY nome");
$stmtProfs->execute();
$professores = $stmtProfs->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Professores";
require __DIR__ . "/partials/layout_start.php";
?>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Professor cadastrado com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Professor excluído com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'duplicate'): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        Já existe um professor com esse e-mail ou matrícula.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h2 class="h5 mb-3">Cadastrar professor</h2>
        <form action="" method="post" class="row g-3">
            <input type="hidden" name="action" value="create">

            <div class="col-12 col-md-6">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>

            <div class="col-12 col-md-6">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="col-12 col-md-6">
                <label for="matricula" class="form-label">Matrícula</label>
                <input type="text" class="form-control" id="matricula" name="matricula" required>
            </div>

            <div class="col-12 col-md-6">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">Cadastrar professor</button>
            </div>
        </form>
    </div>
</div>

<!-- Tabela de professores -->
<h2 class="h5 mb-3">Professores cadastrados</h2>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Nome</th>
                        <th>E-mail</th>
                        <th>Matrícula</th>
                        <th class="text-end pe-3" style="width: 120px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($professores)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Nenhum professor cadastrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($professores as $prof): ?>
                            <tr>
                                <td class="ps-3"><?php echo htmlspecialchars($prof['nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($prof['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($prof['matricula'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-end pe-3">
                                    <?php if ((int) $prof['id'] !== (int) $_SESSION['user_id']): ?>
                                        <form action="" method="post" onsubmit="return confirm('Excluir este professor?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_prof" value="<?php echo (int) $prof['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Você</span>
                                    <?php endif; ?>
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