<?php
session_start();

$validationSteps = [
    'Droits en écriture (config.php)' => 'pending',
    'Extensions PHP (pdo_mysql)' => 'pending',
    'Extensions PHP (mbstring)' => 'pending',
    'Extensions PHP (json)' => 'pending',
    'Extensions PHP (ctype)' => 'pending',
    'Connexion au serveur SQL' => 'pending',
    'Validation des identifiants SQL' => 'pending',
    'Enregistrement de config.php' => 'pending',
    'Création des tables SQL' => 'pending',
    'Création de l\'utilisateur admin' => 'pending',
    'Supprimer le répertoire install' => 'pending'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
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
        <h2>Étapes de validation</h2>
        <ul id="validation-steps">
            <?php foreach ($validationSteps as $step => $status): ?>
                <li data-step="<?= htmlspecialchars($step) ?>" class="status-pending"><?= $step ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div id="form-container" style="display:none;">
        <h2>Configurer la base de données</h2>
        <form id="installation-form">
            <label for="db_host">Hôte MySQL :</label>
            <input type="text" id="db_host" name="db_host" placeholder="localhost" required><br>
            <label for="db_port">Port MySQL :</label>
            <input type="text" id="db_port" name="db_port" placeholder="3306"><br>
            <label for="db_name">Nom de la base de données :</label>
            <input type="text" id="db_name" name="db_name" required><br>
            <label for="db_user">Utilisateur MySQL :</label>
            <input type="text" id="db_user" name="db_user" required><br>
            <label for="db_pass">Mot de passe MySQL :</label>
            <input type="password" id="db_pass" name="db_pass"><br>
            <button type="button" id="submit-sql">Tester la connexion</button>
        </form>
    </div>
    <div id="next-step" style="display:none; margin-top: 20px;"></div>
</body>
</html>
