/                     <-- Racine du projet
|-- /releases         <-- Contient les différentes versions sous forme de ZIP
|-- /public           <-- Contient les fichiers accessibles au web (pointé par le serveur)
|   |-- /pages        <-- Contient les pages PHP de votre application
|   |-- /install      <-- Contient les fichiers d'installation
|   |-- index.php     <-- Point d'entrée principal de l'application
|   |-- setup.php     <-- Seul fichier à DL pour installer l'outil
|-- /src              <-- Contient les fichiers source non publics
|   |-- /classes      <-- Classes PHP personnalisées
|   |-- /lib          <-- Bibliothèques personnalisées ou tierces (non gérées par Composer)
|-- /vendor           <-- Géré par Composer (ne pas inclure dans le dépôt)
|-- composer.json     <-- Configuration des dépendances Composer
|-- composer.lock     <-- Versions exactes des dépendances
|-- README.md         <-- Documentation du projet
|-- .gitignore        <-- Définit les fichiers à ignorer dans le dépôt
