<?php
namespace App\Core;

class Controller {
    // protected function view($view, $data = []) {
    //     extract($data); // Extrait les données pour les utiliser dans la vue
    //     include BASE_PATH . "/views/{$view}.php";
    // 
    protected function view($view, $data = [], $layout = 'default') {
      extract($data);
      // Capture le contenu de la vue
      ob_start();
      include BASE_PATH . "/views/{$view}.php";
      $content = ob_get_clean();
  
      // Charger le bon layout
      include BASE_PATH . "/views/layouts/{$layout}.php";
  }
    
    /**
     * Rediriger vers une URL
     */
    protected function redirect($url) {
        header("Location: " . $url);
        exit;
    }
    
    /**
     * Renvoyer une réponse JSON
     */
    protected function json($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}

