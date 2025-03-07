<?php
/**
 * Configuration générale de l'application
 */

// Définition des constantes de base
define('BASE_PATH', dirname(__DIR__));
define('APP_URL', 'http://localhost/stock-management');
define('APP_NAME', 'Gestion des Stocks');

// Configuration des sessions
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Fuseau horaire
date_default_timezone_set('Africa/Casablanca');

// Configuration des erreurs (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Chargement de la configuration de la base de données
$db_config = require_once BASE_PATH . '/config/database.php';

// Rôles utilisateurs
define('ROLE_ADMIN', 'admin');
define('ROLE_STOREKEEPER', 'storekeeper');
define('ROLE_SECRETARY', 'secretary');

// Statuts des commandes
define('ORDER_PENDING', 'pending');
define('ORDER_APPROVED', 'approved');
define('ORDER_REJECTED', 'rejected');
define('ORDER_DELIVERED', 'delivered');
define('ORDER_CANCELLED', 'cancelled');

// Statuts des paiements
define('PAYMENT_PENDING', 'pending');
define('PAYMENT_PARTIAL', 'partial');
define('PAYMENT_PAID', 'paid');

// Types d'opérations
define('OPERATION_ENTRY', 'entry');
define('OPERATION_EXIT', 'exit');
