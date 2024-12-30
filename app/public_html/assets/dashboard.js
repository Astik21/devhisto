document.addEventListener('DOMContentLoaded', function () {
    const links = document.querySelectorAll('nav a');
    const mainContent = document.getElementById('main-content');

    links.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.getAttribute('href');

            // Charger la nouvelle page dans le conteneur <main>
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur de chargement de la page');
                    }
                    return response.text();
                })
                .then(html => {
                    mainContent.innerHTML = html;
                    history.pushState(null, '', url);
                })
                .catch(error => {
                    console.error(error);
                    mainContent.innerHTML = '<p>Erreur lors du chargement de la page.</p>';
                });
        });
    });

    // Gérer la navigation via le bouton retour
    window.addEventListener('popstate', function () {
        fetch(location.href)
            .then(response => response.text())
            .then(html => {
                mainContent.innerHTML = html;
            });
    });
});
