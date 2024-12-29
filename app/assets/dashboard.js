document.addEventListener('DOMContentLoaded', () => {
    const mainContent = document.getElementById('main-content');

    // Charger une page dynamiquement via AJAX
    async function loadPage(page) {
        mainContent.innerHTML = '<p>Chargement en cours...</p>';
        try {
            const response = await fetch(`index.php?page=${page}`);
            if (!response.ok) throw new Error('Erreur lors du chargement de la page.');
            const html = await response.text();
            const parser = new DOMParser();
            const newContent = parser.parseFromString(html, 'text/html').querySelector('main').innerHTML;
            mainContent.innerHTML = newContent;
        } catch (error) {
            mainContent.innerHTML = `<p class="error">${error.message}</p>`;
        }
    }

    // Gestion du clic sur les liens
    document.querySelectorAll('nav ul li a').forEach(link => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            const page = link.getAttribute('href').split('page=')[1];
            if (page) {
                loadPage(page);
                history.pushState(null, '', `index.php?page=${page}`);
            }
        });
    });

    // Gérer les retours dans l'historique du navigateur
    window.addEventListener('popstate', () => {
        const page = new URL(window.location).searchParams.get('page') || 'home';
        loadPage(page);
    });

    // Charger la page d'accueil par défaut
    const initialPage = new URL(window.location).searchParams.get('page') || 'home';
    loadPage(initialPage);
});
