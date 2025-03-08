<?php
/**
 * Contrôleur des Clients
 * Gère toutes les opérations liées aux clients
 */
namespace Controllers;

use App\Core\Controller;
use App\Models\Order;
use AuthController;
use Client;

class ClientController extends Controller {
    private $clientModel;
    private $authController;
    
    public function __construct() {
        // $this->clientModel = new Client();
        // $this->authController = new AuthController();
    }
    
    /**
     * Afficher la liste des clients
     */
    public function index() {
        // Vérifier les droits d'accès
        $this->authController->checkAccess('any');
        // Récupérer tous les clients
        $clients = Client::getAll();
        // Définir le titre de la page
        $pageTitle = 'Gestion des Clients';
        // Définir les boutons d'action
        $actionButtons = '
            <a href="' . APP_URL . '/clients/create" class="btn btn-primary">
                <i class="bi bi-plus"></i> Ajouter un client
            </a>
        ';
        // Afficher la vue
        $this->view('clients/index', [
            'clients' => $clients,
            'pageTitle' => $pageTitle,
            'actionButtons' => $actionButtons
        ]);
       }
    /**
     * Afficher le formulaire d'ajout de client
     */
    public function create() {
        // Vérifier les droits d'accès
        if(!$this->authController->checkAccess('any')) {
            return $this->view('login');
        }
        // Définir le titre de la page
        $pageTitle = 'Ajouter un client';
        
        // Afficher la vue
        $this->view('clients/create', [
            'pageTitle' => $pageTitle
        ]);
    }
    
    /**
     * Traiter l'ajout d'un client
     */
    public function store() {
        // Vérifier les droits d'accès
        // $this->authController->checkAccess('any');
         // Vérifier si l'utilisateur est connecté
         if (!isset($_SESSION['user_id'])) {
          $this->redirect('/login');
          exit;
      }
      
      // Vérifier si le formulaire a été soumis
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
          $this->redirect('/clients');
          exit;
      }
      
      // Récupérer les données du formulaire
      $data = [
          'nom' => $_POST['nom'] ?? '',
          'prenom' => $_POST['prenom'] ?? '',
          'email' => $_POST['email'] ?? '',
          'telephone' => $_POST['telephone'] ?? '',
          'adresse' => $_POST['adresse'] ?? '',
          'ville' => $_POST['ville'] ?? '',
          'code_postal' => $_POST['code_postal'] ?? '',
          'pays' => $_POST['pays'] ?? ''
      ];
      
      // Valider les données
      $errors = [];
      
      if (empty($data['nom'])) {
          $errors['nom'] = 'Le nom est obligatoire';
      }
      
