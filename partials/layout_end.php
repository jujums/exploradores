    </div>
  </main>
  <script>
    const themeToggle = document.getElementById("theme-toggle");
    const themeToggleIcon = document.getElementById("theme-toggle-icon");
    const themeToggleLabel = document.getElementById("theme-toggle-label");
    const savedTheme = localStorage.getItem("exploradores-theme");
    const darkModeEnabled = savedTheme === "dark";

    document.body.classList.toggle("dark-mode", darkModeEnabled);
    if (themeToggle) {
      const updateThemeButton = (isDarkMode) => {
        themeToggle.setAttribute("aria-pressed", String(isDarkMode));
        themeToggle.setAttribute("aria-label", isDarkMode ? "Ativar modo claro" : "Ativar modo escuro");
        themeToggle.title = isDarkMode ? "Ativar modo claro" : "Ativar modo escuro";
        themeToggleIcon.textContent = isDarkMode ? "☀" : "☾";
        themeToggleLabel.textContent = isDarkMode ? "Modo claro" : "Modo escuro";
      };

      updateThemeButton(darkModeEnabled);
      themeToggle.addEventListener("click", () => {
        const isDarkMode = document.body.classList.toggle("dark-mode");
        localStorage.setItem("exploradores-theme", isDarkMode ? "dark" : "light");
        updateThemeButton(isDarkMode);
      });
    }
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
