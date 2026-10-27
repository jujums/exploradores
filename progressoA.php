<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'aluno') {
    header('Location: /login');
    exit;
}

$id_aluno = (int) $_SESSION['user_id'];
$nome_aluno = htmlspecialchars($_SESSION['user_name'] ?? 'Aluno', ENT_QUOTES, 'UTF-8');

require_once __DIR__ . '/conexao.php';

$db = (new Database())->getConnection();

// ─── Estatísticas por jogo ────────────────────────────────────────────────────
$stmtJogos = $db->prepare(
    'SELECT
        jogo_slug,
        COUNT(DISTINCT sessao_jogo)               AS sessoes,
        COUNT(*)                                   AS total_movimentos,
        SUM(correta)                               AS total_acertos,
        ROUND(SUM(correta) / COUNT(*) * 100, 1)   AS taxa_acerto,
        MAX(criado_em)                             AS ultima_vez
     FROM tentativas_jogos
     WHERE id_aluno = :id
     GROUP BY jogo_slug
     ORDER BY ultima_vez DESC'
);
$stmtJogos->execute([':id' => $id_aluno]);
$estatisticasPorJogo = $stmtJogos->fetchAll(PDO::FETCH_ASSOC);

// Monta mapa jogo_slug => dados para lookup rápido
$jogoMap = [];
foreach ($estatisticasPorJogo as $row) {
    $jogoMap[$row['jogo_slug']] = $row;
}

// ─── Últimas 10 sessões ───────────────────────────────────────────────────────
$stmtHistorico = $db->prepare(
    'SELECT
        jogo_slug,
        sessao_jogo,
        COUNT(*)                                   AS movimentos,
        SUM(correta)                               AS acertos,
        ROUND(SUM(correta) / COUNT(*) * 100, 1)   AS taxa_acerto,
        MIN(criado_em)                             AS inicio_em
     FROM tentativas_jogos
     WHERE id_aluno = :id
     GROUP BY jogo_slug, sessao_jogo
     ORDER BY inicio_em DESC
     LIMIT 10'
);
$stmtHistorico->execute([':id' => $id_aluno]);
$historico = $stmtHistorico->fetchAll(PDO::FETCH_ASSOC);

// ─── Mapeamento visual dos jogos ──────────────────────────────────────────────
$jogoInfo = [
    'alfabeto'         => ['nome' => 'Complete o Alfabeto',    'icon' => '🔤'],
    'caca_palavra'     => ['nome' => 'Caça Palavras',          'icon' => '🔍'],
    'familia_silabica' => ['nome' => 'Família Silábica',       'icon' => '🧩'],
    'ligar_imagem'     => ['nome' => 'Ligue a Imagem',         'icon' => '🖼️'],
    'historia'         => ['nome' => 'Historiinha',            'icon' => '📖'],
    'assimilar_som'    => ['nome' => 'Assimilar o Som',        'icon' => '🎵'],
    'forca'            => ['nome' => 'Descubra a Palavra',     'icon' => '🪝'],
    'forme_palavras'   => ['nome' => 'Forme a Palavra',        'icon' => '🔡'],
];

// ─── Layout ───────────────────────────────────────────────────────────────────
$pageTitle    = "Meu Progresso";
$pageBodyClass = "page-progressoa";
$layoutRole   = "aluno";
$pageHeadExtra = '<link rel="stylesheet" href="statics/css/progressoA.css">';
require __DIR__ . '/partials/layout_start.php';
?>

<div class="progresso-header">
    <h2>Olá, <?= $nome_aluno ?>! 🌟 Veja como você está indo</h2>
    <p>Seu desempenho em cada jogo desde o início.</p>
</div>

<div class="jogos-grid">
<?php foreach ($jogoInfo as $slug => $info):
    $dados = $jogoMap[$slug] ?? null;
    $taxa  = $dados ? (float) $dados['taxa_acerto'] : 0;
?>
    <div class="jogo-card">
        <div class="jogo-card-icon"><?= $info['icon'] ?></div>
        <div class="jogo-card-nome"><?= htmlspecialchars($info['nome'], ENT_QUOTES, 'UTF-8') ?></div>

        <?php if ($dados): ?>
        <div class="jogo-card-stats">
            <span>🎮 <?= (int) $dados['sessoes'] ?> sessão(ões) jogada(s)</span>
            <span>⏱️ Último acesso: <?= date('d/m/Y', strtotime($dados['ultima_vez'])) ?></span>
        </div>
        <div class="taxa-bar-wrap">
            <div class="taxa-bar-fill" style="width: <?= min(100, $taxa) ?>%"></div>
        </div>
        <span class="taxa-label"><?= $taxa ?>% de acerto</span>
        <?php else: ?>
        <span class="badge-nao-jogou">Ainda não jogou</span>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
</div>

<div class="historico-section">
    <h3>📋 Últimas sessões</h3>
    <div class="historico-table-wrap">
        <?php if (empty($historico)): ?>
        <div class="sem-dados">
            <span class="sem-dados-icon">🎯</span>
            Você ainda não jogou nenhuma atividade. Vá para <a href="ativA.php">Atividades</a> e comece agora!
        </div>
        <?php else: ?>
        <table class="historico-table">
            <thead>
                <tr>
                    <th>Jogo</th>
                    <th>Data</th>
                    <th>Movimentos</th>
                    <th>Acertos</th>
                    <th>Taxa</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($historico as $h):
                $nomeJogo = $jogoInfo[$h['jogo_slug']]['nome'] ?? $h['jogo_slug'];
                $iconJogo = $jogoInfo[$h['jogo_slug']]['icon'] ?? '🎮';
            ?>
                <tr>
                    <td><?= $iconJogo ?> <?= htmlspecialchars($nomeJogo, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($h['inicio_em'])) ?></td>
                    <td><?= (int) $h['movimentos'] ?></td>
                    <td><?= (int) $h['acertos'] ?></td>
                    <td><strong><?= $h['taxa_acerto'] ?>%</strong></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/partials/layout_end.php'; ?>
