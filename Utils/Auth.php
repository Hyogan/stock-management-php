<?php
namespace App\Utils;
use App\Utils\Database;
  class Auth {
    private $db;
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Authentifier un utilisateur
     */
    public static function login($username, $password)
     {
       $db =  Database::getInstance();
        $user = $db->fetch("SELECT * FROM utilisateur WHERE nom = ?", [$username]);
        
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            // Stocker les informations de l'utilisateur en session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['nom'];
            $_SESSION['user_type'] = $user['type'];
            $_SESSION['logged_in'] = true;
            return true;
        }
        
        return false;
    }
    
    /**
     * Déconnecter l'utilisateur
     */
    public function logout() {
        // Détruire toutes les variables de session
        $_SESSION = [];
        
        // Détruire la session
        session_destroy();
        
        // Rediriger vers la page de connexion
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
    
    /**
     * Vérifier si l'utilisateur est connecté
     */
    public static function isLoggedIn() 
    {
      return isset($_SESSION['user_id']);
    }
    
    /**
     * Obtenir l'ID de l'utilisateur connecté
     */
    public static function Id() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Obtenir le type d'utilisateur connecté
     */
    public function getUserType() {
        return $_SESSION['user_type'] ?? null;
    }
    
    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     */
    public function hasRole($role) {
        return $this->isLoggedIn() && $_SESSION['user_type'] === $role;
    }
    
    /**
     * Vérifier si l'utilisateur est autorisé à accéder à une page
     */
    public function requireRole($roles) {
        if (!$this->isLoggedIn()) {
            header('Location: ' . APP_URL . '/views/auth/login.php');
            exit;
        }
        
        $roles = (array) $roles;
        
        if (!in_array($this->getUserType(), $roles)) {
            header('Location: ' . APP_URL . '/views/errors/unauthorized.php');
            exit;
        }
        
        return true;
    }
    
    /**
     * Créer un nouvel utilisateur
     */
    public function register($username, $password, $type) {
        // Vérifier si l'utilisateur existe déjà
        $existingUser = $this->db->fetch("SELECT * FROM utilisateur WHERE nom = ?", [$username]);
        
        if ($existingUser) {
            return false; // L'utilisateur existe déjà
        }
        
        // Hacher le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insérer le nouvel utilisateur
        $userId = $this->db->insert('utilisateur', [
            'nom' => $username,
            'mot_de_passe' => $hashedPassword,
            'type' => $type
        ]);
        
        return $userId > 0;
    }


    public static function isAdmin() 
    {
      return isset($_SESSION['user_role']) && $_SESSION['user_role'] === ROLE_ADMIN;
  }
  
  public static function isStorekeeper() 
  {
      return isset($_SESSION['user_role']) && $_SESSION['user_role'] === ROLE_STOREKEEPER;
  }
  
  public static function isSecretary() 
  {
      return isset($_SESSION['user_role']) && $_SESSION['user_role'] === ROLE_SECRETARY;
  }

  public static function checkAccess($requiredRole) {
    if (!self::isLoggedIn()) {
        header('Location: ' . APP_URL . '/auth/login');
        exit;
    }
    $hasAccess = false;
    
    switch ($requiredRole) {
        case 'admin':
            $hasAccess = Auth::isAdmin();
            break;
        case 'magasinier':
            $hasAccess = Auth::isStorekeeper();
            break;
        case 'secretaire':
            $hasAccess = Auth::isSecretary();
            break;
        case 'any':
            $hasAccess = true;
            break;
        default:
            $hasAccess = false;
            break;
    }
    
    if (!$hasAccess) {
        // Rediriger vers une page d'accès refusé
        header('Location: ' . APP_URL . '/access-denied');
        exit;
    }
}
}
