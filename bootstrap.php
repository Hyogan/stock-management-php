<?php
/**
 * Bootstrap de l'application
 * 
 * Ce fichier initialise l'application en chargeant les configurations
 * et en configurant l'environnement
 */

// Définir le chemin de base
define('BASE_PATH', __DIR__);

// Charger l'autoloader
require_once BASE_PATH . '/autoload.php';

// Charger les configurations
require_once BASE_PATH . '/config/config.php';

// Initialiser la session
session_start();

// Configurer la gestion des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', BASE_PATH . '/logs/error.log');

// Configurer le fuseau horaire
date_default_timezone_set('Africa/Casablanca');

// Définir les constantes de l'application
define('APP_URL', 'http://localhost/admin-elect');
define('APP_NAME', 'Admin Elect');
define('APP_EMAIL', 'nelson@gmail.com');
define('APP_VERSION', '1.0.0');

// Définir les constantes pour les rôles
define('ROLE_ADMIN', 'admin');
define('ROLE_STOREKEEPER', 'magasinier');
define('ROLE_SECRETARY', 'secretaire');

// Définir les constantes pour les statuts de commande
define('ORDER_STATUS_PENDING', 'en_attente');
define('ORDER_STATUS_VALIDATED', 'validee');
define('ORDER_STATUS_IN_PROGRESS', 'en_cours');
define('ORDER_STATUS_DELIVERED', 'livree');
define('ORDER_STATUS_CANCELLED', 'annulee');

// Définir les constantes pour les statuts de paiement
define('PAYMENT_STATUS_PENDING', 'en_attente');
define('PAYMENT_STATUS_PARTIAL', 'partiel');
define('PAYMENT_STATUS_COMPLETE', 'complet');
define('PAYMENT_STATUS_REFUNDED', 'rembourse');

// Fonctions utilitaires globales
function redirect($url) {
    header("Location: " . $url);
    exit;
}

function asset($path) {
    return APP_URL . '/assets/' . $path;
}

function url($path) {
    return APP_URL . $path;
}

function old($key, $default = '') {
    return $_SESSION['old'][$key] ?? $default;
}

function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function check_csrf() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        die('CSRF token validation failed');
    }
}

function flash($key, $message = null) {
    if ($message) {
        $_SESSION['flash'][$key] = $message;
    } else {
        $message = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $message;
    }
}

function formatMoney($amount) {
    return number_format($amount, 2, ',', ' ') . ' DH';
}

function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

function isActive($path) {
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return $currentPath === $path ? 'active' : '';
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === ROLE_ADMIN;
}

function isStorekeeper() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === ROLE_STOREKEEPER;
}

function isSecretary() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === ROLE_SECRETARY;
}

function hasPermission($permission) {
    // Implémentation à compléter selon votre système de permissions
    return true;
}
?>
