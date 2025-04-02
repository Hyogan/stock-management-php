<?php
namespace App\Controllers;
/**
 * Contrôleur d'Authentification
 * Gère la connexion, déconnexion et vérification des droits d'accès
 */
use Exception;
use App\Core\Controller;
use App\Models\User;
use App\Utils\Auth;
use App\Utils\Database;
class AuthController extends Controller{
    /**
     * Afficher le formulaire de connexion
     */
    public function login() {
        // die('hi everyone');
        // Si l'utilisateur est déjà connecté, rediriger vers le tableau de bord
        if (Auth::isLoggedIn()) {
          $this->redirect('/dashboard');
        }
        $this->view('auth/login', [
          'pageTitle' => 'Connexion'
      ],'auth');
    }


    public function register() 
    {
      
      // Si l'utilisateur est déjà connecté, rediriger vers le tableau de bord
      if ($this->isLoggedIn()) {
          $this->redirect('/dashboard');
          exit;
      }

      $this->view('auth/register', [
          'pageTitle' => 'Inscription'
      ]);
  }


  public function store()
   {
    // Si l'utilisateur est déjà connecté, rediriger vers le tableau de bord
    
    if ($this->isLoggedIn()) {
        $this->redirect('/dashboard');
        exit;
    }
    

    // Vérifier si le formulaire a été soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = 'secretary'; // Role par défaut
        $statut = 'actif'; // Statut par défaut

        $errors = [];

        // Validation des données
        if (empty($nom)) {
            $errors['nom'] = 'Le nom est obligatoire';
        }
        if (empty($prenom)) {
            $errors['prenom'] = 'Le prénom est obligatoire';
        }
        if (empty($email)) {
            $errors['email'] = 'L\'email est obligatoire';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'L\'email n\'est pas valide';
        }
        if (empty($password)) {
            $errors['password'] = 'Le mot de passe est obligatoire';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères';
        }

        // Vérifier si l'email existe déjà
        if (User::emailExists($email)) {
            $errors['email'] = 'Cet email est déjà utilisé';
        }
        
        // Si des erreurs sont présentes, afficher le formulaire avec les erreurs
        if (!empty($errors)) {
            $this->view('auth/register', [
                'pageTitle' => 'Inscription',
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'errors' => $errors
            ]);
            return;
        }

       
        // Ajouter l'utilisateur à la base de données
        $userId = User::add([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'mot_de_passe' => $password, // Le mot de passe sera hashé dans le modèle
            'role' => 'secretaire',
            'statut' => $statut
        ]);

        if ($userId) {
            // Rediriger vers la page de connexion avec un message de succès
            $this->redirect('/auth/login?register_success=1');
        } else {
            // Afficher une erreur si l'inscription a échoué
            $this->view('auth/register', [
                'pageTitle' => 'Inscription',
                'error' => 'Une erreur est survenue lors de l\'inscription'
            ]);
        }
    } else {
        // Si le formulaire n'a pas été soumis, afficher le formulaire
        $this->register();
    }
}
    
    /**
     * Traiter la connexion
     */
    public function authenticate() 
    {
      // die('hi everyone');
        // Si l'utilisateur est déjà connecté, rediriger vers le tableau de bord
        if (Auth::isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        // Vérifier si le formulaire a été soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']) ? true : false;
            $errors = [];
            if (empty($email)) {
              $errors['username'] = 'Le nom est obligatoire';
          }
          if (empty($password)) {
              $errors['password'] = 'Le mot de passe est obligatoire';
          }
          if (!empty($errors)) {
            $this->view('auth/login', [
                'pageTitle' => 'Connexion',
                'email' => $email,
                'errors' => $errors
            ]);
            return;
        }
        
        $user = User::getByEmail($email);
        if (!$user || !password_verify($password, $user['mot_de_passe'])) {
          // $data = [
           
          // ];
          $this->view('auth/login',[
            'pageTitle' => 'Connexion',
            'email' => $email,
            'error' => 'Identifiants incorrects'
          ]);
          return;
      }
      
      // Vérifier si le compte est actif
      if ($user['statut'] !== 'actif') {
          $this->view('auth/login', [
              'pageTitle' => 'Connexion',
              'email' => $email,
              'error' => 'Votre compte est désactivé'
          ]);
          return;
      }
      
      // Connecter l'utilisateur
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
      $_SESSION['user_email'] = $user['email'];
      $_SESSION['user_role'] = $user['role'];
      $_SESSION['logged_in'] = $user['role'];
      // die($user);

      // var_dump('fsdfsd');
      
      // Si "Se souvenir de moi" est coché, créer un cookie
      if ($remember) {
          $token = bin2hex(random_bytes(32));
          $expiry = time() + (30 * 24 * 60 * 60); // 30 jours
          
          // Enregistrer le token en base de données
          User::saveRememberToken($user['id'], $token, $expiry);
          
          // Créer le cookie
          setcookie('remember_token', $token, $expiry, '/', '', false, true);
      }
      
      // Mettre à jour la dernière connexion
      User::updateLastLogin($user['id']);
      
      // Rediriger vers le tableau de bord
      $this->redirect('/dashboard');
    }
  }
   /**
     * Déconnecte l'utilisateur
     */
    public function logout() {
      // Supprimer le token "Se souvenir de moi" s'il existe
      session_start();
      session_unset();
      session_destroy();
      // Supprimer le cookie de session
      if (ini_get("session.use_cookies")) {
          $params = session_get_cookie_params();
          setcookie(session_name(), '', time() - 42000,
              $params["path"], $params["domain"],
              $params["secure"], $params["httponly"]
          );
      }
      
      // Rediriger vers la page de connexion
      $this->redirect('/auth/login');
  }
    
    /**
     * Vérifier si l'utilisateur est connecté
     */
    public function isLoggedIn() 
    {
        return isset($_SESSION['user_id']);
    }
     /**
     * Affiche le formulaire de réinitialisation de mot de passe
     */
    public function forgotPasswordForm() {
      $this->view('auth/forgot-password', [
          'pageTitle' => 'Mot de passe oublié'
      ]);
    }
      /**
     * Vérifier si l'utilisateur a un rôle spécifique
     */
    public function hasRole($role) {
      return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
  }
    
    /**
     * Rediriger vers le tableau de bord approprié
     */
    public function redirectToDashboard() {
        if (!$this->isLoggedIn()) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        
        if (Auth::isAdmin()) {
            header('Location: ' . APP_URL . '/dashboard/admin');
        } elseif (Auth::isStorekeeper()) {
            header('Location: ' . APP_URL . '/dashboard/storekeeper');
        } elseif (Auth::isSecretary()) {
            header('Location: ' . APP_URL . '/dashboard/secretary');
        } else {
            header('Location: ' . APP_URL);
        }
        exit;
    }

    public function isAdmin(){return Auth::isAdmin();}
    public function isStoreKeeper(){return Auth::isStorekeeper();}
    public function isSecretary(){return Auth::isSecretary();}
    
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
                $hasAccess = Auth::isAdmin();
                break;
            case 'storekeeper':
                $hasAccess = Auth::isStorekeeper();
                break;
            case 'secretary':
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
  /**
   * Traiter la demande de réinitialisation de mot de passe
   */
  public function forgotPassword(){
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirect('/forgot-password');
        exit;
    }
    
    // Récupérer l'email
    $email = $_POST['email'] ?? '';
    
    // Valider l'email
    if (empty($email)) {
        $this->view('auth/forgot-password', [
            'pageTitle' => 'Mot de passe oublié',
            'error' => 'L\'email est obligatoire'
        ]);
        return;
    }
    
    // Vérifier si l'email existe
    $user = User::getByEmail($email);
    if (!$user) {
        $this->view('auth/forgot-password', [
            'pageTitle' => 'Mot de passe oublié',
            'email' => $email,
            'error' => 'Aucun compte n\'est associé à cet email'
        ]);
        return;
    }
    
    // Générer un token de réinitialisation
    $token = bin2hex(random_bytes(32));
    $expiry = time() + (24 * 60 * 60); // 24 heures
    
    // Enregistrer le token en base de données
    User::saveResetToken($user['id'], $token, $expiry);
    // Envoyer l'email de réinitialisation
    $resetLink = APP_URL . '/reset-password?token=' . $token;
    $subject = 'Réinitialisation de votre mot de passe';
    $message = "Bonjour {$user['prenom']},\n\n";
    $message .= "Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le lien ci-dessous pour procéder :\n\n";
    $message .= $resetLink . "\n\n";
    $message .= "Ce lien est valable pendant 24 heures.\n\n";
    $message .= "Si vous n'avez pas demandé à réinitialiser votre mot de passe, ignorez cet email.\n\n";
    $message .= "Cordialement,\n";
    $message .= APP_NAME;
    
    $headers = "From: " . APP_EMAIL . "\r\n";
    $headers .= "Reply-To: " . APP_EMAIL . "\r\n";
    
    mail($email, $subject, $message, $headers);
    
    // Afficher un message de confirmation
    $this->view('auth/forgot-password-confirm', [
        'pageTitle' => 'Email envoyé',
        'email' => $email
    ]);
  }


    /**
     * Affiche le formulaire de réinitialisation de mot de passe
     */
    public function resetPasswordForm() {
      // Récupérer le token
      $token = $_GET['token'] ?? '';
      
      if (empty($token)) {
          $this->redirect('/login');
          exit;
      }
      
      // Vérifier si le token est valide
      $user = User::getByResetToken($token);
      
      if (!$user) {
          $this->view('auth/reset-password-error', [
              'pageTitle' => 'Lien invalide',
              'error' => 'Le lien de réinitialisation est invalide ou a expiré'
          ]);
          return;
      }
      
      // Vérifier si le token a expiré
      if (time() > $user['reset_token_expiry']) {
          $this->view('auth/reset-password-error', [
              'pageTitle' => 'Lien expiré',
              'error' => 'Le lien de réinitialisation a expiré'
          ]);
          return;
      }
      
      $this->view('auth/reset-password', [
          'pageTitle' => 'Réinitialiser le mot de passe',
          'token' => $token
      ]);
  }
  
  /**
   * Traite la réinitialisation de mot de passe
   */
  public function resetPassword() {
      // Vérifier si le formulaire a été soumis
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
          $this->redirect('/login');
          exit;
      }
      
      // Récupérer les données du formulaire
      $token = $_POST['token'] ?? '';
      $password = $_POST['password'] ?? '';
      $passwordConfirm = $_POST['password_confirm'] ?? '';
      
      // Valider les données
      $errors = [];
      
      if (empty($token)) {
          $this->redirect('/login');
          exit;
      }
      
      if (empty($password)) {
          $errors['password'] = 'Le mot de passe est obligatoire';
      } elseif (strlen($password) < 8) {
          $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères';
      }
      
      if ($password !== $passwordConfirm) {
          $errors['password_confirm'] = 'Les mots de passe ne correspondent pas';
      }
      
      // Vérifier si le token est valide
      $user = User::getByResetToken($token);
      
      if (!$user) {
          $this->view('auth/reset-password-error', [
              'pageTitle' => 'Lien invalide',
              'error' => 'Le lien de réinitialisation est invalide ou a expiré'
          ]);
          return;
      }
      
      // Vérifier si le token a expiré
      if (time() > $user['reset_token_expiry']) {
          $this->view('auth/reset-password-error', [
              'pageTitle' => 'Lien expiré',
              'error' => 'Le lien de réinitialisation a expiré'
          ]);
          return;
      }
      
      // S'il y a des erreurs, afficher le formulaire avec les erreurs
      if (!empty($errors)) {
          $this->view('auth/reset-password', [
              'pageTitle' => 'Réinitialiser le mot de passe',
              'token' => $token,
              'errors' => $errors
          ]);
          return;
      }
      // Mettre à jour le mot de passe
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
      User::updatePassword($user['id'],$password, $hashedPassword);
      // Supprimer le token de réinitialisation
      User::clearResetToken($user['id']);
      
      // Afficher un message de confirmation
      $this->view('auth/reset-password-success', [
          'pageTitle' => 'Mot de passe réinitialisé'
      ]);
  }
}
