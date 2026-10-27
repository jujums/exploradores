<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'aluno') {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/conexao.php';
$db = (new Database())->getConnection();

$stmt = $db->prepare(
    'SELECT a.id, a.nome, a.descricao, a.image, a.caminho
       FROM atividades a
       INNER JOIN atividades_turma atvt ON atvt.id_atividade = a.id
       INNER JOIN alunos_turma     alnt ON alnt.id_turma      = atvt.id_turma
      WHERE alnt.id_aluno = :id_aluno
        AND NOW() BETWEEN atvt.dt_inicio AND atvt.dt_fim
      ORDER BY a.nome'
);
$stmt->execute([':id_aluno' => (int) $_SESSION['user_id']]);
$atividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle    = "Atividades";
$pageBodyClass = "page-ativa";
$layoutRole   = "aluno";
require __DIR__ . "/partials/layout_start.php";

$cores = ['red', 'orange', 'yellow', 'green', 'blue', 'purple'];
?>

  <div class="grid">

    <?php if (empty($atividades)): ?>
      <div class="sem-atividades">
        <p>Nenhuma atividade disponível para a sua turma no momento.</p>
      </div>
    <?php else: ?>
      <?php foreach ($atividades as $i => $atv): ?>
        <?php
          $cor = $cores[$i % count($cores)];
          $caminho = ltrim(trim($atv['caminho']), '/');
          if (strpos($caminho, 'games/') === 0) {
              $caminho = substr($caminho, strlen('games/'));
          }
          if (strtolower(pathinfo($caminho, PATHINFO_FILENAME)) === 'historia') {
              $caminho = 'historia.php';
          }
          $imagem = strcasecmp(basename($caminho), 'forca.php') === 0
              ? 'forca.png'
              : $atv['image'];
        ?>
        <div class="card <?= htmlspecialchars($cor) ?>">
          <div class="card-text">
            <a href="/games/<?= htmlspecialchars($caminho, ENT_QUOTES, 'UTF-8') ?>?atividade=<?= (int) $atv['id'] ?>">
              <h2><?= htmlspecialchars($atv['nome']) ?></h2>
            </a>
            <p><?= htmlspecialchars($atv['descricao']) ?></p>
          </div>
          <div class="card-image">
            <img src="statics/imgs/<?= htmlspecialchars($imagem, ENT_QUOTES, 'UTF-8') ?>"
                 alt="<?= htmlspecialchars($atv['nome'], ENT_QUOTES) ?>">
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>

<?php require __DIR__ . "/partials/layout_end.php"; ?>
