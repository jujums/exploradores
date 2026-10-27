document.addEventListener('DOMContentLoaded', function() {
    // Adiciona funcionalidade básica de interação
    const sections = document.querySelectorAll('section');
    
    sections.forEach(section => {
        section.addEventListener('click', function() {
            this.style.backgroundColor = '#f9f9f9';
            setTimeout(() => {
                this.style.backgroundColor = '';
            }, 300);
        });
    });
    
    // Simula um progresso (apenas visual)
    const percentageElement = document.querySelector('.percentage');
    let progress = 2;
    
    // Apenas para demonstração - incrementa o progresso a cada 5 segundos
    setInterval(() => {
        if (progress < 100) {
            progress += 2;
            percentageElement.textContent = progress + '%';
        }
    }, 5000);
    
    console.log('Página de configurações carregada com sucesso!');
});