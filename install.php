<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $dbHost = $_POST['db_host'];
    $dbName = $_POST['db_name'];
    $dbUser = $_POST['db_user'];
    $dbPass = $_POST['db_pass'];

    // Ajouter le port par défaut (3306) si non précisé dans le nom de serveur
    if (!strpos($dbHost, ':')) {
        $dbHost .= ':3306';
    }

    // Vérifier si le fichier .env est accessible en écriture
    $envFile = __DIR__ . '/.env';
    if (!is_writable(__DIR__) || (file_exists($envFile) && !is_writable($envFile))) {
        $error = "Erreur : le fichier .env n'est pas accessible en écriture. Veuillez vérifier les permissions.";
    } else {
        // Tester la connexion à la base de données
        try {
            $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Connexion réussie, enregistrer les informations dans le fichier .env
            $envContent = "DB_HOST=$dbHost\nDB_NAME=$dbName\nDB_USER=$dbUser\nDB_PASS=$dbPass\n";
            file_put_contents($envFile, $envContent);

            // Charger le fichier SQL pour créer la base de données
            $sql = file_get_contents(__DIR__ . '/install/bdd.sql');
            $pdo->exec($sql);

            // Créer un utilisateur admin
            $passwordHash = password_hash('admin', PASSWORD_BCRYPT);
            $sqlInsertAdmin = "INSERT INTO users (username, password, role_id) 
                               VALUES ('admin', :password, (SELECT id FROM roles WHERE role_name = 'admin'))";
            $stmt = $pdo->prepare($sqlInsertAdmin);
            $stmt->bindParam(':password', $passwordHash);
            $stmt->execute();

            // Rediriger vers la page d'accueil
            header("Location: /index.php");
            exit;

        } catch (PDOException $e) {
            $error = "Erreur de connexion : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation</title>
</head>
<body>
    <h1>Installation de l'outil</h1>
    <?php if (!empty($error)) : ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
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
</body>
</html>
