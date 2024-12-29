<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Charger la configuration
if (!file_exists('config.php')) {
    die('Erreur : Le fichier de configuration est manquant.');
}
require_once __DIR__ . '/config.php';

// Connexion à la base de données
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        ]
    );
} catch (PDOException $e) {
    die('Erreur : Impossible de se connecter à la base de données. ' . $e->getMessage());
}

// Déterminer la page à charger
$page = $_GET['page'] ?? 'home';

// Vérifier si le fichier correspondant existe
$pageFile = __DIR__ . "/pages/{$page}.php";
if (!file_exists($pageFile)) {
    $page = '404';
    $pageFile = __DIR__ . "/pages/404.php";
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - DevHisto</title>
    <link rel="stylesheet" href="/assets/styles.css">
    <script src="/assets/dashboard.js" defer></script>
</head>
<body>
    <header>
        <h1>Bienvenue sur DevHisto</h1>
        <nav>
            <ul>
                <li><a href="index.php?page=home">Accueil</a></li>
                <li><a href="index.php?page=add">Ajouter un devis</a></li>
                <li><a href="index.php?page=view">Consulter les devis</a></li>
                <li><a href="index.php?page=stats">Statistiques</a></li>
                <li><a href="login.php?logout=true">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <?php
        // Répertoire contenant les fichiers autorisés
        $allowedDirectory = __DIR__ . '/pages';

        // Nettoyage du chemin fourni
        $pageFile = realpath($allowedDirectory . '/' . basename($pageFile));

        // Vérification : est-ce que le fichier existe et est dans le répertoire autorisé ?
        if ($pageFile && strpos($pageFile, $allowedDirectory) === 0 && file_exists($pageFile)) {
            include $pageFile;
        } else {
            // Option : afficher une page 404 ou un message d'erreur
            include __DIR__ . '/pages/404.php';
        }
        ?>
    </main>
    <footer>
        <p>&copy; <?= date('Y') ?> DevHisto. Tous droits réservés.</p>
    </footer>
</body>
</html>
