<?php
session_start();

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
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Erreur : Connexion à la base de données échouée. ' . $e->getMessage());
}

// Gestion de la page demandée
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ?page=login');
    exit;
}

// Charger les pages dynamiquement
$pagePath = "pages/$page.php";
if (file_exists($pagePath)) {
    require $pagePath;
} else {
    require 'pages/404.php';
}
