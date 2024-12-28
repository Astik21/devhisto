<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf_file'])) {
    $uploadDir = __DIR__ . '/../uploads/';
    $uploadFile = $uploadDir . basename($_FILES['pdf_file']['name']);
    $error = '';
    $success = '';

    // Validation du fichier
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    if ($_FILES['pdf_file']['type'] !== 'application/pdf') {
        $error = "Le fichier doit être au format PDF.";
    } elseif (!move_uploaded_file($_FILES['pdf_file']['tmp_name'], $uploadFile)) {
        $error = "Erreur lors du téléchargement du fichier.";
    } else {
        // Appeler le script OCR pour extraire les données
        $output = shell_exec("tesseract " . escapeshellarg($uploadFile) . " stdout");
        $extractedData = $output ? nl2br(htmlspecialchars($output)) : "Aucune donnée détectée dans le fichier.";

        // Sauvegarder le chemin du fichier pour validation ultérieure
        $_SESSION['uploaded_pdf'] = $uploadFile;
    }
}

?>
<h2>Ajouter un devis</h2>
<?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<?php if (!empty($extractedData)): ?>
    <h3>Contenu extrait :</h3>
    <div class="ocr-output"><?= $extractedData ?></div>
    <form method="POST" action="index.php?page=validate">
        <input type="hidden" name="pdf_path" value="<?= htmlspecialchars($_SESSION['uploaded_pdf']) ?>">
        <button type="submit">Valider les données</button>
    </form>
<?php else: ?>
    <form method="POST" enctype="multipart/form-data">
        <label for="pdf_file">Sélectionner un fichier PDF :</label>
        <input type="file" id="pdf_file" name="pdf_file" accept="application/pdf" required><br>
        <button type="submit">Téléverser et analyser</button>
    </form>
<?php endif; ?>
