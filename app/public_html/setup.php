<?php
session_start();

// === Variables à renseigner === //
$githubZipUrl = "https://github.com/Astik21/devhisto/raw/main/releases/devhisto_v0.3.zip";
$publicHtmlPath = '/opt/bitnami/nginx/html'; // Chemin monté pour Nginx
$privatePath = '/app/private'; // Chemin monté pour PHP-FPM 

// === Fonction pour téléchargement et extraction === //
function downloadAndExtractFromGitHub($githubZipUrl, $publicHtmlPath, $privatePath) {
    $status = [];

    // Téléchargement du fichier ZIP
    $zipFile = sys_get_temp_dir() . '/devhisto.zip'; // Fichier temporaire
    $status[] = "Téléchargement en cours...";
    if (!file_put_contents($zipFile, file_get_contents($githubZipUrl))) {
        $status[] = "Erreur : Impossible de télécharger les fichiers depuis GitHub ($githubZipUrl).";
        return $status;
    }
    $status[] = "Fichier téléchargé avec succès.";

    // Extraction du fichier ZIP
    $status[] = "Extraction des fichiers en cours...";
    $zip = new ZipArchive();
    if ($zip->open($zipFile) === TRUE) {
        $tempExtractPath = sys_get_temp_dir() . '/devhisto_extracted';
        if (!is_dir($tempExtractPath)) {
            mkdir($tempExtractPath, 0755, true);
        }
        $zip->extractTo($tempExtractPath);
        $zip->close();

        // Déplacement des fichiers vers les bons répertoires
        if (is_dir("$tempExtractPath/public_html")) {
            rename("$tempExtractPath/public_html", $publicHtmlPath);
            $status[] = "Fichiers publics déplacés vers $publicHtmlPath.";
        } else {
            $status[] = "Erreur : Le dossier public_html est introuvable dans l'archive.";
        }

        if (is_dir("$tempExtractPath/private")) {
            rename("$tempExtractPath/private", $privatePath);
            $status[] = "Fichiers privés déplacés vers $privatePath.";
        } else {
            $status[] = "Erreur : Le dossier private est introuvable dans l'archive.";
        }

        // Supprimer le dossier temporaire
        system("rm -rf " . escapeshellarg($tempExtractPath));
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

// Téléchargement et extraction
$steps = array_merge($steps, downloadAndExtractFromGitHub($githubZipUrl, $publicHtmlPath, $privatePath));

// Redirection vers la page install.php si tout est OK
if (end($steps) === "Fichier ZIP temporaire supprimé.") {
    $steps[] = "Redirection vers la page install.php...";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }
        h1, h2 {
            color: #333;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        li:last-child {
            border-bottom: none;
        }
        .button-container {
            margin-top: 20px;
            text-align: center;
        }
        .button-container button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .button-container button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Installation de DevHisto</h1>
        <div id="status-container">
            <h2>Étapes de l'installation</h2>
            <ul>
                <?php foreach ($steps as $step): ?>
                    <li><?= htmlspecialchars($step) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php if (end($steps) === "Redirection vers la page install.php..."): ?>
            <div class="button-container">
                <form method="get" action="/install.php">
                    <button type="submit">Passer à l'étape suivante</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
