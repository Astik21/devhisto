
<?php
header('Content-Type: application/json');

$request = json_decode(file_get_contents('php://input'), true);
$step = $request['step'] ?? null;
$formData = $request['formData'] ?? [];

try {
    switch ($step) {
        case 'check-write':
            $configFile = __DIR__ . '/../config.php';
            if (is_writable(__DIR__) && (!file_exists($configFile) || is_writable($configFile))) {
                echo json_encode(['status' => 'ok']);
            } else {
                echo json_encode(['status' => 'ko', 'message' => 'Droits insuffisants pour écrire dans config.php.']);
            }
            break;

        case 'check-extension-pdo':
        case 'check-extension-mbstring':
        case 'check-extension-json':
        case 'check-extension-ctype':
            $extension = str_replace('check-extension-', '', $step);
            if (extension_loaded($extension)) {
                echo json_encode(['status' => 'ok']);
            } else {
                echo json_encode(['status' => 'ko', 'message' => "Extension PHP $extension manquante."]);
            }
            break;

        case 'check-mysql-server':
            $pdo = new PDO("mysql:host={$formData['db_host']};port={$formData['db_port']}", $formData['db_user'], $formData['db_pass']);
            echo json_encode(['status' => 'ok']);
            break;

        case 'check-mysql-credentials':
            $pdo = new PDO("mysql:host={$formData['db_host']};port={$formData['db_port']};dbname={$formData['db_name']}", $formData['db_user'], $formData['db_pass']);
            echo json_encode(['status' => 'ok']);
            break;

        case 'create-config':
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
                echo json_encode(['status' => 'ko', 'message' => 'Impossible d'enregistrer config.php.']);
            }
            break;

        case 'create-tables':
            $sql = file_get_contents(__DIR__ . '/bdd.sql');
            $pdo = new PDO("mysql:host={$formData['db_host']};port={$formData['db_port']};dbname={$formData['db_name']}", $formData['db_user'], $formData['db_pass']);
            $pdo->exec($sql);
            echo json_encode(['status' => 'ok']);
            break;

        case 'create-admin':
            $passwordHash = password_hash('admin', PASSWORD_BCRYPT);
            $pdo = new PDO("mysql:host={$formData['db_host']};port={$formData['db_port']};dbname={$formData['db_name']}", $formData['db_user'], $formData['db_pass']);
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
