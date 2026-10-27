<?php
header('Content-Type: application/json; charset=utf-8');

session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'aluno') {
    http_response_code(401);
    echo json_encode(['erro' => 'Sessão inválida']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

$raw = file_get_contents('php://input');
$dados = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['erro' => 'JSON inválido']);
    exit;
}

// Validar campos obrigatórios
$obrigatorios = ['jogo_slug', 'sessao_jogo', 'numero_tentativa', 'movimento_tipo', 'correta', 'id_atividade'];
foreach ($obrigatorios as $campo) {
    if (!isset($dados[$campo])) {
        http_response_code(400);
        echo json_encode(['erro' => "Campo obrigatório ausente: $campo"]);
        exit;
    }
}

// Sanitizar / tipar valores
$id_aluno        = (int) $_SESSION['user_id'];
$id_atividade    = (int) $dados['id_atividade'];
$jogo_slug       = substr(preg_replace('/[^a-z0-9_\-]/', '', (string) $dados['jogo_slug']), 0, 60);
$sessao_jogo     = substr(preg_replace('/[^a-zA-Z0-9\-]/', '', (string) $dados['sessao_jogo']), 0, 36);
$fase            = isset($dados['fase'])  ? substr((string) $dados['fase'], 0, 80)  : null;
$numero_tentativa = max(1, (int) $dados['numero_tentativa']);
$movimento_tipo  = substr(preg_replace('/[^a-z0-9_\-]/', '', (string) $dados['movimento_tipo']), 0, 40);
$movimento       = isset($dados['movimento']) ? json_encode($dados['movimento'])   : null;
$correta         = (int) (bool) $dados['correta'];
$pontuacao_delta = isset($dados['pontuacao_delta']) ? round((float) $dados['pontuacao_delta'], 2) : null;
$tempo_ms        = isset($dados['tempo_ms']) ? (int) $dados['tempo_ms'] : null;

require_once __DIR__ . '/../conexao.php';

try {
    $db = (new Database())->getConnection();

    // Valida que a atividade está disponível para o aluno (via turma)
    $stmtCheck = $db->prepare(
        'SELECT 1 FROM atividades_turma atvt
         INNER JOIN alunos_turma alnt ON alnt.id_turma = atvt.id_turma
         WHERE atvt.id_atividade = :id_atividade
           AND alnt.id_aluno = :id_aluno
         LIMIT 1'
    );
    $stmtCheck->execute([':id_atividade' => $id_atividade, ':id_aluno' => $id_aluno]);
    if (!$stmtCheck->fetch()) {
        http_response_code(403);
        echo json_encode(['erro' => 'Atividade inválida ou não disponível']);
        exit;
    }

    $stmt = $db->prepare(
        'INSERT INTO tentativas_jogos
            (id_aluno, id_atividade, jogo_slug, sessao_jogo, fase, numero_tentativa,
             movimento_tipo, movimento, correta, pontuacao_delta, tempo_ms)
         VALUES
            (:id_aluno, :id_atividade, :jogo_slug, :sessao_jogo, :fase, :numero_tentativa,
             :movimento_tipo, :movimento, :correta, :pontuacao_delta, :tempo_ms)'
    );

    $stmt->execute([
        ':id_aluno'         => $id_aluno,
        ':id_atividade'     => $id_atividade,
        ':jogo_slug'        => $jogo_slug,
        ':sessao_jogo'      => $sessao_jogo,
        ':fase'             => $fase,
        ':numero_tentativa' => $numero_tentativa,
        ':movimento_tipo'   => $movimento_tipo,
        ':movimento'        => $movimento,
        ':correta'          => $correta,
        ':pontuacao_delta'  => $pontuacao_delta,
        ':tempo_ms'         => $tempo_ms,
    ]);

    echo json_encode(['sucesso' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao salvar jogada']);
}
