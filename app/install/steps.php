<?php
$stepDisplayNames = [
    'check_write_permissions' => [
        'label' => 'Droits en écriture (config.php)',
        'hidden' => false
    ],
    'check_php_pdo_mysql' => [
        'label' => 'Extensions PHP : PDO MySQL',
        'hidden' => false
    ],
    'check_php_mbstring' => [
        'label' => 'Extensions PHP : mbstring',
        'hidden' => false
    ],
    'check_php_json' => [
        'label' => 'Extensions PHP : JSON',
        'hidden' => false
    ],
    'check_php_ctype' => [
        'label' => 'Extensions PHP : Ctype',
        'hidden' => false
    ],
    'check_config_file' => [
        'label' => 'Contrôle de la présence du fichier config.php',
        'hidden' => false
    ],
    'test_sql_connection' => [
        'label' => 'Connexion au serveur SQL',
        'hidden' => false
    ],
    'validate_sql_credentials' => [
        'label' => 'Validation des identifiants SQL',
        'hidden' => false
    ],
    'save_config_file' => [
        'label' => 'Enregistrement du fichier config.php',
        'hidden' => false
    ],
    'delete_existing_tables' => [
        'label' => 'Suppression des tables existantes',
        'hidden' => true
    ],
    'create_sql_tables' => [
        'label' => 'Création des tables SQL',
        'hidden' => false
    ],
    'create_admin_user' => [
        'label' => 'Création de l\'utilisateur admin',
        'hidden' => false
    ],
    'remove_install_directory' => [
        'label' => 'Suppression des fichiers d\'installation',
        'hidden' => false
    ]
];