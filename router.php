<?php
// router.php
if (preg_match('/\.(?:css|js|jpg|jpeg|png|gif|ico|svg)$/', $_SERVER["REQUEST_URI"])) {
    // Si la requête concerne un fichier statique
    if (strpos($_SERVER["REQUEST_URI"], '/assets/') === 0) {
        // Rediriger les requêtes /assets/ vers /public/assets/
        $file = __DIR__ . '/public' . $_SERVER["REQUEST_URI"];
        if (file_exists($file)) {
            // Définir le type MIME approprié
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            switch ($ext) {
                case 'css':
                    header('Content-Type: text/css');
                    break;
                case 'js':
                    header('Content-Type: application/javascript');
                    break;
                case 'jpg':
                case 'jpeg':
                    header('Content-Type: image/jpeg');
                    break;
                case 'png':
                    header('Content-Type: image/png');
                    break;
                case 'gif':
                    header('Content-Type: image/gif');
                    break;
                case 'svg':
                    header('Content-Type: image/svg+xml');
                    break;
                case 'ico':
                    header('Content-Type: image/x-icon');
                    break;
            }
            readfile($file);
            exit;
        }
    }
}

// Pour toutes les autres requêtes, inclure index.php
include __DIR__ . '/index.php';
