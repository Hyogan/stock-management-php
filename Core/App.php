<?php
namespace App\Core;

class App {
  public function run() {
    $uri = $_SERVER['REQUEST_URI'];
    
    // Supprimer les paramètres de requête s'il y en a
    if (($pos = strpos($uri, '?')) !== false) {
        $uri = substr($uri, 0, $pos);
    }
    
    // Supprimer le trailing slash s'il y en a un (sauf pour la racine)
    if ($uri !== '/' && substr($uri, -1) === '/') {
        $uri = rtrim($uri, '/');
    }
    
    // Charger les routes
    include BASE_PATH . '/routes.php';
    
    // Vérifier si les routes sont définies
    if (!isset($routes) || !is_array($routes)) {
        die("Erreur critique: Les routes ne sont pas correctement définies.");
    }
    
    // Chercher une correspondance de route
    $matchedRoute = false;
    $params = [];
    
    foreach ($routes as $route => $handler) {
        // Convertir la route en expression régulière
        $pattern = $this->convertRouteToRegex($route);
        
        if (preg_match($pattern, $uri, $matches)) {
            $matchedRoute = true;
            
            // Extraire les paramètres de l'URL
            $paramNames = $this->extractParamNames($route);
            foreach ($paramNames as $index => $name) {
                if (isset($matches[$index + 1])) {
                    $params[$name] = $matches[$index + 1];
                }
            }
            $controllerName = $handler[0];
            $action = $handler[1];
            
            // Construire le nom complet de la classe avec namespace
            $controllerClass = "App\\Controllers\\{$controllerName}";
            
            // Vérifier si la classe existe
            if (!class_exists($controllerClass)) {
                $this->handleError("Contrôleur '{$controllerClass}' non trouvé.");
                return;
            }
            
            $controller = new $controllerClass();
            
            // Vérifier si la méthode existe
            if (!method_exists($controller, $action)) {
                $this->handleError("Action '{$action}' non trouvée dans le contrôleur '{$controllerClass}'.");
                return;
            }
            
            // Exécuter l'action avec les paramètres
            call_user_func_array([$controller, $action], $params);
            break;
        }
    }
    
    if (!$matchedRoute) {
        // Gestion de l'erreur 404
        $this->handleNotFound();
    }
  }


        /**
     * Gère les erreurs 404
     */
    private function handleNotFound() {
      header("HTTP/1.0 404 Not Found");
      $errorFile = BASE_PATH . "/views/errors/404.php";
      
      if (file_exists($errorFile)) {
          include $errorFile;
      } else {
          echo "<h1>404 - Page non trouvée</h1>";
          echo "<p>La page que vous recherchez n'existe pas.</p>";
      }
  }
  
  /**
   * Gère les erreurs générales
   */
  private function handleError($message) {
      header("HTTP/1.0 500 Internal Server Error");
      $errorFile = BASE_PATH . "/views/errors/500.php";
      
      if (file_exists($errorFile)) {
          include $errorFile;
      } else {
          echo "<h1>500 - Erreur interne du serveur</h1>";
          echo "<p>{$message}</p>";
      }
  }
/**
 * Convertit une route avec paramètres en expression régulière
 */
private function convertRouteToRegex($route) {
    // Remplacer {param} ou :param par une capture d'expression régulière
    $pattern = preg_replace('/{([a-zA-Z0-9_]+)}/', '([^/]+)', $route);
    $pattern = preg_replace('/:([a-zA-Z0-9_]+)/', '([^/]+)', $pattern);
    
    // Échapper les caractères spéciaux et ajouter les délimiteurs
    $pattern = '#^' . str_replace('/', '\/', $pattern) . '$#';
    
    return $pattern;
}

/**
 * Extrait les noms des paramètres d'une route
 */
private function extractParamNames($route) {
    $paramNames = [];
    
    // Extraire les noms des paramètres entre accolades
    if (preg_match_all('/{([a-zA-Z0-9_]+)}/', $route, $matches)) {
        $paramNames = $matches[1];
    }
    
    // Extraire les noms des paramètres après les deux-points
    if (preg_match_all('/:([a-zA-Z0-9_]+)/', $route, $matches)) {
        $paramNames = array_merge($paramNames, $matches[1]);
    }
    
    return $paramNames;
  }
}
