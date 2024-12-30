<?php
session_start();

// === Variables à renseigner === //
$githubZipUrl = "https://github.com/Astik21/devhisto/raw/main/releases/devhisto_v0.3.zip";
$publicHtmlPath = __DIR__ . '/../html'; // Chemin monté pour public_html
$privatePath = __DIR__ . '/../private'; // Chemin monté pour private
$tempPath = $publicHtmlPath . '/temp_' . uniqid(); // Répertoire temporaire unique

// === Fonction pour téléchargement et extraction === //
function downloadAndExtractFromGitHub($githubZipUrl, $publicHtmlPath, $privatePath, $tempPath) {
    $status = [];

    // Création du répertoire temporaire
    if (!is_dir($tempPath)) {
        mkdir($tempPath, 0755, true);
        $status[] = "Création du répertoire temporaire : $tempPath.";
    }

    // Téléchargement du fichier ZIP
    $zipFile = $tempPath . '/devhisto.zip';
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
        $tempExtractPath = $tempPath . '/extracted';
        mkdir($tempExtractPath, 0755, true);
        $zip->extractTo($tempExtractPath);
        $zip->close();
        $status[] = "Fichiers extraits dans $tempExtractPath.";

        // Déplacement des fichiers vers les bons répertoires
        if (is_dir("$tempExtractPath/public_html")) {
            recursiveCopy("$tempExtractPath/public_html", $publicHtmlPath);
            $status[] = "Fichiers publics déplacés vers $publicHtmlPath.";
        } else {
            $status[] = "Erreur : Le dossier public_html est introuvable dans l'archive.";
        }

        if (is_dir("$tempExtractPath/private")) {
            recursiveCopy("$tempExtractPath/private", $privatePath);
            $status[] = "Fichiers privés déplacés vers $privatePath.";
        } else {
            $status[] = "Erreur : Le dossier private est introuvable dans l'archive.";
        }

        // Suppression du dossier temporaire
        system("rm -rf " . escapeshellarg($tempPath));
        $status[] = "Répertoire temporaire supprimé : $tempPath.";
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

// === Fonction pour copier récursivement === //
function recursiveCopy($source, $destination) {
    $directory = opendir($source);
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    while (($file = readdir($directory)) !== false) {
        if ($file !== '.' && $file !== '..') {
            $srcFile = $source . '/' . $file;
            $destFile = $destination . '/' . $file;

            if (is_dir($srcFile)) {
                recursiveCopy($srcFile, $destFile);
            } else {
                copy($srcFile, $destFile);
            }
        }
    }
    closedir($directory);
}

// === Exécution === //
$steps = [];

// Téléchargement et extraction
$steps = array_merge($steps, downloadAndExtractFromGitHub($githubZipUrl, $publicHtmlPath, $privatePath, $tempPath));

// Ajouter le bouton si tout est OK
$success = in_array("Fichiers publics déplacés vers $publicHtmlPath.", $steps) &&
           in_array("Fichiers privés déplacés vers $privatePath.", $steps);
if ($success) {
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
        <?php if ($success): ?>
            <div class="button-container">
                <form method="get" action="/install.php">
                    <button type="submit">Passer à l'étape suivante</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
