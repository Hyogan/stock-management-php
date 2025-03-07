<?php
/**
 * Contrôleur d'Authentification
 * Gère la connexion, déconnexion et vérification des droits d'accès
 */
class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Afficher le formulaire de connexion
     */
    public function showLoginForm() {
        // Si l'utilisateur est déjà connecté, rediriger vers le tableau de bord
        if ($this->isLoggedIn()) {
            $this->redirectToDashboard();
            exit;
        }
        
        // Afficher la vue de connexion
        require_once BASE_PATH . '/views/auth/login.php';
    }
    
    /**
     * Traiter la connexion
     */
    public function login() {
        // Si l'utilisateur est déjà connecté, rediriger vers le tableau de bord
        if ($this->isLoggedIn()) {
            $this->redirectToDashboard();
            exit;
        }
        
        // Vérifier si le formulaire a été soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Authentifier l'utilisateur
            $user = $this->userModel->authenticate($username, $password);
            
            if ($user) {
                // Enregistrer l'utilisateur dans la session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nom'];
                $_SESSION['user_type'] = $user['type'];
                
                // Rediriger vers le tableau de bord approprié
                $this->redirectToDashboard();
                exit;
            } else {
                // Afficher un message d'erreur
                $error = "Nom d'utilisateur ou mot de passe incorrect.";
                require_once BASE_PATH . '/views/auth/login.php';
            }
        } else {
            // Rediriger vers le formulaire de connexion
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
    }
    
    /**
     * Déconnecter l'utilisateur
     */
    public function logout() {
        // Détruire la session
        session_unset();
        session_destroy();
        
        // Rediriger vers la page de connexion
        header('Location: ' . APP_URL . '/auth/login');
        exit;
    }
    
    /**
     * Vérifier si l'utilisateur est connecté
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Vérifier si l'utilisateur est administrateur
     */
    public function isAdmin() {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        return $_SESSION['user_type'] === 'directeur';
    }
    
    /**
     * Vérifier si l'utilisateur est magasinier
     */
    public function isStorekeeper() {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        return $_SESSION['user_type'] === 'magasinier';
    }
    
    /**
     * Vérifier si l'utilisateur est secrétaire
     */
    public function isSecretary() {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        return $_SESSION['user_type'] === 'secretaire';
    }
    
    /**
     * Rediriger vers le tableau de bord approprié
     */
    public function redirectToDashboard() {
        if (!$this->isLoggedIn()) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        
        if ($this->isAdmin()) {
            header('Location: ' . APP_URL . '/dashboard/admin');
        } elseif ($this->isStorekeeper()) {
            header('Location: ' . APP_URL . '/dashboard/storekeeper');
        } elseif ($this->isSecretary()) {
            header('Location: ' . APP_URL . '/dashboard/secretary');
        } else {
            header('Location: ' . APP_URL);
        }
        
        exit;
    }
    
    /**
     * Vérifier si l'utilisateur a les droits d'accès
     */
    public function checkAccess($requiredRole) {
        if (!$this->isLoggedIn()) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        
        $hasAccess = false;
        
        switch ($requiredRole) {
            case 'admin':
                $hasAccess = $this->isAdmin();
                break;
            case 'storekeeper':
                $hasAccess = $this->isStorekeeper();
                break;
            case 'secretary':
                $hasAccess = $this->isSecretary();
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
    
    /**
     * Afficher la page de changement de mot de passe
     */
    public function showChangePasswordForm() {
        if (!$this->isLoggedIn()) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        
        require_once BASE_PATH . '/views/auth/change_password.php';
    }
    
    /**
     * Traiter le changement de mot de passe
     */
    public function changePassword() {
        if (!$this->isLoggedIn()) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Vérifier que les nouveaux mots de passe correspondent
            if ($newPassword !== $confirmPassword) {
                $error = "Les nouveaux mots de passe ne correspondent pas.";
                require_once BASE_PATH . '/views/auth/change_password.php';
                return;
            }
            
            try {
                // Changer le mot de passe
                $this->userModel->changePassword($_SESSION['user_id'], $currentPassword, $newPassword);
                
                // Afficher un message de succès
                $success = "Votre mot de passe a été changé avec succès.";
                require_once BASE_PATH . '/views/auth/change_password.php';
            } catch (Exception $e) {
                // Afficher un message d'erreur
                $error = $e->getMessage();
                require_once BASE_PATH . '/views/auth/change_password.php';
            }
        } else {
            // Rediriger vers le formulaire de changement de mot de passe
            header('Location: ' . APP_URL . '/auth/change-password');
            exit;
        }
    }
}
