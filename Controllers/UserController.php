<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\User;
use App\Utils\Auth;
/**
 * Contrôleur d'Utilisateur
 * Gère les opérations CRUD pour les utilisateurs
 */
class UserController extends Controller{

  public function __construct() {
    $this->checkAuth();
  }
  private function checkAuth() {
    if(!Auth::isLoggedIn()) {
    }
  }
  private function checkAdmin() {
    if(!Auth::isAdmin()) {
      return $this->redirect('/dashboard');
    }
  }
    /**
     * Afficher la liste des utilisateurs
     */
    public function index() {
        // Récupérer tous les utilisateurs
        $users = User::getAll();
        // dd($users);
        return $this->view('users/index',['users' => $users],'admin');
    }
    
    /**
     * Afficher le formulaire d'ajout d'utilisateur
     */
    public function create() {
        // Récupérer les types d'utilisateurs pour le formulaire
        $userTypes = ['admin', 'magasinier', 'secretaire'];
        return $this->view('users/create',['user' => $userTypes],'admin');
    }
    
    /**
     * Traiter l'ajout d'un utilisateur
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $userTypes = ['admin', 'magasinier', 'secretaire'];
            // Récupérer les données du formulaire
            $userData = [
                'nom' => $_POST['nom'] ?? '',
                'prenom' => $_POST['prenom'] ?? '',
                'email' => $_POST['email'] ?? '',
                'username' => $_POST['username'] ?? '',
                'mot_de_passe' => $_POST['password'] ?? '',
                'role' => $_POST['role'] ?? ''
            ];
            // dd($userData);
            $errors = $this->validateUserData(data: $userData,type: 'create');
            if (empty($errors)) {
                // Ajouter l'utilisateur
                $userId = User::add($userData);
                if ($userId) {
                    $_SESSION['success_message'] = "L'utilisateur a été ajouté avec succès.";
                    return $this->redirect('/users');
                    // exit;
                } else {
                    $error = "Une erreur s'est produite lors de l'ajout de l'utilisateur.";
                    return $this->view('users/create',[
                      'userTypes' => $userTypes,
                      'errors' => $errors
                    ],
                      'admin');
                }
            } else {
                // dd($errors);
                return $this->view('users/create',[
                  'userTypes' => $userTypes,
                  'errors' => $errors
                ],
                  'admin');         
          }
        } else {
            return $this->redirect('/users/create');
        }
    }
    
    /**
     * Afficher le formulaire de modification d'utilisateur
     */
    public function edit($id) 
    {
        // Vérifier les droits d'accès
        $this->checkAdmin();        
        // Récupérer l'utilisateur
        $user = User::getById($id);
        if (!$user) {
            $_SESSION['error_message'] = "L'utilisateur demandé n'existe pas.";
            return $this->redirect('/users');
        }
        
        // Récupérer les types d'utilisateurs pour le formulaire
        $userTypes = ['admin', 'magasinier', 'secretaire'];
        // Afficher la vue
        return $this->view(
          'users/edit',[
            'user' => $user,
            'userTypes' => $userTypes
          ],'admin');
    }
    
