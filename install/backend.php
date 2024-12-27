<?php
header('Content-Type: application/json');

$request = json_decode(file_get_contents('php://input'), true);
$step = $request['step'] ?? null;
$formData = $request['formData'] ?? [];

try {
    switch ($step) {
        case 'Droits en écriture (config.php)':
            $configFile = __DIR__ . '/../config.php';
            if (is_writable(__DIR__) && (!file_exists($configFile) || is_writable($configFile))) {
                echo json_encode(['status' => 'ok']);
            } else {
                echo json_encode(['status' => 'ko', 'message' => 'Pas de droits suffisants pour config.php.']);
            }
            break;

        case 'Extensions PHP (pdo_mysql)':
        case 'Extensions PHP (mbstring)':
        case 'Extensions PHP (json)':
        case 'Extensions PHP (ctype)':
            $extension = strtolower(explode(' ', $step)[2]);
            if (extension_loaded($extension)) {
                echo json_encode(['status' => 'ok']);
            } else {
                echo json_encode(['status' => 'ko', 'message' => "Extension PHP $extension manquante."]);
            }
            break;

        case 'Validation du serveur MySQL':
            $pdo = new PDO("mysql:host={$formData['db_host']};port={$formData['db_port']}", $formData['db_user'], $formData['db_pass']);
            echo json_encode(['status' => 'ok']);
            break;

        case 'Validation des identifiants MySQL':
            $pdo = new PDO("mysql:host={$formData['db_host']};port={$formData['db_port']};dbname={$formData['db_name']}", $formData['db_user'], $formData['db_pass']);
            echo json_encode(['status' => 'ok']);
            break;

        case 'Enregistrement de config.php':
            $configContent = <<<PHP
<?php
define('DB_HOST', '{$formData['db_host']}');
define('DB_PORT', '{$formData['db_port']}');
define('DB_NAME', '{$formData['db_name']}');
define('DB_USER', '{$formData['db_user']}');
define('DB_PASSWORD', '{$formData['db_pass']}');
PHP;
            if (file_put_contents(__DIR__ . '/../config.php', $configContent)) {
                echo json_encode(['status' => 'ok']);
            } else {
                echo json_encode(['status' => 'ko', 'message' => 'Erreur lors de l\'écriture de config.php.']);
            }
            break;

        case 'Création des tables SQL':
            $sql = file_get_contents(__DIR__ . '/bdd.sql');
            $pdo = new PDO("mysql:host={$formData['db_host']};port={$formData['db_port']};dbname={$formData['db_name']}", $formData['db_user'], $formData['db_pass']);
            $pdo->exec($sql);
            echo json_encode(['status' => 'ok']);
            break;

        case 'Création de l\'utilisateur admin':
            $pdo = new PDO("mysql:host={$formData['db_host']};port={$formData['db_port']};dbname={$formData['db_name']}", $formData['db_user'], $formData['db_pass']);
            $passwordHash = password_hash('admin', PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role_id) VALUES ('admin', :password, (SELECT id FROM roles WHERE role_name = 'admin'))");
            $stmt->bindParam(':password', $passwordHash);
            $stmt->execute();
            echo json_encode(['status' => 'ok']);
            break;

        default:
            echo json_encode(['status' => 'ko', 'message' => 'Étape inconnue.']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'ko', 'message' => $e->getMessage()]);
}
