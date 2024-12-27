<?php
// Initialisation des étapes de validation
$validationSteps = [
    'Droits en écriture (config.php)' => 'pending',
    'Extensions PHP (pdo_mysql)' => 'pending',
    'Extensions PHP (mbstring)' => 'pending',
    'Extensions PHP (json)' => 'pending',
    'Extensions PHP (ctype)' => 'pending',
    'Validation du serveur MySQL' => 'pending',
    'Validation des identifiants MySQL' => 'pending',
    'Enregistrement de config.php' => 'pending',
    'Création des tables SQL' => 'pending',
    'Création de l\'utilisateur admin' => 'pending'
];

// Initialisation de $canProceed par défaut
$canProceed = true;

// Vérification des droits d'écriture sur config.php
$configFile = __DIR__ . '/config.php';
if (is_writable(__DIR__) && (!file_exists($configFile) || is_writable($configFile))) {
    $validationSteps['Droits en écriture (config.php)'] = 'ok';
} else {
    $validationSteps['Droits en écriture (config.php)'] = 'ko';
}

// Vérification des extensions PHP nécessaires
$requiredExtensions = ['pdo_mysql', 'mbstring', 'json', 'ctype'];
foreach ($requiredExtensions as $extension) {
    $validationSteps["Extensions PHP ($extension)"] = extension_loaded($extension) ? 'ok' : 'ko';
}

// Vérifier si tout est OK pour afficher le formulaire
$canProceed = !in_array('ko', $validationSteps);

// Génération du fichier config.php
function generateConfigFile($dbHost, $dbUser, $dbPassword, $dbName, $dbPort = null) {
    $portLine = $dbPort ? "define('DB_PORT', '{$dbPort}');\n" : "";
    return <<<PHP
<?php
// Fichier généré automatiquement par install.php
define('DB_HOST', '{$dbHost}');
define('DB_USER', '{$dbUser}');
define('DB_PASSWORD', '{$dbPassword}');
define('DB_NAME', '{$dbName}');
{$portLine}// Autres configurations possibles
define('APP_DEBUG', false); // Activez true pour le mode débogage
PHP;
}

// Enregistrement sécurisé de config.php
function saveConfigFile($filePath, $content) {
    if (file_exists($filePath)) {
        return 'exists'; // Ne pas écraser un fichier existant
    }
    return file_put_contents($filePath, $content) !== false ? 'written' : 'error';
}

// Fonction pour afficher les étapes de validation
function displayValidationSteps($steps) {
    foreach ($steps as $step => $status) {
        echo "<li><span class='status-icon'>" .
             ($status === 'ok' ? '✅' : ($status === 'ko' ? '❌' : '⏳')) .
             "</span> <span class='status-$status'>" .
             htmlspecialchars($step) .
             "</span></li>";
    }
    ob_flush();
    flush();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation</title>
    <style>
        .status-ok { color: green; font-weight: bold; }
        .status-ko { color: red; font-weight: bold; }
        .status-pending { color: orange; font-weight: bold; }
        .status-icon { font-size: 1.2em; }
    </style>
</head>
<body>
    <h1>Installation de l'outil</h1>

    <div>
        <h2>Étapes de validation</h2>
        <ul>
            <?php displayValidationSteps($validationSteps); ?>
        </ul>
    </div>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <?php
        $dbHost = $_POST['db_host'];
        $dbName = $_POST['db_name'];
        $dbUser = $_POST['db_user'];
        $dbPass = $_POST['db_pass'];
        $dbPort = !empty($_POST['db_port']) ? $_POST['db_port'] : null;

        try {
            // Étape 1 : Validation du serveur MySQL
            $pdo = new PDO("mysql:host=$dbHost" . ($dbPort ? ";port=$dbPort" : ""), $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $validationSteps['Validation du serveur MySQL'] = 'ok';
            displayValidationSteps($validationSteps);

            // Étape 2 : Validation des identifiants MySQL
            $pdo->exec("USE $dbName");
            $validationSteps['Validation des identifiants MySQL'] = 'ok';
            displayValidationSteps($validationSteps);

            // Étape 3 : Enregistrement de config.php
            $configContent = generateConfigFile($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
            $configSaveStatus = saveConfigFile($configFile, $configContent);

            if ($configSaveStatus === 'written' || $configSaveStatus === 'exists') {
                $validationSteps['Enregistrement de config.php'] = 'ok';
            } else {
                $validationSteps['Enregistrement de config.php'] = 'ko';
                throw new Exception("Impossible d'écrire dans config.php.");
            }
            displayValidationSteps($validationSteps);

            // Étape 4 : Création des tables SQL
            $sql = file_get_contents(__DIR__ . '/install/bdd.sql');
            $pdo->exec($sql);
            $validationSteps['Création des tables SQL'] = 'ok';
            displayValidationSteps($validationSteps);

            // Étape 5 : Création de l'utilisateur admin
            $passwordHash = password_hash('admin', PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role_id) 
                                   VALUES ('admin', :password, 
                                   (SELECT id FROM roles WHERE role_name = 'admin'))");
            $stmt->bindParam(':password', $passwordHash);
            $stmt->execute();
            $validationSteps['Création de l\'utilisateur admin'] = 'ok';
            displayValidationSteps($validationSteps);

            echo "<p style='color: green;'>Installation réussie !</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>
    <?php else: ?>
        <form method="POST" action="">
            <label for="db_host">Hôte MySQL :</label><br>
            <input type="text" id="db_host" name="db_host" placeholder="Ex : localhost" required><br><br>

            <label for="db_port">Port MySQL (optionnel) :</label><br>
            <input type="text" id="db_port" name="db_port" placeholder="Ex : 3306"><br><br>

            <label for="db_name">Nom de la base de données :</label><br>
            <input type="text" id="db_name" name="db_name" required><br><br>

            <label for="db_user">Utilisateur MySQL :</label><br>
            <input type="text" id="db_user" name="db_user" required><br><br>

            <label for="db_pass">Mot de passe MySQL :</label><br>
            <input type="password" id="db_pass" name="db_pass"><br><br>

            <button type="submit">Valider</button>
        </form>
    <?php endif; ?>
</body>
</html>
