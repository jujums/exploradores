<?php
/**
 * Front Controller — Exploradores das Palavras
 * Ponto de entrada único. Mapeia slugs amigáveis para os arquivos PHP reais.
 */

define('APP_BASE', '');  // VirtualHost raiz — sem prefixo de subpasta

// ── Tabela de rotas ────────────────────────────────────────────────────────
$routes = [
    // Telas públicas (sem layout_start)
    ''                        => 'inicial.php',
    'login'                   => 'login.php',
    'sair'                    => 'logout.php',

    // Área do aluno
    'inicio'                  => 'home.php',
    'atividades'              => 'ativA.php',
    'progresso'               => 'progressoA.php',
    'sobre'                   => 'sobreA.php',
    'configuracoes'           => 'configuracaoA.php',

    // Área do professor
    'admin'                   => 'admin/home.php',
    'admin/turmas'             => 'admin/turmasView.php',
    'admin/turmas/adicionar'   => 'admin/turmasAdd.php',
    'admin/turmas/editar'      => 'admin/turmasAdd.php',
    'admin/alunos'            => 'admin/alunosView.php',
    'admin/alunos/adicionar'  => 'admin/alunosAdd.php',
    'admin/alunos/editar'     => 'admin/alunosAdd.php',
    'admin/atividades'             => 'admin/atividadesTurma.php',
    'admin/atividades/vincular'    => 'admin/atividadesAdd.php',
    'admin/atividades/editar'      => 'admin/atividadesAdd.php',
    'admin/desempenho'         => 'admin/desempenhoTurma.php',
    'admin/graficos'           => 'admin/graficosAdmin.php',
    'admin/professores'       => 'admin/cadastroProf.php',

    // Jogos
    'jogos/caca-palavras'     => 'games/caca_palavra.php',
    'jogos/alfabeto'          => 'games/Alfabeto.php',
    'jogos/familia-silabica'  => 'games/familia_silabica.php',
    'jogos/ligar-imagem'      => 'games/ligar_imagem.php',
    'jogos/historia'          => 'games/historia.php',
    'jogos/assimilar-som'     => 'games/assimilar_som.php',
    'jogos/forca'             => 'games/forca.php',
    'jogos/forme-palavras'    => 'games/forme_palavras.php',
];

// ── Resolução do slug ──────────────────────────────────────────────────────
$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$slug = trim(str_replace(APP_BASE, '', $uri), '/');

$file = $routes[$slug] ?? null;

if ($file === null || !file_exists(__DIR__ . '/' . $file)) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>404</title></head>'
       . '<body><h1>404 — Página não encontrada</h1>'
       . '<p><a href="' . APP_BASE . '/">Voltar ao início</a></p></body></html>';
    exit;
}

// $basePath absoluto garante que CSS/JS/imagens funcionem em qualquer profundidade de URL
$basePath = APP_BASE . '/';

include __DIR__ . '/' . $file;
