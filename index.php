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
require_once 'config.php';

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

// Gestion des pages
$page = $_GET['page'] ?? 'home';

// Charger les informations utilisateur
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - DevHisto</title>
    <link rel="stylesheet" href="styles.css">
    <script src="dashboard.js" defer></script>
</head>
<body>
    <header>
        <h1>Bienvenue, <?= htmlspecialchars($user['username']) ?> !</h1>
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
    <main id="main-content">
        <?php
        switch ($page) {
            case 'home':
                echo "<h2>Accueil</h2>";
                echo "<p>Bienvenue sur DevHisto ! Utilisez le menu ci-dessus pour naviguer entre les sections.</p>";
                break;

            case 'add':
                echo "<h2>Ajouter un devis</h2>";
                echo '<form>
                        <label for="description">Description :</label>
                        <input type="text" id="description" name="description" required><br>
                        <label for="amount">Montant :</label>
                        <input type="number" id="amount" name="amount" required><br>
                        <button type="submit">Ajouter</button>
                      </form>';
                break;

            case 'view':
                echo "<h2>Consulter les devis</h2>";
                echo "<p>Liste des devis à venir.</p>";
                break;

            case 'stats':
                echo "<h2>Statistiques</h2>";
                echo "<p>Statistiques et visualisation des données.</p>";
                break;

            default:
                echo "<h2>Page introuvable</h2>";
                echo "<p>La page demandée n'existe pas.</p>";
                break;
        }
        ?>
    </main>
    <footer>
        <p>&copy; <?= date('Y') ?> DevHisto. Tous droits réservés.</p>
    </footer>
</body>
</html>
