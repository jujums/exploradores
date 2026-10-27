<?php
$pageTitle = "Inicio";
$pageBodyClass = "page-homea";
$layoutRole = "aluno";
$brandLogoFile = "arco.png";
$brandLogoAlt = "Logo Exploradores das Palavras";
require __DIR__ . "/partials/layout_start.php";
?>
        <section class="inicio">
            <div class="text">
                <h2>Exploradores das Palavras</h2>
                <p> A facilitação do processo de alfabetização e letramento infantil, utilizando atividades envolventes para maior interesse do aluno.</p>
            </div>
            <div class="arco">
                <img src="statics/imgs/arco.png" alt="Logo Exploradores das Palavras">
            </div>
        </section>
        <hr>
        <section class="fim">
            <div class="logos">
            <img src="statics/imgs/escola.png" class="escola">
            <img src="statics/imgs/tecnico.png" class="tec">
            </div>
           
        </section>
<?php require __DIR__ . "/partials/layout_end.php"; ?>
