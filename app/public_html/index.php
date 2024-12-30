<?php
session_start();

// Définir le chemin de configuration en tant que constante
define('CONFIG_PATH', __DIR__ . '/../private/config/config.php');

// Charger la configuration
if (!file_exists(CONFIG_PATH)) {
    die('Erreur : Le fichier de configuration est manquant.');
}
require_once CONFIG_PATH;

// Connexion à la base de données
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Erreur : Connexion à la base de données échouée. ' . $e->getMessage());
}

// Gestion de la page demandée
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) && $page !== 'login') {
    header('Location: ?page=login');
    exit;
}

// Charger les pages dynamiquement
$pagePath = __DIR__ . "/pages/$page.php";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord</title>
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
    <header>
        <h1>Bienvenue dans DevHisto</h1>
        <nav>
            <ul>
                <li><a href="?page=home">Accueil</a></li>
                <li><a href="?page=add">Ajouter</a></li>
                <li><a href="?page=view">Consulter</a></li>
                <li><a href="?page=stats">Statistiques</a></li>
                <li><a href="?logout=true">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main id="main-content">
        <?php
        if (file_exists($pagePath)) {
            require $pagePath;
        } else {
            require __DIR__ . '/pages/404.php';
        }
        ?>
    </main>
    <footer>
        <p>&copy; <?= date('Y') ?> DevHisto. Tous droits réservés.</p>
    </footer>
    <?php if ($page !== 'login'): ?>
        <script src="/assets/dashboard.js"></script>
    <?php endif; ?>
</body>
</html>
