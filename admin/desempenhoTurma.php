<?php
session_start();
if (!isset($_SESSION['prof']) || $_SESSION['prof'] != 1) {
    header("Location: /login");
    exit();
}

$idProfessor = (int) $_SESSION['user_id'];

require_once __DIR__ . '/../conexao.php';
$database = new Database();
$db = $database->getConnection();

// Turmas do professor
$stmtTurmas = $db->prepare("SELECT id, nome FROM turmas WHERE id_professor = :idProf ORDER BY nome");
$stmtTurmas->bindParam(':idProf', $idProfessor, PDO::PARAM_INT);
$stmtTurmas->execute();
$turmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

$idTurma   = isset($_GET['turma']) && (int) $_GET['turma'] > 0 ? (int) $_GET['turma'] : null;
$nomeTurma = '';
$resumo    = [];
$detalhes  = [];

if ($idTurma !== null) {
    // Verifica que a turma pertence ao professor
    $stmtCheck = $db->prepare("SELECT nome FROM turmas WHERE id = :id AND id_professor = :idProf");
    $stmtCheck->execute([':id' => $idTurma, ':idProf' => $idProfessor]);
    $turmaRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$turmaRow) {
        $idTurma = null;
    } else {
        $nomeTurma = $turmaRow['nome'];

        // Resumo geral por aluno
        $sqlResumo = "
            SELECT
                u.id,
                u.nome,
                COUNT(tj.id)                      AS total_tentativas,
                COALESCE(SUM(tj.correta), 0)      AS corretas,
                COUNT(DISTINCT tj.jogo_slug)       AS jogos_distintos,
                COUNT(DISTINCT tj.sessao_jogo)     AS sessoes,
                MAX(tj.criado_em)                  AS ultima_atividade
            FROM usuarios u
            INNER JOIN alunos_turma alt ON alt.id_aluno = u.id
            LEFT  JOIN tentativas_jogos tj ON tj.id_aluno = u.id
            WHERE alt.id_turma = :idTurma
            GROUP BY u.id, u.nome
            ORDER BY u.nome
        ";
        $stmtResumo = $db->prepare($sqlResumo);
        $stmtResumo->bindParam(':idTurma', $idTurma, PDO::PARAM_INT);
        $stmtResumo->execute();
        $resumo = $stmtResumo->fetchAll(PDO::FETCH_ASSOC);

        // Detalhes por aluno × atividade × jogo
        $sqlDetalhes = "
            SELECT
                u.id                               AS id_aluno,
                COALESCE(a.nome, '—')              AS nome_atividade,
                tj.jogo_slug,
                COUNT(tj.id)                       AS tentativas,
                SUM(tj.correta)                    AS corretas,
                MAX(tj.criado_em)                  AS ultima
            FROM usuarios u
            INNER JOIN alunos_turma alt ON alt.id_aluno = u.id
            INNER JOIN tentativas_jogos tj ON tj.id_aluno = u.id
            LEFT  JOIN atividades a ON a.id = tj.id_atividade
            WHERE alt.id_turma = :idTurma
            GROUP BY u.id, a.id, a.nome, tj.jogo_slug
            ORDER BY u.id, a.nome, tj.jogo_slug
        ";
        $stmtDet = $db->prepare($sqlDetalhes);
        $stmtDet->bindParam(':idTurma', $idTurma, PDO::PARAM_INT);
        $stmtDet->execute();
        foreach ($stmtDet->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $detalhes[$row['id_aluno']][] = $row;
        }
    }
}

$jogoNomes = [
    'alfabeto'         => 'Complete o Alfabeto',
    'caca_palavra'     => 'Caça Palavras',
    'familia_silabica' => 'Famílias Silábicas',
    'forca'            => 'Forca',
    'forme_palavras'   => 'Forme Palavras',
    'historia'         => 'História',
    'ligar_imagem'     => 'Ligue a Imagem',
    'assimilar_som'    => 'Assimilar Som',
];

$pageTitle = "Desempenho por Turma";
require __DIR__ . "/partials/layout_start.php";
?>

