<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $turma = $_GET['turma'] ?? '';
    $atividade_id = $_GET['atividade_id'] ?? '';

    // Buscar estatísticas gerais
    $query = "
        SELECT 
            COUNT(DISTINCT al.id) as total_alunos,
            COUNT(DISTINCT a.id) as total_atividades,
            SUM(a.total_questoes) as total_questoes
        FROM alunos al
        CROSS JOIN atividades a
        WHERE al.turma = :turma
    ";
    
    if ($atividade_id) {
        $query .= " AND a.id = :atividade_id";
    }

    $stmt = $db->prepare($query);
    $stmt->bindParam(":turma", $turma);
    
    if ($atividade_id) {
        $stmt->bindParam(":atividade_id", $atividade_id);
    }
    
    $stmt->execute();
    $estatisticas = $stmt->fetch(PDO::FETCH_ASSOC);

    // Buscar dados das atividades
    $query = "
        SELECT 
            a.id,
            a.titulo,
            a.descricao,
            a.total_questoes,
            COUNT(DISTINCT aa.aluno_id) as alunos_ativos,
            SUM(CASE WHEN aa.nota >= 7 THEN 1 ELSE 0 END) as acertos,
            SUM(CASE WHEN aa.nota < 7 THEN 1 ELSE 0 END) as erros
        FROM atividades a
        LEFT JOIN aspectos_academicos aa ON a.id = aa.atividade_id
        LEFT JOIN alunos al ON aa.aluno_id = al.id
        WHERE al.turma = :turma
    ";
    
    if ($atividade_id) {
        $query .= " AND a.id = :atividade_id";
    }
    
    $query .= " GROUP BY a.id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(":turma", $turma);
    
    if ($atividade_id) {
        $stmt->bindParam(":atividade_id", $atividade_id);
    }
    
    $stmt->execute();
    $atividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Preparar resposta
    $response = [
        'totalAlunos' => (int)$estatisticas['total_alunos'],
        'atividades' => $atividades
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro no servidor: ' . $e->getMessage()]);
}
?>