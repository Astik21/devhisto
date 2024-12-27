
<?php
// Entry point for the installation process
session_start();

// Redirect AJAX requests to the backend handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/install/backend.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation</title>
    <link rel="stylesheet" href="install/install.css">
    <script src="install/install.js" defer></script>
</head>
<body>
    <h1>Installation de l'outil</h1>
    <div id="validation-container">
        <ul id="validation-steps">
            <li data-step="check-write">Droits en écriture (config.php)</li>
            <li data-step="check-extension-pdo">Extension PHP: pdo_mysql</li>
            <li data-step="check-extension-mbstring">Extension PHP: mbstring</li>
            <li data-step="check-extension-json">Extension PHP: json</li>
            <li data-step="check-extension-ctype">Extension PHP: ctype</li>
            <li data-step="check-mysql-server">Connexion au serveur MySQL</li>
            <li data-step="check-mysql-credentials">Validation des identifiants MySQL</li>
            <li data-step="create-config">Enregistrement du fichier config.php</li>
            <li data-step="create-tables">Création des tables SQL</li>
            <li data-step="create-admin">Création de l'utilisateur admin</li>
        </ul>
    </div>
    <div id="form-container">
        <form id="installation-form">
            <label for="db_host">Hôte MySQL :</label><br>
            <input type="text" id="db_host" name="db_host" placeholder="localhost" required><br><br>
            <label for="db_port">Port MySQL :</label><br>
            <input type="text" id="db_port" name="db_port" placeholder="3306"><br><br>
            <label for="db_name">Nom de la base de données :</label><br>
            <input type="text" id="db_name" name="db_name" required><br><br>
            <label for="db_user">Utilisateur MySQL :</label><br>
            <input type="text" id="db_user" name="db_user" required><br><br>
            <label for="db_pass">Mot de passe MySQL :</label><br>
            <input type="password" id="db_pass" name="db_pass"><br><br>
            <button type="button" id="start-installation">Lancer l'installation</button>
        </form>
    </div>
</body>
</html>
