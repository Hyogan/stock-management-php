<?php
/**
 * Contrôleur d'Utilisateur
 * Gère les opérations CRUD pour les utilisateurs
 */
class UserController {
    private $userModel;
    private $authController;
    
    public function __construct() {
        $this->userModel = new User();
        $this->authController = new AuthController();
    }
    
    /**
     * Afficher la liste des utilisateurs
     */
    public function index() {
        // Vérifier les droits d'accès (seul l'admin peut gérer les utilisateurs)
        $this->authController->checkAccess('admin');
        
        // Récupérer tous les utilisateurs
        $users = $this->userModel->getAll();
        
        // Afficher la vue
        require_once BASE_PATH . '/views/users/index.php';
    }
    
    /**
     * Afficher le formulaire d'ajout d'utilisateur
     */
    public function showAddForm() {
        // Vérifier les droits d'accès
        $this->authController->checkAccess('admin');
        
        // Récupérer les types d'utilisateurs pour le formulaire
        $userTypes = ['directeur', 'storekeeper', 'secretary'];
        
        // Afficher la vue
        require_once BASE_PATH . '/views/users/add.php';
    }
    
    /**
     * Traiter l'ajout d'un utilisateur
     */
    public function add() {
        // Vérifier les droits d'accès
        $this->authController->checkAccess('admin');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $userData = [
                'nom' => $_POST['nom'] ?? '',
                'prenom' => $_POST['prenom'] ?? '',
                'email' => $_POST['email'] ?? '',
                'username' => $_POST['username'] ?? '',
                'password' => $_POST['password'] ?? '',
                'type' => $_POST['type'] ?? ''
            ];
            
            // Valider les données
            $errors = $this->validateUserData($userData);
            
            if (empty($errors)) {
                // Ajouter l'utilisateur
                $userId = $this->userModel->add($userData);
                
                if ($userId) {
                    // Rediriger vers la liste des utilisateurs avec un message de succès
                    $_SESSION['success_message'] = "L'utilisateur a été ajouté avec succès.";
                    header('Location: ' . APP_URL . '/users');
                    exit;
                } else {
                    $error = "Une erreur s'est produite lors de l'ajout de l'utilisateur.";
                }
            } else {
                // Afficher les erreurs
                $error = implode('<br>', $errors);
            }
            
            // En cas d'erreur, réafficher le formulaire avec les données
            $userTypes = ['directeur', 'storekeeper', 'secretary'];
            require_once BASE_PATH . '/views/users/add.php';
        } else {
            // Rediriger vers le formulaire d'ajout
            header('Location: ' . APP_URL . '/users/add');
            exit;
        }
    }
    
    /**
     * Afficher le formulaire de modification d'utilisateur
     */
    public function showEditForm($id) {
        // Vérifier les droits d'accès
        $this->authController->checkAccess('admin');
        
        // Récupérer l'utilisateur
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            // Utilisateur non trouvé
            $_SESSION['error_message'] = "L'utilisateur demandé n'existe pas.";
            header('Location: ' . APP_URL . '/users');
            exit;
        }
        
        // Récupérer les types d'utilisateurs pour le formulaire
        $userTypes = ['directeur', 'storekeeper', 'secretary'];
        
        // Afficher la vue
        require_once BASE_PATH . '/views/users/edit.php';
    }
    
    /**
     * Traiter la modification d'un utilisateur
     */
    public function update($id) {
        // Vérifier les droits d'accès
        $this->authController->checkAccess('admin');
        
        // Vérifier si l'utilisateur existe
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            $_SESSION['error_message'] = "L'utilisateur demandé n'existe pas.";
            header('Location: ' . APP_URL . '/users');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $userData = [
                'nom' => $_POST['nom'] ?? '',
                'prenom' => $_POST['prenom'] ?? '',
                'email' => $_POST['email'] ?? '',
                'username' => $_POST['username'] ?? '',
                'type' => $_POST['type'] ?? ''
            ];
            
            // Ajouter le mot de passe seulement s'il est fourni
            if (!empty($_POST['password'])) {
                $userData['password'] = $_POST['password'];
            }
            
            // Valider les données
            $errors = $this->validateUserData($userData, $id);
            
            if (empty($errors)) {
                // Mettre à jour l'utilisateur
                $success = $this->userModel->update($id, $userData);
                
                if ($success) {
                    // Rediriger vers la liste des utilisateurs avec un message de succès
                    $_SESSION['success_message'] = "L'utilisateur a été mis à jour avec succès.";
                    header('Location: ' . APP_URL . '/users');
                    exit;
                } else {
                    $error = "Une erreur s'est produite lors de la mise à jour de l'utilisateur.";
                }
            } else {
                // Afficher les erreurs
                $error = implode('<br>', $errors);
            }
            
            // En cas d'erreur, réafficher le formulaire avec les données
            $userTypes = ['directeur', 'storekeeper', 'secretary'];
            require_once BASE_PATH . '/views/users/edit.php';
        } else {
            // Rediriger vers le formulaire de modification
            header('Location: ' . APP_URL . '/users/edit/' . $id);
            exit;
        }
    }
    
    /**
     * Supprimer un utilisateur
     */
    public function delete($id) {
        // Vérifier les droits d'accès
        $this->authController->checkAccess('admin');
        
        // Vérifier si l'utilisateur existe
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            $_SESSION['error_message'] = "L'utilisateur demandé n'existe pas.";
            header('Location: ' . APP_URL . '/users');
            exit;
        }
        
        // Empêcher la suppression de son propre compte
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error_message'] = "Vous ne pouvez pas supprimer votre propre compte.";
            header('Location: ' . APP_URL . '/users');
            exit;
        }
        
        // Supprimer l'utilisateur
        $success = $this->userModel->delete($id);
        
        if ($success) {
          $_SESSION['success_message'] = "L'utilisateur a été supprimé avec succès.";
      } else {
          $_SESSION['error_message'] = "Une erreur s'est produite lors de la suppression de l'utilisateur.";
      }
      
      header('Location: ' . APP_URL . '/users');
      exit;
  }

   /**
     * Afficher le profil de l'utilisateur connecté
     */
    public function profile() {
      // Vérifier si l'utilisateur est connecté
      $this->authController->checkLogin();
      
      // Récupérer les informations de l'utilisateur
      $user = $this->userModel->getById($_SESSION['user_id']);
      
      // Afficher la vue
      require_once BASE_PATH . '/views/users/profile.php';
  }
  
  /**
   * Mettre à jour le profil de l'utilisateur connecté
   */
  public function updateProfile() {
      // Vérifier si l'utilisateur est connecté
      $this->authController->checkLogin();
      
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          // Récupérer les données du formulaire
          $userData = [
              'nom' => $_POST['nom'] ?? '',
              'prenom' => $_POST['prenom'] ?? '',
              'email' => $_POST['email'] ?? '',
              'username' => $_POST['username'] ?? ''
          ];
          
          // Ajouter le mot de passe seulement s'il est fourni
          if (!empty($_POST['password'])) {
              $userData['password'] = $_POST['password'];
          }
          
          // Valider les données
          $errors = $this->validateUserData($userData, $_SESSION['user_id']);
          
          if (empty($errors)) {
              // Mettre à jour l'utilisateur
              $success = $this->userModel->update($_SESSION['user_id'], $userData);
              
              if ($success) {
                  // Mettre à jour le nom dans la session
                  $_SESSION['user_name'] = $userData['nom'];
                  
                  // Rediriger vers le profil avec un message de succès
                  $_SESSION['success_message'] = "Votre profil a été mis à jour avec succès.";
                  header('Location: ' . APP_URL . '/profile');
                  exit;
              } else {
                  $error = "Une erreur s'est produite lors de la mise à jour de votre profil.";
              }
          } else {
              // Afficher les erreurs
              $error = implode('<br>', $errors);
          }
          
          // En cas d'erreur, réafficher le formulaire avec les données
          $user = $userData;
          require_once BASE_PATH . '/views/users/profile.php';
      } else {
          // Rediriger vers le profil
          header('Location: ' . APP_URL . '/profile');
          exit;
      }
  }
  
  /**
   * Valider les données d'un utilisateur
   */
  private function validateUserData($data, $userId = null) {
      $errors = [];
      
      // Vérifier que les champs obligatoires sont remplis
      if (empty($data['nom'])) {
          $errors[] = "Le nom est obligatoire.";
      }
      
      if (empty($data['prenom'])) {
          $errors[] = "Le prénom est obligatoire.";
      }
      
      if (empty($data['email'])) {
          $errors[] = "L'email est obligatoire.";
      } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
          $errors[] = "L'email n'est pas valide.";
      }
      
      if (empty($data['username'])) {
          $errors[] = "Le nom d'utilisateur est obligatoire.";
      }
      
      // Vérifier que le mot de passe est fourni pour un nouvel utilisateur
      if (!$userId && empty($data['password'])) {
          $errors[] = "Le mot de passe est obligatoire.";
      }
      
      // Vérifier que le type d'utilisateur est valide
      $validTypes = ['directeur', 'storekeeper', 'secretary'];
      if (empty($data['type']) || !in_array($data['type'], $validTypes)) {
          $errors[] = "Le type d'utilisateur n'est pas valide.";
      }
      
      // Vérifier que le nom d'utilisateur est unique
      if ($this->userModel->isUsernameExists($data['username'], $userId)) {
          $errors[] = "Ce nom d'utilisateur est déjà utilisé.";
      }
      
      // Vérifier que l'email est unique
      if ($this->userModel->isEmailExists($data['email'], $userId)) {
          $errors[] = "Cet email est déjà utilisé.";
      }
      
      return $errors;
  }

}
