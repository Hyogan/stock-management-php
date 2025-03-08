<?php
/**
 * Point d'entrée de l'application
 */

// Définir le chemin de base
define('BASE_PATH', __DIR__);

// Charger l'autoloader
require_once BASE_PATH . '/autoload.php';

// Charger les configurations
require_once BASE_PATH . '/config/config.php';

// Démarrer la session
session_start();

// Initialiser l'application
$app = new App\Core\App();
$app->run();
