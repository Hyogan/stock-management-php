<?php
/**
 * Configuration générale de l'application
 */
// Configuration de l'application
define('APP_URL', 'http://localhost:8080'); // URL de base de l'application
define('APP_NAME', 'Gestion de stocks '); // Nom de l'application
define('APP_VERSION', '1.0.0'); // Version de l'application

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'admin_elect');
define('DB_USER', 'root');
define('DB_PASS', 'root');

// Configuration des chemins
define('ROOT_PATH', dirname(__DIR__));
define('VIEW_PATH', ROOT_PATH . '/views');
define('CONTROLLER_PATH', ROOT_PATH . '/Controllers');
define('MODEL_PATH', ROOT_PATH . '/Models');
define('ASSET_PATH', ROOT_PATH . '/public/assets');
define('ROLE_ADMIN', 'admin');
define('ROLE_STOREKEEPER', 'magasinier');
define('ROLE_SECRETARY', 'secretaire');
define('OP_TYPE_EXIT', 'sortie');
define('OP_TYPE_ENTRY', 'entree');

// Configuration des sessions
define('SESSION_LIFETIME', 3600); // Durée de vie de la session en secondes (1 heure)

require_once BASE_PATH . '/Utils/Helpers.php';

// return [
//     'base_path' => dirname(__DIR__),
//     'app_url' => 'http://localhost/stock-management',
//     'app_name' => 'Gestion des Stocks',
//     'session' => [
//         'cookie_httponly' => 1,
//         'use_only_cookies' => 1,
//     ],
//     'timezone' => 'Africa/Casablanca',
//     'error_reporting' => E_ALL,
//     'display_errors' => 1, // Mettez à 0 en production
//     'database' => require_once __DIR__ . '/database.php',
//     'roles' => [
//         'admin' => 'admin',
//         'storekeeper' => 'storekeeper',
//         'secretary' => 'secretary',
//     ],
//     'order_statuses' => [
//         'pending' => 'pending',
//         'approved' => 'approved',
//         'rejected' => 'rejected',
//         'delivered' => 'delivered',
//         'cancelled' => 'cancelled',
//     ],
//     'payment_statuses' => [
//         'pending' => 'pending',
//         'partial' => 'partial',
//         'paid' => 'paid',
//     ],
//     'operation_types' => [
//         'entry' => 'entry',
//         'exit' => 'exit',
//     ],
// ];
