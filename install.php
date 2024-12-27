<?php
// Vérification des extensions PHP nécessaires
function checkExtensions($requiredExtensions) {
    $results = [];
    foreach ($requiredExtensions as $extension) {
        $results[$extension] = extension_loaded($extension) ? 'ok' : 'ko';
    }
    return $results;
}

// Initialisation des contrôles
$checks = [];

// Vérification des droits d'écriture sur config.php
$configFile = __DIR__ . '/config.php';
$canWriteConfig = is_writable(__DIR__) && (!file_exists($configFile) || is_writable($configFile));
$checks['write'] = $canWriteConfig ? 'ok' : 'ko';

// Vérification des extensions PHP nécessaires
$requiredExtensions = ['pdo_mysql', 'mbstring', 'json', 'ctype'];
$extensionResults = checkExtensions($requiredExtensions);
$checks['Extensions PHP'] = $extensionResults;

// Vérifier si tout est OK
$canProceed = $checks['write'] === 'ok' && !in_array('ko', $extensionResults);

// Traiter le formulaire si tout est OK
if ($canProceed && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = $_POST['db_host'];
    $dbName = $_POST['db_name'];
    $dbUser = $_POST['db_user'];
    $dbPass = $_POST['db_pass'];

    // Ajouter le port par défaut si non précisé
    if (!strpos($dbHost, ':')) {
        $dbHost .= ':3306';
    }

    try {
        // Tester la connexion à MySQL
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Générer le fichier config.php avec les informations
        $configContent = "<?php\n\nreturn [\n" .
                         "    'DB_HOST' => '$dbHost',\n" .
                         "    'DB_NAME' => '$dbName',\n" .
                         "    'DB_USER' => '$dbUser',\n" .
                         "    'DB_PASS' => '$dbPass',\n" .
                         "];\n";
        file_put_contents($configFile, $configContent);

        // Exécuter le script SQL
        $sql = file_get_contents(__DIR__ . '/install/bdd.sql');
        $pdo->exec($sql);

        // Créer l'utilisateur admin
        $passwordHash = password_hash('admin', PASSWORD_BCRYPT);
        $sqlInsertAdmin = "INSERT INTO users (username, password, role_id) 
                           VALUES ('admin', :password, (SELECT id FROM roles WHERE role_name = 'admin'))";
        $stmt = $pdo->prepare($sqlInsertAdmin);
        $stmt->bindParam(':password', $passwordHash);
        $stmt->execute();

        // Rediriger vers la page de connexion
        header("Location: /index.php");
        exit;

    } catch (PDOException $e) {
        $error = "Erreur de connexion : " . $e->getMessage();
    }
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
        .status-icon { font-size: 1.2em; }
    </style>
</head>
<body>
    <h1>Installation de l'outil</h1>
    
    <div>
        <h2>Étapes de contrôle</h2>
        <ul>
            <!-- Vérification des droits d'écriture -->
            <li>
                <span class="status-icon">
                    <?= $checks['write'] === 'ok' ? '✅' : '❌' ?>
                </span>
                <span class="<?= $checks['write'] === 'ok' ? 'status-ok' : 'status-ko' ?>">
                    Droits en écriture
                </span>
            </li>

            <!-- Vérification des extensions PHP -->
            <li>
                <span>Extensions PHP :</span>
                <ul>
                    <?php foreach ($checks['Extensions PHP'] as $extension => $result): ?>
                        <li>
                            <span class="status-icon">
                                <?= $result === 'ok' ? '✅' : '❌' ?>
                            </span>
                            <span class="<?= $result === 'ok' ? 'status-ok' : 'status-ko' ?>">
                                <?= htmlspecialchars($extension) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
        </ul>
    </div>

    <?php if (!$canProceed): ?>
        <p style="color: red;">Veuillez corriger les erreurs ci-dessus et recharger la page pour continuer.</p>
    <?php else: ?>
        <form method="POST" action="">
            <label for="db_host">Hôte MySQL :</label><br>
            <input type="text" id="db_host" name="db_host" value="<?= htmlspecialchars($dbHost ?? '') ?>" placeholder="Ex : localhost ou localhost:3306" required><br><br>

            <label for="db_name">Nom de la base de données :</label><br>
            <input type="text" id="db_name" name="db_name" value="<?= htmlspecialchars($dbName ?? '') ?>" required><br><br>

            <label for="db_user">Utilisateur MySQL :</label><br>
            <input type="text" id="db_user" name="db_user" value="<?= htmlspecialchars($dbUser ?? '') ?>" required><br><br>

            <label for="db_pass">Mot de passe MySQL :</label><br>
            <input type="password" id="db_pass" name="db_pass" value="<?= htmlspecialchars($dbPass ?? '') ?>"><br><br>

            <button type="submit">Installer</button>
        </form>
    <?php endif; ?>
</body>
</html>
