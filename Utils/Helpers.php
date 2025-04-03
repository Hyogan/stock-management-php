<?php
/**
 * Fonctions utilitaires pour l'application
 */

/**
 * Échapper les données pour éviter les attaques XSS
 */

function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Rediriger vers une URL
 */
// function redirect($url) {
//     header("Location: {$url}");
//     exit;
// }

/**
 * Générer un jeton CSRF
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifier un jeton CSRF
 */
function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

/**
 * Afficher un message flash
 */
function flash($key, $message = null) {
  if ($message) {
      // Set flash message
      $_SESSION['flash'][$key] = $message;
  } elseif (isset($_SESSION['flash'][$key])) {
      // Retrieve and delete flash message (so it only appears once)
      $msg = $_SESSION['flash'][$key];
      unset($_SESSION['flash'][$key]);
      return $msg;
  }
  return null;
}
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Récupérer et effacer un message flash
 */
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function dd($variable) {
  echo "<pre>";
  print_r($variable); // Use print_r for array display
  echo "</pre>";
  die();
}

/**
 * Formater une date
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    $dateObj = new DateTime($date);
    return $dateObj->format($format);
}

/**
 * Formater un prix
 */
function formatPrice($price) {
    return number_format($price, 2, ',', ' ') . ' FCFA';
}

/**
 * Générer un numéro de référence unique
 */
function generateReference($prefix = 'REF') {
    $timestamp = time();
    $random = mt_rand(1000, 9999);
    return $prefix . $timestamp . $random;
}

/**
 * Vérifier si une requête est de type POST
 */
function isPostRequest() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Obtenir l'URL actuelle
 */
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Tronquer un texte
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}
