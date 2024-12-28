<?php
header('Content-Type: application/json');

// Récupération des données envoyées via AJAX
$request = json_decode(file_get_contents('php://input'), true);
$step = $request['step'] ?? null;
$formData = $request['formData'] ?? [];

try {
    switch ($step) {
        // Vérification des droits en écriture
        case 'Droits en écriture (config.php)':
            $configFile = __DIR__ . '/../config.php';
            if (is_writable(__DIR__) && (!file_exists($configFile) || is_writable($configFile))) {
                echo json_encode(['status' => 'ok']);
            } else {
                echo json_encode(['status' => 'ko', 'message' => 'Pas de droits suffisants pour config.php.']);
            }
            break;

        // Vérification des extensions PHP
        case 'Extensions PHP (pdo_mysql)':
        case 'Extensions PHP (mbstring)':
        case 'Extensions PHP (json)':
        case 'Extensions PHP (ctype)':
            $extension = strtolower(explode(' ', $step)[2]); // Extrait l'extension depuis le nom de l'étape
            if (extension_loaded($extension)) {
                echo json_encode(['status' => 'ok']);
            } else {
                echo json_encode(['status' => 'ko', 'message' => "Extension PHP $extension manquante."]);
            }
            break;

        // Validation de la connexion au serveur SQL
        case 'Connexion au serveur SQL':
            try {
                $pdo = new PDO("mysql:host={$formData['db_host']};port={$formData['db_port']}", $formData['db_user'], $formData['db_pass']);
                echo json_encode(['status' => 'ok']);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'ko', 'message' => 'Connexion au serveur SQL échouée : ' . $e->getMessage()]);
            }
            break;

        // Validation des identifiants SQL
        case 'Validation des identifiants SQL':
            try {
                $pdo = new PDO("mysql:host={$formData['db_host']};port={$formData['db_port']};dbname={$formData['db_name']}", $formData['db_user'], $formData['db_pass']);
                echo json_encode(['status' => 'ok']);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'ko', 'message' => 'Connexion à la base de données échouée : ' . $e->getMessage()]);
            }
            break;

        // Enregistrement du fichier config.php
        case 'Enregistrement de config.php':
            $configContent = <<<PHP
<?php
define('DB_HOST', '{$formData['db_host']}');
define('DB_PORT', '{$formData['db_port']}');
define('DB_NAME', '{$formData['db_name']}');
define('DB_USER', '{$formData['db_user']}');
define('DB_PASSWORD', '{$formData['db_pass']}');
PHP;
            try {
                $configFile = __DIR__ . '/../config.php';
                if (file_put_contents($configFile, $configContent)) {
                    echo json_encode(['status' => 'ok']);
                } else {
                    echo json_encode(['status' => 'ko', 'message' => 'Erreur lors de l\'écriture de config.php.']);
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'ko', 'message' => 'Erreur critique : ' . $e->getMessage()]);
            }
            break;

        // Création des tables SQL
        case 'Création des tables SQL':
            try {
                $sql = file_get_contents(__DIR__ . '/bdd.sql');
                $pdo = new PDO("mysql:host={$formData['db_host']};port={$formData['db_port']};dbname={$formData['db_name']}", $formData['db_user'], $formData['db_pass']);
                $pdo->exec($sql);
                echo json_encode(['status' => 'ok']);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'ko', 'message' => 'Création des tables échouée : ' . $e->getMessage()]);
            }
            break;

        // Création de l'utilisateur admin
        case 'Création de l\'utilisateur admin':
            try {
                $pdo = new PDO("mysql:host={$formData['db_host']};port={$formData['db_port']};dbname={$formData['db_name']}", $formData['db_user'], $formData['db_pass']);
                $passwordHash = password_hash('admin', PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role_id) VALUES ('admin', :password, (SELECT id FROM roles WHERE role_name = 'admin'))");
                $stmt->bindParam(':password', $passwordHash);
                $stmt->execute();
                echo json_encode(['status' => 'ok']);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'ko', 'message' => 'Création de l\'utilisateur admin échouée : ' . $e->getMessage()]);
            }
            break;

        default:
            echo json_encode(['status' => 'ko', 'message' => 'Étape inconnue.']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'ko', 'message' => 'Erreur inattendue : ' . $e->getMessage()]);
}