    /**
     * Traiter la modification d'un utilisateur
     */
    public function update($id) {
        // Vérifier les droits d'accès
        $this->checkAdmin();
        // Vérifier si l'utilisateur existe
        $user = User::getById($id);
        if (!$user) {
          flash("error","L'utilisateur demandé n'existe pas.");
          return $this->redirect('/users');
      }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'nom' => $_POST['nom'] ?? '',
                'prenom' => $_POST['prenom'] ?? '',
                'email' => $_POST['email'] ?? '',
                'username' => $_POST['username'] ?? '',
                'role' => $_POST['role'] ?? '',
                'statut' => $_POST['statut'] ?? 'actif'
            ];
            $userTypes = ['admin', 'magasinier', 'secretaire'];
            // Ajouter le mot de passe seulement s'il est fourni
            if (!empty($_POST['mot_de_passe'])) {
                $userData['mot_de_passe'] = $_POST['mot_de_passe'];
            }
            // Valide les données
            $errors = $this->validateUserData($userData, $id,'update');
            if (empty($errors)) {
                // Mettre à jour l'utilisateur
                $success = User::update($id, $userData);
                if ($success) {
                   flash("success","L'utilisateur a été mis à jour avec succès.");
                   return $this->redirect('/users');
                } else {
                  flash("error","Quelque chose s'est mal passé ");
                  return $this->view('users/edit/'.$id,[
                    'userTypes' => $userTypes,
                    'user' => $user,
                    'errors' => $errors
                  ],
                    'admin');
                }
            } else {
                // Afficher les erreurs
                $data = [
                  'userTypes' => $userTypes,
                  'errors' => $errors,
                  'user' => $user
                ];
                // dd($userData);
                return $this->view('users/edit',$data,'admin'); 
              }
        } else {
          return $this->redirect('/users/edit/'.$id);
        }
    }
    
    /**
     * Supprimer un utilisateur
     */
    public function delete($id) {
        // Vérifier les droits d'accès
        $this->checkAdmin();   
        // Vérifier si l'utilisateur existe
        $user = User::getById($id);
        if (!$user) {
            flash("error","L'utilisateur demandé n'existe pas");
            $this->redirect('/users');
        }
        
        // Empêcher la suppression de son propre compte
        if ($id == $_SESSION['user_id']) {
            flash("success","Vous ne pouvez pas supprimer votre propre compte.");
            $this->redirect('/users');
        }
        
        // Supprimer l'utilisateur
        $success = User::delete($id);
        if ($success) {
          flash("success","L'utilisateur a été supprimé avec succès.");
      } else {
          flash( "success","Une erreur s'est produite lors de la suppression de l'utilisateur.");
      }
      return $this->redirect('/users');
  }

  public function show($id)
  {
      // Check if the user is logged in
      if (!isset($_SESSION['user_id'])) {
          header('Location: /login');
          exit;
      }

      // Fetch the user data by ID
      $user = User::getById($id);
      // Check if the user exists
      if (!$user) {
          flash("error","Utilisateur non trouvé.");
      }
      // Pass user data to the view
      $this->view('users/show', ['user' => $user],'admin');
  }
  
  /**
   * Mettre à jour le profil de l'utilisateur connecté
   */
  public function profile() 
  {
    $this->checkAuth();
    $user = User::getById(Auth::Id());
    return $this->view('users/profile',['user' => $user],'admin');
  }

  public function updateProfile()
   {
      // Vérifier si l'utilisateur est connecté
      $this->checkAuth();
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
              if($userData['password'] != $_POST['confirm_password']) {
                $error[] = "Les mots de passe ne correspondent pas";
              }
          }
          
          // Valider les données
          $errors = $this->validateUserData($userData, Auth::Id(),'self-update');
          if (empty($errors)) {
              // Mettre à jour l'utilisateur
              $user = User::getById(Auth::Id());
              $userData['role'] = $user['role'];
              $userData['statut'] = $user['statut'];
              // dd($userData);
              $success = User::update(Auth::Id(), $userData);
              if ($success) {
                  // Mettre à jour le nom dans la session
                  $_SESSION['user_name'] = $userData['nom'];
                  // Rediriger vers le profil avec un message de succès
                  flash("success", "Votre profil a été mis à jour avec succès.");
              } else {
                  $error = "Une erreur s'est produite lors de la mise à jour de votre profil.";
              }
          } else {
              // Afficher les erreurs
              $error = implode('<br>', $errors);
          }
          
          // En cas d'erreur, réafficher le formulaire avec les données
          $user = $userData;
          return $this->view('users/profile', [
            'user' => $user,
            'errors' => $errors
        ],'admin');

      } else {
          // Rediriger vers le profil
          return $this->redirect('/users/profile');
      }
  }
  
  /**
   * Valider les données d'un utilisateur
   */
  private function validateUserData($data, $userId = null,$type = null) {
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
      if (!$userId && empty($data['mot_de_passe'])) {
          $errors[] = "Le mot de passe est obligatoire.";
      }
      
      // Vérifier que le type d'utilisateur est valide
      $validTypes = ['admin', 'magasinier', 'secretaire'];
      if(in_array($type, ['update','create'])) {
        if (empty($data['role']) || !in_array($data['role'], $validTypes)) {
          $errors[] = "Le type d'utilisateur n'est pas valide.";
        }
      }
     
      
      // Vérifier que le nom d'utilisateur est unique
      if (User::usernameExists($data['username'], $userId)) {
          $errors[] = "Ce nom d'utilisateur est déjà utilisé.";
      }
      
      // Vérifier que l'email est unique
      if (User::EmailExists($data['email'], $userId)) {
          $errors[] = "Cet email est déjà utilisé.";
      }
      
      return $errors;
  }

}
