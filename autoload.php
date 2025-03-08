<?php
/**
 * Autoloader personnalisé pour l'application
 * 
 * Ce fichier gère le chargement automatique des classes en fonction de leur namespace
 */

spl_autoload_register(function ($class) {
    // Préfixe de base pour le namespace de l'application
    $prefix = 'App\\';
    
    // Répertoire de base pour les classes de l'application
    $baseDir = __DIR__ . '/';
    
    // Vérifier si la classe utilise le préfixe
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // Si la classe n'utilise pas notre préfixe, passer au prochain autoloader
        return;
    }
    
    // Obtenir le chemin relatif de la classe
    $relativeClass = substr($class, $len);
    
    // Remplacer les séparateurs de namespace par des séparateurs de répertoire
    // et ajouter .php
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
     // Débogage
    //  error_log("Tentative de chargement de la classe: {$class}");
    //  error_log("Chemin du fichier: {$file}");
    //  error_log("Le fichier existe: " . (file_exists($file) ? 'Oui' : 'Non'));
    // Si le fichier existe, le charger
    if (file_exists($file)) {
        require $file;
    }
});

// <?php

// /**
//  * Autoloader personnalisé pour charger automatiquement les classes
//  */
// spl_autoload_register(function ($class) {
//     // Définir le répertoire de base
//     $baseDir = __DIR__ . '/';
    
//     // Convertir le namespace en chemin de fichier
//     $file = $baseDir . str_replace('\\', '/', $class) . '.php';
    
//     // Gérer les cas spéciaux pour les différents namespaces
//     if (strpos($class, 'App\\Models\\') === 0) {
//         $file = $baseDir . str_replace('App\\Models\\', 'Models/', $class) . '.php';
//     } elseif (strpos($class, 'App\\Controllers\\') === 0) {
//         $file = $baseDir . str_replace('App\\Controllers\\', 'Controllers/', $class) . '.php';
//     } elseif (strpos($class, 'App\\Core\\') === 0) {
//         $file = $baseDir . str_replace('App\\Core\\', 'Core/', $class) . '.php';
//     } elseif (strpos($class, 'App\\Utils\\') === 0) {
//         $file = $baseDir . str_replace('App\\Utils\\', 'Utils/', $class) . '.php';
//     }
    
//     // Déboguer le chargement de classe (décommenter pour déboguer)
//     echo "Tentative de chargement de la classe: $class<br>";
//     echo "Chemin du fichier: $file<br>";
//     echo "Le fichier existe: " . (file_exists($file) ? 'Oui' : 'Non') . "<br>";
    
//     // Si le fichier existe, le charger
//     if (file_exists($file)) {
//         require $file;
//     }
// });
