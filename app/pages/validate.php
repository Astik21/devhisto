<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pdf_path'])) {
    $pdfPath = $_POST['pdf_path'];
    $output = shell_exec("tesseract " . escapeshellarg($pdfPath) . " stdout");

    $extractedData = $output ? nl2br(htmlspecialchars($output)) : "Aucune donnée détectée.";

    if (isset($_POST['validate'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO devis (description, amount, date) VALUES (:description, :amount, :date)");
            $stmt->execute([
                'description' => $_POST['description'],
                'amount' => $_POST['amount'],
                'date' => $_POST['date'],
            ]);
            $success = "Le devis a été ajouté avec succès.";
            unset($_SESSION['uploaded_pdf']);
        } catch (Exception $e) {
            $error = "Erreur lors de l'ajout du devis : " . $e->getMessage();
        }
    }
}
?>
<h2>Valider les données extraites</h2>
<?php if (!empty($success)): ?>
    <p class="message"><?= htmlspecialchars($success) ?></p>
<?php elseif (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<form method="POST" action="index.php?page=validate">
    <label for="description">Description :</label>
    <textarea id="description" name="description" required><?= htmlspecialchars($extractedData ?? '') ?></textarea><br>
    <label for="amount">Montant (€) :</label>
    <input type="number" id="amount" name="amount" step="0.01" required><br>
    <label for="date">Date :</label>
    <input type="date" id="date" name="date" value="<?= date('Y-m-d') ?>"><br>
    <input type="hidden" name="pdf_path" value="<?= htmlspecialchars($pdfPath) ?>">
    <button type="submit" name="validate">Enregistrer le devis</button>
</form>
