/**
 * game_api.js — Helper compartilhado para salvar jogadas no banco de dados.
 * Carregado por todos os jogos ANTES do script específico do jogo.
 *
 * Expõe globalmente:
 *   SESSAO_JOGO  — UUID da sessão corrente (gerado ao carregar a página)
 *   salvarJogada(dados) — envia POST assíncrono ao endpoint (falha silenciosa)
 */

// Gera um UUID v4 usando a API nativa quando disponível, com fallback.
function gerarUUID() {
    if (typeof crypto !== 'undefined' && crypto.randomUUID) {
        return crypto.randomUUID();
    }
    // Fallback manual compatível com browsers antigos
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        const r = (Math.random() * 16) | 0;
        const v = c === 'x' ? r : (r & 0x3) | 0x8;
        return v.toString(16);
    });
}

// UUID único para esta sessão de jogo (renovado a cada carregamento de página)
const SESSAO_JOGO = gerarUUID();

/**
 * Salva uma jogada no banco de dados.
 * @param {Object} dados
 *   @param {string} dados.jogo_slug        — identificador do jogo ex: 'alfabeto'
 *   @param {string} dados.movimento_tipo   — ex: 'digitar_letra', 'drop_silaba'
 *   @param {boolean} dados.correta         — se o movimento foi correto
 *   @param {Object} [dados.movimento]      — detalhes do movimento (JSON livre)
 *   @param {string} [dados.fase]           — fase/nível dentro do jogo
 *   @param {number} [dados.numero_tentativa] — número da tentativa (default 1)
 *   @param {number} [dados.pontuacao_delta] — pontos ganhos/perdidos
 *   @param {number} [dados.tempo_ms]       — tempo em ms para realizar o movimento
 */
async function salvarJogada(dados) {
    try {
        await fetch('../api/salvar_jogada.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                sessao_jogo: SESSAO_JOGO,
                numero_tentativa: 1,
                id_atividade: (typeof ID_ATIVIDADE !== 'undefined' && ID_ATIVIDADE > 0) ? ID_ATIVIDADE : null,
                ...dados
            })
        });
    } catch (_) {
        // Falha silenciosa — não interfere na experiência do jogo
    }
}
