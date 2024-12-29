<?php
header('Content-Type: application/json');
require_once __DIR__ . '/steps.php';

// Récupération des données envoyées via AJAX
$request = json_decode(file_get_contents('php://input'), true);
$step = $request['step'] ?? null;
$formData = $request['formData'] ?? [];

try {
    switch ($step) {
        // Vérification des droits en écriture
        case 'check_write_permissions':
            $configFile = __DIR__ . '/../../private/config/config.php';
            if (is_writable(__DIR__) && (!file_exists($configFile) || is_writable($configFile))) {
                echo json_encode(['status' => 'ok', 'displayName' => $stepDisplayNames[$step]['label']]);
            } else {
                echo json_encode(['status' => 'ko', 'message' => 'Pas de droits suffisants pour config.php.', 'displayName' => $stepDisplayNames[$step]['label']]);
            }
            break;

        // Vérification des extensions PHP
        case 'check_php_pdo_mysql':
        case 'check_php_mbstring':
        case 'check_php_json':
        case 'check_php_ctype':
            $extension = str_replace('check_php_', '', $step);
            echo json_encode([
                'status' => extension_loaded($extension) ? 'ok' : 'ko',
                'message' => "Extension PHP `$extension` non détectée.",
                'displayName' => $stepDisplayNames[$step]['label']
            ]);
            break;

        // Vérification de config.php et préremplissage des champs
        case 'check_config_file':
            $configFile = __DIR__ . '/../config.php';
            if (file_exists($configFile)) {
                require_once $configFile;

                if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_NAME')) {
                    echo json_encode([
                        'status' => 'ko',
                        'message' => 'Les constantes nécessaires dans config.php ne sont pas définies.',
                        'displayName' => $stepDisplayNames[$step]['label'],
                        'formData' => [] // Retourner un tableau vide si les constantes sont absentes
                    ]);
                } else {
                    // Retourner les valeurs de config.php
                    echo json_encode([
                        'status' => 'ok',
                        'message' => 'Fichier config.php valide.',
                        'displayName' => $stepDisplayNames[$step]['label'],
                        'formData' => [
                            'db_host' => DB_HOST,
                            'db_port' => DB_PORT,
                            'db_name' => DB_NAME,
                            'db_user' => DB_USER,
                            'db_pass' => DB_PASSWORD
                        ]
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'ko',
                    'message' => 'Fichier config.php introuvable.',
                    'displayName' => $stepDisplayNames[$step]['label'],
                    'formData' => [] // Retourner un tableau vide si le fichier n'existe pas
                ]);
            }
            break;



        // Validation de la connexion au serveur SQL
        case 'test_sql_connection':
            try {
                $dbHost = $formData['db_host'] ?? '';
                $dbPort = $formData['db_port'] ?? '';
                $dbUser = $formData['db_user'] ?? '';
                $dbPass = $formData['db_pass'] ?? '';

                if (empty($dbHost) || empty($dbUser)) {
                    throw new Exception('Les paramètres SQL (hôte et utilisateur) sont obligatoires.');
                }

                $dsn = "mysql:host=$dbHost";
                if (!empty($dbPort)) {
                    $dsn .= ";port=$dbPort";
                }

                $pdo = new PDO($dsn, $dbUser, $dbPass);
                echo json_encode(['status' => 'ok', 'displayName' => $stepDisplayNames[$step]['label']]);
            } catch (Exception $e) {
                echo json_encode([
                    'status' => 'ko',
                    'message' => 'Erreur : ' . $e->getMessage(),
                    'formData' => $formData,
                    'displayName' => $stepDisplayNames[$step]['label']
                ]);
            }
            break;




        // Validation des identifiants SQL
        case 'validate_sql_credentials':
            try {
                $dbHost = $formData['db_host'] ?? '';
                $dbPort = $formData['db_port'] ?? '';
                $dbName = $formData['db_name'] ?? '';
                $dbUser = $formData['db_user'] ?? '';
                $dbPass = $formData['db_pass'] ?? '';

                $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName";
                $pdo = new PDO($dsn, $dbUser, $dbPass);
                echo json_encode(['status' => 'ok', 'displayName' => $stepDisplayNames[$step]['label']]);
            } catch (PDOException $e) {
                echo json_encode([
                    'status' => 'ko',
                    'message' => 'Validation des identifiants SQL échouée : ' . $e->getMessage(),
                    'formData' => compact('dbHost', 'dbPort', 'dbName', 'dbUser', 'dbPass'),
                    'displayName' => $stepDisplayNames[$step]['label']
                ]);
            }
            break;


        // Enregistrement du fichier config.php
        case 'save_config_file':
            $configContent = <<<PHP
<?php
define('DB_HOST', '{$formData['db_host']}');
define('DB_PORT', '{$formData['db_port']}');
define('DB_NAME', '{$formData['db_name']}');
define('DB_USER', '{$formData['db_user']}');
define('DB_PASSWORD', '{$formData['db_pass']}');
PHP;
            try {
                $configFile = __DIR__ . '/../../private/config/config.php';
                if (file_put_contents($configFile, $configContent)) {
                    echo json_encode(['status' => 'ok', 'displayName' => $stepDisplayNames[$step]['label']]);
                } else {
                        
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'ko', 'message' => 'Erreur critique : ' . $e->getMessage(), 'displayName' => $stepDisplayNames[$step]['label']]);
            }
            break;

        // Création des tables SQL
        case 'create_sql_tables':
            try {
                $pdo = new PDO("mysql:host={$formData['db_host']};port={$formData['db_port']};dbname={$formData['db_name']}", $formData['db_user'], $formData['db_pass']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Vérifier l'existence des tables avec le préfixe devhisto_
                $existingTables = $pdo->query("SHOW TABLES LIKE 'devhisto_%'")->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($existingTables)) {
                    // Tables existantes, demander confirmation
                    echo json_encode([
                        'status' => 'ko',
                        'message' => 'Des tables avec le préfixe `devhisto_` existent déjà.',
                        'actionRequired' => true,
                        'existingTables' => $existingTables,
                        'displayName' => $stepDisplayNames[$step]['label']
                    ]);
                    break;
                }

                // Créer les tables
                $sql = file_get_contents(__DIR__ . '/bdd.sql');
                $pdo->exec($sql);

                echo json_encode(['status' => 'ok', 'displayName' => $stepDisplayNames[$step]['label']]);
            } catch (PDOException $e) {
                echo json_encode([
                    'status' => 'ko',
                    'message' => 'Création des tables échouée : ' . $e->getMessage(),
                    'displayName' => $stepDisplayNames[$step]['label']
                ]);
            }
            break;

        //Suppression des tables existantes
        case 'delete_existing_tables':
            try {
                $pdo = new PDO("mysql:host={$formData['db_host']};port={$formData['db_port']};dbname={$formData['db_name']}", $formData['db_user'], $formData['db_pass']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Désactiver les contraintes de clé étrangère
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

                // Supprimer les tables existantes avec le préfixe devhisto_
                $existingTables = $pdo->query("SHOW TABLES LIKE 'devhisto_%'")->fetchAll(PDO::FETCH_COLUMN);

                foreach ($existingTables as $table) {
                    $pdo->exec("DROP TABLE `$table`");
                }

                // Réactiver les contraintes de clé étrangère
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

                echo json_encode(['status' => 'ok', 'message' => 'Tables existantes supprimées.', 'displayName' => $stepDisplayNames[$step]['label']]);
            } catch (PDOException $e) {
                echo json_encode([
                    'status' => 'ko',
                    'message' => 'Erreur lors de la suppression des tables : ' . $e->getMessage(),
                    'displayName' => $stepDisplayNames[$step]['label']
                ]);
            }
            break;


        // Création de l'utilisateur admin
        case 'create_admin_user':
            try {
                $pdo = new PDO("mysql:host={$formData['db_host']};port={$formData['db_port']};dbname={$formData['db_name']}", $formData['db_user'], $formData['db_pass']);
                $passwordHash = password_hash('admin', PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO devhisto_users (username, password, role_id) VALUES ('admin', :password, (SELECT id FROM devhisto_roles WHERE role_name = 'admin'))");
                $stmt->bindParam(':password', $passwordHash);
                $stmt->execute();
                echo json_encode(['status' => 'ok', 'displayName' => $stepDisplayNames[$step]['label']]);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'ko', 'message' => 'Création de l\'utilisateur admin échouée : ' . $e->getMessage(), 'displayName' => $stepDisplayNames[$step]['label']]);
            }
            break;

        // Suppression du répertoire install et du fichier install.php
        case 'remove_install_directory':
            $installDir = __DIR__; // Répertoire actuel (install/)
            $rootInstallFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'install.php'; // Fichier install.php à la racine
            $rootSetupFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'setup.php'; // Fichier setup.php à la racine

            try {
                // Supprimer les fichiers et répertoires dans install/
                $files = array_diff(scandir($installDir), ['.', '..']);
                foreach ($files as $file) {
                    $filePath = $installDir . DIRECTORY_SEPARATOR . $file;
                    is_dir($filePath) ? rmdir($filePath) : unlink($filePath);
                }
                rmdir($installDir); // Supprimer le répertoire install/

                // Supprimer install.php à la racine
                if (file_exists($rootInstallFile)) {
                    unlink($rootInstallFile);
                }

                // Supprimer setup.php à la racine
                if (file_exists($rootSetupFile)) {
                    unlink($rootSetupFile);
                }

                echo json_encode(['status' => 'ok', 'displayName' => $stepDisplayNames[$step]['label']]);
            } catch (Exception $e) {
                echo json_encode(['status' => 'ko', 'message' => 'Erreur lors de la suppression : ' . $e->getMessage(), 'displayName' => $stepDisplayNames[$step]['label']]);
            }
            break;
        default:
            echo json_encode(['status' => 'ko', 'message' => 'Étape inconnue.', 'displayName' => $stepDisplayNames[$step]['label']]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'ko', 'message' => 'Erreur inattendue : ' . $e->getMessage(), 'displayName' => $stepDisplayNames[$step]['label']]);
}
