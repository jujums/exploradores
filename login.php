<?php
session_start();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . "/conexao.php";

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        $erro = 'Não foi possível conectar ao banco de dados.';
    } elseif ($email === '' || $senha === '') {
        $erro = 'Informe e-mail e senha.';
    } else {
        // fetch() em vez de rowCount(): funciona mesmo se houver e-mail repetido
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email ORDER BY id LIMIT 1");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $loginOk = false;

        if ($row) {
            $hashSalvo = (string) $row['senha'];

            if (password_verify($senha, $hashSalvo)) {
                $loginOk = true;

                // Atualiza o hash se o algoritmo padrão do PHP mudou
                if (password_needs_rehash($hashSalvo, PASSWORD_DEFAULT)) {
                    $upd = $db->prepare("UPDATE usuarios SET senha = :senha WHERE id = :id");
                    $upd->execute([':senha' => password_hash($senha, PASSWORD_DEFAULT), ':id' => $row['id']]);
                }
            } elseif (password_get_info($hashSalvo)['algo'] === null && hash_equals($hashSalvo, $senha)) {
                // Senha foi gravada em TEXTO PURO direto no banco (INSERT manual).
                // Aceita esta vez e já converte para hash, para as próximas vezes.
                $loginOk = true;
                $upd = $db->prepare("UPDATE usuarios SET senha = :senha WHERE id = :id");
                $upd->execute([':senha' => password_hash($senha, PASSWORD_DEFAULT), ':id' => $row['id']]);
            }
        }

        if ($loginOk) {
            // Limpa qualquer sessão anterior (ex.: aluno logado antes no mesmo navegador)
            $_SESSION = [];
            session_regenerate_id(true);

            $_SESSION['user_id']   = (int) $row['id'];
            $_SESSION['user_name'] = $row['nome'];

            $ehAdmin     = (int) $row['admin'] === 1;
            $ehProfessor = (int) $row['professor'] === 1 || $ehAdmin; // admin também entra no painel

            if ($ehProfessor) {
                $_SESSION['user_type'] = 'professor';
                $_SESSION['prof']      = 1;
                $_SESSION['admin']     = $ehAdmin ? 1 : 0;
                header("Location: /admin");
            } else {
                $_SESSION['user_type'] = 'aluno';
                header("Location: /inicio");
            }
            exit();
        }

        $erro = 'E-mail ou senha inválidos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="statics/css/loginA.css">
</head>
<body class="page-logina">
    <img src="statics/imgs/logo.png" alt="logo" class="arco">
    <img src="statics/imgs/sol.png" alt="sol" class="sol">

    <img src="statics/imgs/nuvem.png" alt="nuvem" class="nuvem1">
    <img src="statics/imgs/nuvem.png" alt="nuvem" class="nuvem2">
    <img src="statics/imgs/nuvem.png" alt="nuvem" class="nuvem3">

    <div class="container">
        <h1>Login</h1>
        <?php if ($erro !== ''): ?>
            <p class="erro-login" style="color:#b00020;font-weight:bold;"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form action="/login" method="post" class="form">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required
                   value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>

            <button type="submit">Entrar</button>
        </form>
    </div>
    <div vw class="enabled">
        <div vw-access-button class="active"></div>
        <div vw-plugin-wrapper>
            <div class="vw-plugin-top-wrapper"></div>
        </div>
    </div>
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script>
        new window.VLibras.Widget("https://vlibras.gov.br/app");
    </script>
</body>
</html>
