// Sistema de controle de atividades concluídas
class GerenciadorAtividades {
    constructor() {
        this.atividades = [
            'caca_palavras',
            'complete_alfabeto', 
            'familia_silabica',
            'ligar_imagem',
            'historia',
            'assimilar_som'
        ];
        this.carregarStatus();
        this.atualizarInterface();
    }

    carregarStatus() {
        this.atividades.forEach(atividade => {
            const status = localStorage.getItem(`atividade_${atividade}`);
            if (status === 'concluida') {
                this.marcarComoConcluida(atividade, false);
            }
        });
    }

    marcarComoConcluida(nomeAtividade, salvar = true) {
        const card = document.querySelector(`[data-atividade="${nomeAtividade}"]`);
        if (card) {
            card.classList.add('concluida');
            
            const badge = card.querySelector('.status-badge');
            if (badge) {
                badge.textContent = 'Concluído';
                badge.classList.remove('pendente');
                badge.classList.add('concluido');
            }

            if (salvar) {
                localStorage.setItem(`atividade_${nomeAtividade}`, 'concluida');
            }
        }
        
        // Atualizar também no quadro de progresso
        const itemQuadro = document.querySelector(`.atividade-item[data-atividade="${nomeAtividade}"]`);
        if (itemQuadro) {
            const statusQuadro = itemQuadro.querySelector('.atividade-status');
            statusQuadro.textContent = 'Concluído';
            statusQuadro.classList.remove('pendente');
            statusQuadro.classList.add('concluido');
        }
        
        this.atualizarProgresso();
    }

    atualizarProgresso() {
        const totalAtividades = this.atividades.length;
        const concluidas = this.atividades.filter(atividade => 
            localStorage.getItem(`atividade_${atividade}`) === 'concluida'
        ).length;

        const porcentagem = (concluidas / totalAtividades) * 100;
        
        // Atualizar contadores
        document.getElementById('contadorConcluidas').textContent = concluidas;
        document.getElementById('contadorTotal').textContent = totalAtividades;
        
        // Atualizar barra de progresso
        const barra = document.getElementById('barraProgresso');
        if (barra) {
            barra.style.width = `${porcentagem}%`;
        }
    }

    atualizarInterface() {
        // Verificar status de cada atividade
        this.atividades.forEach(atividade => {
            if (localStorage.getItem(`atividade_${atividade}`) === 'concluida') {
                this.marcarComoConcluida(atividade, false);
            }
        });
        this.atualizarProgresso();
    }

    limparProgresso() {
        if (confirm('Deseja limpar todo o progresso das atividades?')) {
            this.atividades.forEach(atividade => {
                localStorage.removeItem(`atividade_${atividade}`);
                
                // Remover classe concluida dos cards
                const card = document.querySelector(`[data-atividade="${atividade}"]`);
                if (card) {
                    card.classList.remove('concluida');
                    
                    const badge = card.querySelector('.status-badge');
                    if (badge) {
                        badge.textContent = 'Pendente';
                        badge.classList.remove('concluido');
                        badge.classList.add('pendente');
                    }
                }
                
                // Atualizar também no quadro de progresso
                const itemQuadro = document.querySelector(`.atividade-item[data-atividade="${atividade}"]`);
                if (itemQuadro) {
                    const statusQuadro = itemQuadro.querySelector('.atividade-status');
                    statusQuadro.textContent = 'Pendente';
                    statusQuadro.classList.remove('concluido');
                    statusQuadro.classList.add('pendente');
                }
            });
            
            this.atualizarProgresso();
        }
    }
}

// Inicializar quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    window.gerenciadorAtividades = new GerenciadorAtividades();
    
    // Adicionar evento ao botão de limpar progresso
    document.getElementById('limparProgresso').addEventListener('click', function() {
        window.gerenciadorAtividades.limparProgresso();
    });
    
    // Adicionar funcionalidade de clique nas atividades
    document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('click', function() {
            const atividade = this.getAttribute('data-atividade');
            
            if(this.classList.contains('concluida')) {
                // Se já está concluída, remover o status
                localStorage.removeItem(`atividade_${atividade}`);
                this.classList.remove('concluida');
                this.querySelector('.status-badge').textContent = 'Pendente';
                this.querySelector('.status-badge').className = 'status-badge pendente';
                
                // Atualizar também no quadro de progresso
                const itemQuadro = document.querySelector(`.atividade-item[data-atividade="${atividade}"]`);
                if (itemQuadro) {
                    const statusQuadro = itemQuadro.querySelector('.atividade-status');
                    statusQuadro.textContent = 'Pendente';
                    statusQuadro.classList.remove('concluido');
                    statusQuadro.classList.add('pendente');
                }
            } else {
                // Se não está concluída, marcar como concluída
                window.gerenciadorAtividades.marcarComoConcluida(atividade);
            }
        });
    });
});