      if (empty($data['email'])) {
          $errors['email'] = 'L\'email est obligatoire';
      } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
          $errors['email'] = 'L\'email n\'est pas valide';
      }
      
      // S'il y a des erreurs, afficher le formulaire avec les erreurs
      if (!empty($errors)) {
          $this->view('clients/create', [
              'pageTitle' => 'Ajouter un client',
              'data' => $data,
              'errors' => $errors
          ]);
          return;
      }
      
      // Ajouter le client
      $clientId = Client::create($data);
      
      // Rediriger vers la liste des clients
      $_SESSION['success'] = 'Le client a été ajouté avec succès';
      $this->redirect('/clients');
  }
    
    /**
     * Afficher les détails d'un client
     */
    public function show($id) {
        // Vérifier les droits d'accès
        $this->authController->checkAccess('any');
        // Récupérer le client
        $client = Client::getById($id);
        
        // Vérifier si le client existe
        if (!$client) {
            $_SESSION['error'] = 'Le client n\'existe pas';
            $this->redirect('/clients');
            exit;
        }
        
        // Récupérer les commandes du client
        $orders = Client::getOrders($id, 5);
        
        // Définir le titre de la page
        $pageTitle = 'Détails du client';
        
        // Afficher la vue
        $this->view('clients/show', [
            'client' => $client,
            'orders' => $orders,
            'pageTitle' => $pageTitle
        ]);
    }
    
    /**
     * Afficher le formulaire de modification d'un client
     */
    public function edit($id) {
        // Vérifier les droits d'accès
        if(!$this->authController->checkAccess('any')){
          return $this->view('login');
        }
        
        // Récupérer le client
        $client = $this->clientModel->getById($id);
        
        if (!$client) {
            $_SESSION['error'] = "Le client demandé n'existe pas.";
            header('Location: ' . APP_URL . '/clients');
            exit;
        }
        $client = Client::getById($id);
        // Vérifier si le client existe
        if (!$client) {
            $_SESSION['error'] = 'Le client n\'existe pas';
            $this->redirect('/clients');
            exit;
        }
        
        // Définir le titre de la page
        $pageTitle = 'Modifier le client';
        
        // Afficher la vue
        $this->view('clients/edit', [
            'client' => $client,
            'pageTitle' => $pageTitle
        ]);
    }  
    /**
     * Traiter la modification d'un client
     */
    public function update($id) {
        // Vérifier les droits d'accès
        if(!$this->authController->checkAccess('any')) {
            return $this->view('login');
        } 
        // Récupérer le client
        $client = $this->clientModel->getById($id);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
          $this->redirect('/clients');
          exit;
      }
      
      // Récupérer le client
      $client = Client::getById($id);
      
      // Vérifier si le client existe
      if (!$client) {
          $_SESSION['error'] = 'Le client n\'existe pas';
          $this->redirect('/clients');
          exit;
      }
      
      // Récupérer les données du formulaire
      $data = [
          'nom' => $_POST['nom'] ?? '',
          'prenom' => $_POST['prenom'] ?? '',
          'email' => $_POST['email'] ?? '',
          'telephone' => $_POST['telephone'] ?? '',
          'adresse' => $_POST['adresse'] ?? '',
          'ville' => $_POST['ville'] ?? '',
          'code_postal' => $_POST['code_postal'] ?? '',
          'pays' => $_POST['pays'] ?? ''
      ];
      // Valider les données
      $errors = [];
      if (empty($data['nom'])) {
          $errors['nom'] = 'Le nom est obligatoire';
      }
      if (empty($data['email'])) {
          $errors['email'] = 'L\'email est obligatoire';
      } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
          $errors['email'] = 'L\'email n\'est pas valide';
      }
      // S'il y a des erreurs, afficher le formulaire avec les erreurs
      if (!empty($errors)) {
          $this->view('clients/edit', [
              'pageTitle' => 'Modifier le client',
              'client' => $client,
              'errors' => $errors
          ]);
          return;
      }
      // Mettre à jour le client
      Client::update($id, $data);
      // Rediriger vers la liste des clients
      $_SESSION['success'] = 'Le client a été modifié avec succès';
      $this->redirect('/clients');
    }
    
    /**
     * Traiter la suppression d'un client
     */
    public function delete($id) {
        // Vérifier les droits d'accès
        if(!$this->authController->isLoggedIn()){
          return $this->view('login');
        }
        $client = Client::getById($id);
        // Vérifier si le client existe
        if (!$client) {
            $_SESSION['error'] = 'Le client n\'existe pas';
            $this->redirect('/clients');
            exit;
        }
        
        // Supprimer le client
        Client::delete($id);
        
        // Rediriger vers la liste des clients
        $_SESSION['success'] = 'Le client a été supprimé avec succès';
        $this->redirect('/clients');
      }    
    /**
     * Rechercher des clients
     */
    public function search() {
        // Vérifier les droits d'accès
        $this->authController->checkAccess('any');
        // Récupérer le terme de recherche
        $query = $_GET['q'] ?? '';
        
        if (empty($query)) {
            header('Location: ' . APP_URL . '/clients');
            exit;
        }
        
        // Rechercher les clients
        $clients = $this->clientModel->search($query);
        
        // Définir le titre de la page
        $pageTitle = 'Résultats de recherche: ' . $query;
        
        // Définir les boutons d'action
        $actionButtons = '
            <a href="' . APP_URL . '/clients/create" class="btn btn-primary">
                <i class="bi bi-plus"></i> Ajouter un client
            </a>
        ';
        
        // Afficher la vue
        require_once BASE_PATH . '/views/layouts/header.php';
        require_once BASE_PATH . '/views/clients/index.php';
        require_once BASE_PATH . '/views/layouts/footer.php';
    }
    
    /**
     * Exporter la liste des clients au format CSV
     */
    public function exportCsv() {
        // Vérifier les droits d'accès
        $this->authController->checkAccess('any');
        
        // Récupérer tous les clients
        $clients = $this->clientModel->getAll();
        
        // Définir les en-têtes pour le téléchargement
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=clients_' . date('Y-m-d') . '.csv');
        
        // Créer le flux de sortie
        $output = fopen('php://output', 'w');
        
        // Ajouter l'en-tête UTF-8 BOM pour Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Ajouter les en-têtes de colonnes
        fputcsv($output, ['ID', 'Nom', 'Prénom', 'Téléphone', 'Email', 'Adresse', 'Ville', 'Code Postal', 'Pays', 'Type', 'Date de création']);
        
        // Ajouter les données
        foreach ($clients as $client) {
            fputcsv($output, [
                $client['id_client'],
                $client['nom'],
                $client['prenom'],
                $client['telephone'],
                $client['email'],
                $client['adresse'],
                $client['ville'],
                $client['code_postal'],
                $client['pays'],
                $client['type'],
                $client['date_creation']
            ]);
        }
        
        fclose($output);
        exit;
    }
}