<!-- Seletor de Turma -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="" class="row g-3 align-items-end">
            <div class="col-12 col-md-6 col-lg-4">
                <label for="turma" class="form-label fw-semibold">Selecionar Turma</label>
                <select class="form-select" name="turma" id="turma">
                    <option value="">— Escolha uma turma —</option>
                    <?php foreach ($turmas as $t): ?>
                        <option value="<?= (int) $t['id'] ?>"
                            <?= $idTurma === (int) $t['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['nome'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Ver Desempenho</button>
            </div>
            <?php if ($idTurma && $nomeTurma): ?>
                <div class="col-auto align-self-center">
                    <span class="badge bg-secondary fs-6">Turma: <?= htmlspecialchars($nomeTurma, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if ($idTurma === null && empty($turmas)): ?>
    <div class="alert alert-warning">Nenhuma turma cadastrada. <a href="/admin/turmas/adicionar">Cadastre uma turma</a> primeiro.</div>

<?php elseif ($idTurma === null): ?>
    <div class="alert alert-info">Selecione uma turma acima para visualizar o desempenho dos alunos.</div>

<?php elseif (empty($resumo)): ?>
    <div class="alert alert-warning">Nenhum aluno encontrado na turma <strong><?= htmlspecialchars($nomeTurma, ENT_QUOTES, 'UTF-8') ?></strong>.</div>

<?php else: ?>

    <!-- Tabela de resumo geral -->
    <h5 class="mb-3">Resumo Geral — <span class="text-muted fw-normal"><?= htmlspecialchars($nomeTurma, ENT_QUOTES, 'UTF-8') ?></span></h5>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Aluno</th>
                            <th class="text-center">Tentativas</th>
                            <th class="text-center">Corretas</th>
                            <th class="text-center">Precisão</th>
                            <th class="text-center">Jogos Usados</th>
                            <th class="text-center">Sessões</th>
                            <th class="text-center pe-3">Última Atividade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resumo as $aluno):
                            $precisao = $aluno['total_tentativas'] > 0
                                ? round($aluno['corretas'] / $aluno['total_tentativas'] * 100, 1)
                                : null;
                            $badgeClass = $precisao === null ? 'secondary'
                                : ($precisao >= 75 ? 'success' : ($precisao >= 50 ? 'warning' : 'danger'));
                        ?>
                            <tr>
                                <td class="ps-3 fw-semibold"><?= htmlspecialchars($aluno['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-center"><?= (int) $aluno['total_tentativas'] ?></td>
                                <td class="text-center"><?= (int) $aluno['corretas'] ?></td>
                                <td class="text-center">
                                    <?php if ($precisao !== null): ?>
                                        <span class="badge bg-<?= $badgeClass ?>"><?= $precisao ?>%</span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= (int) $aluno['jogos_distintos'] ?></td>
                                <td class="text-center"><?= (int) $aluno['sessoes'] ?></td>
                                <td class="text-center pe-3">
                                    <?php if ($aluno['ultima_atividade']): ?>
                                        <span class="small"><?= date('d/m/Y H:i', strtotime($aluno['ultima_atividade'])) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">Sem registros</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detalhamento por aluno (accordion) -->
    <h5 class="mb-3">Detalhamento por Aluno</h5>

    <div class="accordion shadow-sm" id="acordeaoAlunos">
        <?php foreach ($resumo as $i => $aluno):
            $alunoId  = (int) $aluno['id'];
            $collapseId = 'aluno-' . $alunoId;
            $linhasDetalhe = $detalhes[$alunoId] ?? [];
            $precisao = $aluno['total_tentativas'] > 0
                ? round($aluno['corretas'] / $aluno['total_tentativas'] * 100, 1)
                : null;
            $badgeClass = $precisao === null ? 'secondary'
                : ($precisao >= 75 ? 'success' : ($precisao >= 50 ? 'warning' : 'danger'));
        ?>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#<?= $collapseId ?>"
                            aria-expanded="false"
                            aria-controls="<?= $collapseId ?>">
                        <span class="fw-semibold me-3"><?= htmlspecialchars($aluno['nome'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php if ($precisao !== null): ?>
                            <span class="badge bg-<?= $badgeClass ?> me-2"><?= $precisao ?>% precisão</span>
                        <?php endif; ?>
                        <span class="badge bg-light text-dark border"><?= (int) $aluno['total_tentativas'] ?> tentativas</span>
                        <?php if ($aluno['ultima_atividade']): ?>
                            <span class="ms-auto text-muted small me-3">Última: <?= date('d/m/Y', strtotime($aluno['ultima_atividade'])) ?></span>
                        <?php endif; ?>
                    </button>
                </h2>
                <div id="<?= $collapseId ?>" class="accordion-collapse collapse"
                     data-bs-parent="#acordeaoAlunos">
                    <div class="accordion-body p-0">
                        <?php if (empty($linhasDetalhe)): ?>
                            <p class="text-muted p-3 mb-0">Nenhuma jogada registrada para este aluno.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Atividade</th>
                                            <th>Jogo</th>
                                            <th class="text-center">Tentativas</th>
                                            <th class="text-center">Corretas</th>
                                            <th class="text-center">Precisão</th>
                                            <th class="text-center pe-3">Última</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($linhasDetalhe as $linha):
                                            $prec = $linha['tentativas'] > 0
                                                ? round($linha['corretas'] / $linha['tentativas'] * 100, 1)
                                                : 0;
                                            $bc = $prec >= 75 ? 'success' : ($prec >= 50 ? 'warning' : 'danger');
                                            $jogoLabel = $jogoNomes[$linha['jogo_slug']] ?? $linha['jogo_slug'];
                                        ?>
                                            <tr>
                                                <td class="ps-3"><?= htmlspecialchars($linha['nome_atividade'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($jogoLabel, ENT_QUOTES, 'UTF-8') ?></td>
                                                <td class="text-center"><?= (int) $linha['tentativas'] ?></td>
                                                <td class="text-center"><?= (int) $linha['corretas'] ?></td>
                                                <td class="text-center">
                                                    <span class="badge bg-<?= $bc ?>"><?= $prec ?>%</span>
                                                </td>
                                                <td class="text-center pe-3 small">
                                                    <?= date('d/m/Y H:i', strtotime($linha['ultima'])) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php endif; ?>

<?php require __DIR__ . "/partials/layout_end.php"; ?>
