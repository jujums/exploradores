<?php
$pageTitle = "Atividades em Atraso";
$pageBodyClass = "page-conteudo-atraso";
$layoutRole = "professor";
require __DIR__ . "/partials/layout_start.php";
?>

<div class="container">
        <!-- Quadro branco de progresso (lado esquerdo) -->
        <div class="quadro-progresso">
            <h2>Progresso das Atividades</h2>
            <div class="progresso-info">
                <span id="contadorConcluidas">0</span> de <span id="contadorTotal">6</span> atividades concluídas
            </div>
            <div class="barra-progresso">
                <div class="progresso-preenchimento" id="barraProgresso"></div>
            </div>
            <button class="limpar-progresso" id="limparProgresso">Limpar Progresso</button>
            
            <div class="lista-atividades">
                <div class="atividade-item" data-atividade="caca_palavras">
                    <div class="atividade-cor" style="background-color: #ff004f;"></div>
                    <div class="atividade-nome">Caça Palavras</div>
                    <div class="atividade-status pendente">Pendente</div>
                </div>
                <div class="atividade-item" data-atividade="complete_alfabeto">
                    <div class="atividade-cor" style="background-color: #ff5038;"></div>
                    <div class="atividade-nome">Complete o Alfabeto</div>
                    <div class="atividade-status pendente">Pendente</div>
                </div>
                <div class="atividade-item" data-atividade="familia_silabica">
                    <div class="atividade-cor" style="background-color: #febe16;"></div>
                    <div class="atividade-nome">Família Silábica</div>
                    <div class="atividade-status pendente">Pendente</div>
                </div>
                <div class="atividade-item" data-atividade="ligar_imagem">
                    <div class="atividade-cor" style="background-color: #09d0a8;"></div>
                    <div class="atividade-nome">Ligue a Palavra à Imagem</div>
                    <div class="atividade-status pendente">Pendente</div>
                </div>
                <div class="atividade-item" data-atividade="historia">
                    <div class="atividade-cor" style="background-color: #0b98bb;"></div>
                    <div class="atividade-nome">História</div>
                    <div class="atividade-status pendente">Pendente</div>
                </div>
                <div class="atividade-item" data-atividade="assimilar_som">
                    <div class="atividade-cor" style="background-color: #af68da;"></div>
                    <div class="atividade-nome">Assimilar ao som</div>
                    <div class="atividade-status pendente">Pendente</div>
                </div>
            </div>
        </div>

        <!-- Grid de atividades (lado direito) -->
        <div class="grid"> 
            <div class="card red" data-atividade="caca_palavras">
                <div class="card-text">
                    <a href="caca_palavra.php"><h2>Caça Palavras</h2></a>
                    <p>Estimula o reconhecimento de letras e palavras, ampliando o vocabulário e fortalecendo o aprendizado.</p>
                    <div class="status-atividade">
                        <span class="status-badge pendente">Pendente</span>
                    </div>
                </div>
                <div class="card-image">
                    <img src="statics/imgs/lupa.png" alt="Caça Palavras">
                </div>
            </div>

            <div class="card orange" data-atividade="complete_alfabeto">
                <div class="card-text">
                    <a href="Alfabeto.php"><h2>Complete o Alfabeto</h2></a>
                    <p>Ajuda no reconhecimento e no materializado dos textos, além de sua ordem correta.</p>
                    <div class="status-atividade">
                        <span class="status-badge pendente">Pendente</span>
                    </div>
                </div>
                <div class="card-image">
                    <img src="statics/imgs/alfabeto.png" alt="Complete o Alfabeto">
                </div>
            </div>

            <div class="card yellow" data-atividade="familia_silabica">
                <div class="card-text">
                    <a href="familia_silabica.php"><h2>Família Silábica</h2></a>
                    <p>Completando as palavras com as famílias silábicas a partir de imagens, auxilia na alfabetização.</p>
                    <div class="status-atividade">
                        <span class="status-badge pendente">Pendente</span>
                    </div>
                </div>
                <div class="card-image">
                    <img src="statics/imgs/cerebro.png" alt="Família Silábica">
                </div>
            </div>

            <div class="card green" data-atividade="ligar_imagem">
                <div class="card-text">
                    <a href="ligar_imagem.php"><h2>Ligue a Palavra à Imagem</h2></a>
                    <p>Associar palavras escritas a imagens permite à criança reconhecer visualmente o vocabulário.</p>
                    <div class="status-atividade">
                        <span class="status-badge pendente">Pendente</span>
                    </div>
                </div>
                <div class="card-image">
                    <img src="statics/imgs/ligar.png" alt="Ligue a Palavra à Imagem">
                </div>
            </div>

            <div class="card blue" data-atividade="historia">
                <div class="card-text">
                    <a href="/games/historia.php"><h2>História</h2></a>
                    <p>A história interativa estimula a leitura e a compreensão de forma divertida.</p>
                    <div class="status-atividade">
                        <span class="status-badge pendente">Pendente</span>
                    </div>
                </div>
                <div class="card-image">
                    <img src="statics/imgs/historias.png" alt="História">
                </div>
            </div>

            <div class="card purple" data-atividade="assimilar_som">
                <div class="card-text">
                    <a href="assimilar_som.php"><h2>Assimilar ao som</h2></a>
                    <p>Associar imagens aos sons ajuda a criança a entender melhor as letras e seus fonemas.</p>
                    <div class="status-atividade">
                        <span class="status-badge pendente">Pendente</span>
                    </div>
                </div>
                <div class="card-image">
                    <img src="statics/imgs/som.png" alt="Assimilar ao som">
                </div>
            </div>
        </div>
    </div>

    <script src="statics/js/conteudo_atraso.js"></script>

<?php require __DIR__ . "/partials/layout_end.php"; ?>
