<?php
header('Content-Type: application/json; charset=utf-8');

session_start();

$sessaoValida = (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'professor')
             || (isset($_SESSION['prof'])      && (int) $_SESSION['prof'] === 1);

if (!$sessaoValida) {
    http_response_code(401);
    echo json_encode(['erro' => 'Sessão inválida']);
    exit;
}

$id_professor = (int) $_SESSION['user_id'];
$id_turma     = isset($_GET['id_turma']) ? (int) $_GET['id_turma'] : 0;
$id_aluno     = isset($_GET['id_aluno'])  ? (int) $_GET['id_aluno']  : 0;
$jogo_slug = $_GET['jogo_slug'] ?? '';
$jogosPermitidos = [
    'alfabeto',
    'assimilar_som',
    'caca_palavra',
    'familia_silabica',
    'historia',
    'ligar_imagem',
    'forca',
    'forme_palavras',
];

if (!is_string($jogo_slug)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Jogo inválido']);
    exit;
}

$jogo_slug = trim($jogo_slug);

if ($id_turma <= 0) {
    http_response_code(400);
    echo json_encode(['erro' => 'id_turma obrigatório']);
    exit;
}

if ($jogo_slug !== '' && !in_array($jogo_slug, $jogosPermitidos, true)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Jogo inválido']);
    exit;
}

require_once __DIR__ . '/../conexao.php';

try {
    $db = (new Database())->getConnection();

    // Garante que a turma pertence ao professor autenticado
    $stmtCheck = $db->prepare(
        'SELECT id FROM turmas WHERE id = :id_turma AND id_professor = :id_prof LIMIT 1'
    );
    $stmtCheck->execute([':id_turma' => $id_turma, ':id_prof' => $id_professor]);
    if (!$stmtCheck->fetch()) {
        http_response_code(403);
        echo json_encode(['erro' => 'Turma não encontrada ou sem permissão']);
        exit;
    }

    // ─── Total de alunos na turma ─────────────────────────────────────────
    $stmtAlunos = $db->prepare(
        'SELECT COUNT(*) FROM alunos_turma WHERE id_turma = :id_turma'
    );
    $stmtAlunos->execute([':id_turma' => $id_turma]);
    $totalAlunos = (int) $stmtAlunos->fetchColumn();

    // ─── Stats por jogo_slug ──────────────────────────────────────────────
    $filtroAluno = $id_aluno > 0 ? 'AND tj.id_aluno = :id_aluno' : '';
    $filtroJogo = $jogo_slug !== '' ? 'AND tj.jogo_slug = :jogo_slug' : '';

    $sql = "
        SELECT
            tj.jogo_slug,
            COUNT(DISTINCT tj.sessao_jogo)              AS total_sessoes,
            COUNT(DISTINCT tj.id_aluno)                 AS total_alunos,
            SUM(tj.correta)                             AS total_acertos,
            COUNT(*)                                    AS total_movimentos,
            ROUND(SUM(tj.correta) / COUNT(*) * 100, 1) AS taxa_acerto
        FROM tentativas_jogos tj
        JOIN alunos_turma alt ON alt.id_aluno = tj.id_aluno
        WHERE alt.id_turma = :id_turma
        $filtroAluno
        $filtroJogo
        GROUP BY tj.jogo_slug
        ORDER BY total_sessoes DESC
    ";

    $params = [':id_turma' => $id_turma];
    if ($id_aluno > 0) {
        $params[':id_aluno'] = $id_aluno;
    }
    if ($jogo_slug !== '') {
        $params[':jogo_slug'] = $jogo_slug;
    }

    $stmtJogos = $db->prepare($sql);
    $stmtJogos->execute($params);
    $jogos = $stmtJogos->fetchAll(PDO::FETCH_ASSOC);

    // Converte tipos numéricos
    foreach ($jogos as &$j) {
        $j['total_sessoes']    = (int)   $j['total_sessoes'];
        $j['total_alunos']     = (int)   $j['total_alunos'];
        $j['total_acertos']    = (int)   $j['total_acertos'];
        $j['total_movimentos'] = (int)   $j['total_movimentos'];
        $j['taxa_acerto']      = (float) $j['taxa_acerto'];
        $j['total_erros']      = $j['total_movimentos'] - $j['total_acertos'];
    }
    unset($j);

    // ─── Lista de alunos com taxa geral ───────────────────────────────────
    $stmtRanking = $db->prepare(
        "SELECT
            u.id,
            u.nome,
            COUNT(DISTINCT tj.sessao_jogo)              AS sessoes,
            ROUND(SUM(tj.correta) / COUNT(*) * 100, 1) AS taxa_acerto
         FROM usuarios u
         JOIN alunos_turma alt ON alt.id_aluno = u.id
         LEFT JOIN tentativas_jogos tj ON tj.id_aluno = u.id
         WHERE alt.id_turma = :id_turma
         GROUP BY u.id, u.nome
         ORDER BY taxa_acerto DESC"
    );
    $stmtRanking->execute([':id_turma' => $id_turma]);
    $alunos = $stmtRanking->fetchAll(PDO::FETCH_ASSOC);

    foreach ($alunos as &$al) {
        $al['sessoes']     = (int)   $al['sessoes'];
        $al['taxa_acerto'] = (float) ($al['taxa_acerto'] ?? 0);
    }
    unset($al);

    echo json_encode([
        'total_alunos' => $totalAlunos,
        'jogos'        => $jogos,
        'alunos'       => $alunos,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao carregar estatísticas']);
}
