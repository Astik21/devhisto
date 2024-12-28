<?php
session_start();

// === Variables à renseigner === //
$githubZipUrl = "https://github.com/astik21/devhisto/releases/devhisto_v0.1.zip";
$destinationDir = __DIR__ . '/tmp';
$rootDir = __DIR__;

// === Fonction pour téléchargement et extraction === //
function downloadAndExtractFromGitHub($githubZipUrl, $destinationDir, $rootDir) {
    $status = [];

    // Téléchargement du fichier ZIP
    $zipFile = $destinationDir . '/devhisto.zip';
    $status[] = "Téléchargement en cours...";
    if (!file_put_contents($zipFile, file_get_contents($githubZipUrl))) {
        $status[] = "Erreur : Impossible de télécharger les fichiers depuis GitHub.";
        return $status;
    }
    $status[] = "Fichier téléchargé avec succès.";

    // Extraction du fichier ZIP
    $status[] = "Extraction des fichiers en cours...";
    $zip = new ZipArchive();
    if ($zip->open($zipFile) === TRUE) {
        $zip->extractTo($rootDir);
        $zip->close();
        $status[] = "Fichiers extraits avec succès à la racine du serveur.";
    } else {
        $status[] = "Erreur : Impossible d'extraire les fichiers ZIP.";
        unlink($zipFile);
        return $status;
    }

    // Suppression du fichier ZIP temporaire
    unlink($zipFile);
    $status[] = "Fichier ZIP temporaire supprimé.";

    return $status;
}

// === Exécution === //
$steps = [];

// Création du répertoire temporaire si nécessaire
if (!is_dir($destinationDir)) {
    mkdir($destinationDir, 0777, true);
    $steps[] = "Répertoire temporaire créé.";
} else {
    $steps[] = "Répertoire temporaire déjà existant.";
}

// Téléchargement et extraction
$steps = array_merge($steps, downloadAndExtractFromGitHub($githubZipUrl, $destinationDir, $rootDir));

// Redirection vers la page install.php si tout est OK
if (end($steps) === "Fichier ZIP temporaire supprimé.") {
    $steps[] = "Redirection vers la page install.php...";
    header("Location: install.php");
    exit;
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
    <div id="status-container">
        <h2>Étapes de l'installation</h2>
        <ul>
            <?php foreach ($steps as $step): ?>
                <li><?= htmlspecialchars($step) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
