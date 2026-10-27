        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script>
    const adminThemeToggle = document.getElementById("adminThemeToggle");
    const adminThemeIcon = document.getElementById("adminThemeIcon");
    const adminThemeLabel = document.getElementById("adminThemeLabel");

    function setAdminTheme(isDarkMode) {
        document.body.classList.toggle("dark-mode", isDarkMode);
        adminThemeToggle.setAttribute("aria-pressed", String(isDarkMode));
        adminThemeToggle.setAttribute("aria-label", isDarkMode ? "Ativar modo claro" : "Ativar modo escuro");
        adminThemeToggle.title = isDarkMode ? "Ativar modo claro" : "Ativar modo escuro";
        adminThemeIcon.textContent = isDarkMode ? "☀" : "☾";
        adminThemeLabel.textContent = isDarkMode ? "Modo claro" : "Modo escuro";
    }

    setAdminTheme(localStorage.getItem("exploradores-theme") === "dark");
    adminThemeToggle.addEventListener("click", () => {
        const isDarkMode = !document.body.classList.contains("dark-mode");
        localStorage.setItem("exploradores-theme", isDarkMode ? "dark" : "light");
        setAdminTheme(isDarkMode);
        if (window.appDebug) {
            window.appDebug.recarregarDados();
        }
    });
</script>
<div vw class="enabled">
    <div vw-access-button class="active"></div>
    <div vw-plugin-wrapper>
        <div class="vw-plugin-top-wrapper"></div>
    </div>
</div>
<script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
<script>
    new window.VLibras.Widget("https://vlibras.gov.br/app");
</script>
</body>
</html>
