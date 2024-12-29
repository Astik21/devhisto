<?php
session_start();
require_once __DIR__ . '/install/steps.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation</title>
    <script>
        const stepDisplayNames = <?= json_encode(array_combine(array_keys($stepDisplayNames), array_column($stepDisplayNames, 'label'))) ?>;
    </script>
    <link rel="stylesheet" href="/install/install.css">
    <script src="/install/install.js" defer></script>

</head>
<body>
    <h1>Installation de DevHisto</h1>

    <!-- Formulaire SQL (masqué par défaut) -->
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

    <!-- Liste des étapes -->
    <div id="validation-container">
        <h2>Étapes de validation</h2>
        <ul id="validation-steps">
            <?php foreach ($stepDisplayNames as $step => $data): ?>
                <li data-step="<?= htmlspecialchars($step) ?>" 
                    class="status-pending" 
                    <?= !empty($data['hidden']) ? 'style="display: none;"' : '' ?>>
                    <?= htmlspecialchars($data['label']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Étape suivante -->
    <div id="next-step" style="display:none; margin-top: 20px;"></div>
</body>
</html>
