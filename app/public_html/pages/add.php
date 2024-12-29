<?php
require_once __DIR__ . '/../../private/vendor/autoload.php'; // Autoload de Composer

use Smalot\PdfParser\Parser;

$error = '';
$extractedData = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf_file'])) {
    $uploadDir = __DIR__ . '/../uploads/';
    $pdfPath = $uploadDir . basename($_FILES['pdf_file']['name']);

    // Validation du fichier et téléchargement
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    if ($_FILES['pdf_file']['type'] !== 'application/pdf') {
        $error = "Le fichier doit être au format PDF.";
    } elseif (!move_uploaded_file($_FILES['pdf_file']['tmp_name'], $pdfPath)) {
        $error = "Erreur lors du téléchargement du fichier.";
    } else {
        // Analyser le PDF
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($pdfPath);
            $extractedData = $pdf->getText();
            if (empty(trim($extractedData))) {
                $error = "Aucune donnée valide n'a pu être extraite du fichier PDF.";
            }
        } catch (Exception $e) {
            $error = "Erreur lors de l'analyse du fichier : " . $e->getMessage();
        }

        // Supprimer le fichier PDF après traitement
        unlink($pdfPath);
    }
}
?>
<h2>Ajouter un devis</h2>
<?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<?php if (!empty($extractedData)): ?>
    <h3>Contenu extrait :</h3>
    <form method="POST" action="index.php?page=validate">
        <label for="description">Description :</label>
        <textarea id="description" name="description" rows="10" cols="50" required><?= htmlspecialchars($extractedData) ?></textarea><br>
        <label for="amount">Montant (€) :</label>
        <input type="number" id="amount" name="amount" step="0.01" required><br>
        <label for="date">Date :</label>
        <input type="date" id="date" name="date" value="<?= date('Y-m-d') ?>" required><br>
        <button type="submit">Valider les données</button>
    </form>
<?php else: ?>
    <form method="POST" enctype="multipart/form-data">
        <label for="pdf_file">Sélectionner un fichier PDF :</label>
        <input type="file" id="pdf_file" name="pdf_file" accept="application/pdf" required><br>
        <button type="submit">Téléverser et analyser</button>
    </form>
<?php endif; ?>
