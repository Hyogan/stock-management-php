<?php
/**
 * Contrôleur des Clients
 * Gère toutes les opérations liées aux clients
 */
class ClientController {
    private $clientModel;
    private $authController;
    
    public function __construct() {
        $this->clientModel = new Client();
        $this->authController = new AuthController();
    }
    
    /**
     * Afficher la liste des clients
     */
    public function index() {
        // Vérifier les droits d'accès
        $this->authController->checkAccess('any');
        
        // Récupérer tous les clients
        $clients = $this->clientModel->getAll();
        
        // Définir le titre de la page
        $pageTitle = 'Gestion des Clients';
        
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
     * Afficher le formulaire d'ajout de client
     */
    public function create() {
        // Vérifier les droits d'accès
        $this->authController->checkAccess('any');
        
        // Définir le titre de la page
        $pageTitle = 'Ajouter un Client';
        
        // Afficher la vue
        require_once BASE_PATH . '/views/layouts/header.php';
        require_once BASE_PATH . '/views/clients/create.php';
        require_once BASE_PATH . '/views/layouts/footer.php';
    }
    
    /**
     * Traiter l'ajout d'un client
     */
    public function store() {
        // Vérifier les droits d'accès
        $this->authController->checkAccess('any');
        
        // Vérifier si le formulaire a été soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $clientData = [
                'nom' => $_POST['nom'] ?? '',
                'prenom' => $_POST['prenom'] ?? '',
                'telephone' => $_POST['telephone'] ?? '',
                'email' => $_POST['email'] ?? '',
                'adresse' => $_POST['adresse'] ?? '',
                'ville' => $_POST['ville'] ?? '',
                'code_postal' => $_POST['code_postal'] ?? '',
                'pays' => $_POST['pays'] ?? 'Sénégal',
                'type' => $_POST['type'] ?? 'particulier'
            ];
            
            // Valider les données
            $errors = [];
            
            if (empty($clientData['nom'])) {
                $errors[] = "Le nom du client est requis.";
            }
            
            if (empty($clientData['telephone'])) {
                $errors[] = "Le numéro de téléphone est requis.";
            }
            
            if (!empty($clientData['email']) && !filter_var($clientData['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'adresse e-mail n'est pas valide.";
            }
            
            // S'il y a des erreurs, afficher le formulaire avec les erreurs
            if (!empty($errors)) {
                $error = implode('<br>', $errors);
                $pageTitle = 'Ajouter un Client';
                
                require_once BASE_PATH . '/views/layouts/header.php';
                require_once BASE_PATH . '/views/clients/create.php';
                require_once BASE_PATH . '/views/layouts/footer.php';
                return;
            }
            
            try {
                // Ajouter le client
                $clientId = $this->clientModel->create($clientData);
                
                // Rediriger vers la liste des clients avec un message de succès
                $_SESSION['success'] = "Le client a été ajouté avec succès.";
                header('Location: ' . APP_URL . '/clients');
                exit;
            } catch (Exception $e) {
                // Afficher le formulaire avec l'erreur
                $error = "Erreur lors de l'ajout du client : " . $e->getMessage();
                $pageTitle = 'Ajouter un Client';
                
                require_once BASE_PATH . '/views/layouts/header.php';
                require_once BASE_PATH . '/views/clients/create.php';
                require_once BASE_PATH . '/views/layouts/footer.php';
            }
        } else {
            // Rediriger vers le formulaire d'ajout
            header('Location: ' . APP_URL . '/clients/create');
            exit;
        }
    }
    
    /**
     * Afficher les détails d'un client
     */
    public function show($id) {
        // Vérifier les droits d'accès
        $this->authController->checkAccess('any');
        
        // Récupérer le client
        $client = $this->clientModel->getById($id);
        
        if (!$client) {
            $_SESSION['error'] = "Le client demandé n'existe pas.";
            header('Location: ' . APP_URL . '/clients');
            exit;
        }
        
        // Récupérer les commandes du client
        $orders = $this->clientModel->getOrders($id);
        
        // Définir le titre de la page
        $pageTitle = 'Détails du Client: ' . $client['nom'] . ' ' . $client['prenom'];
        
        // Définir les boutons d'action
        $actionButtons = '
            <a href="' . APP_URL . '/clients/edit/' . $id . '" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Modifier
            </a>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="bi bi-trash"></i> Supprimer
            </button>
            <a href="' . APP_URL . '/orders/create/' . $id . '" class="btn btn-primary">
                <i class="bi bi-plus"></i> Nouvelle commande
            </a>
        ';
        
        // Afficher la vue
        require_once BASE_PATH . '/views/layouts/header.php';
        require_once BASE_PATH . '/views/clients/show.php';
        require_once BASE_PATH . '/views/layouts/footer.php';
    }
    
    /**
     * Afficher le formulaire de modification d'un client
     */
    public function edit($id) {
        // Vérifier les droits d'accès
        $this->authController->checkAccess('any');
        
        // Récupérer le client
        $client = $this->clientModel->getById($id);
        
        if (!$client) {
            $_SESSION['error'] = "Le client demandé n'existe pas.";
            header('Location: ' . APP_URL . '/clients');
            exit;
        }
        
        // Définir le titre de la page
        $pageTitle = 'Modifier le Client: ' . $client['nom'] . ' ' . $client['prenom'];
        
        // Afficher la vue
        require_once BASE_PATH . '/views/layouts/header.php';
        require_once BASE_PATH . '/views/clients/edit.php';
        require_once BASE_PATH . '/views/layouts/footer.php';
    }
    
    /**
     * Traiter la modification d'un client
     */
    public function update($id) {
        // Vérifier les droits d'accès
        $this->authController->checkAccess('any');
        
        // Récupérer le client
        $client = $this->clientModel->getById($id);
        
        if (!$client) {
            $_SESSION['error'] = "Le client demandé n'existe pas.";
            header('Location: ' . APP_URL . '/clients');
            exit;
        }
        
        // Vérifier si le formulaire a été soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $clientData = [
                'nom' => $_POST['nom'] ?? '',
                'prenom' => $_POST['prenom'] ?? '',
                'telephone' => $_POST['telephone'] ?? '',
                'email' => $_POST['email'] ?? '',
                'adresse' => $_POST['adresse'] ?? '',
                'ville' => $_POST['ville'] ?? '',
                'code_postal' => $_POST['code_postal'] ?? '',
                'pays' => $_POST['pays'] ?? 'Sénégal',
                'type' => $_POST['type'] ?? 'particulier'
            ];
            
            // Valider les données
            $errors = [];
            
            if (empty($clientData['nom'])) {
                $errors[] = "Le nom du client est requis.";
            }
            
            if (empty($clientData['telephone'])) {
                $errors[] = "Le numéro de téléphone est requis.";
            }
            
            if (!empty($clientData['email']) && !filter_var($clientData['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'adresse e-mail n'est pas valide.";
            }
            
            // S'il y a des erreurs, afficher le formulaire avec les erreurs
            if (!empty($errors)) {
                $error = implode('<br>', $errors);
                $pageTitle = 'Modifier le Client: ' . $client['nom'] . ' ' . $client['prenom'];
                
                require_once BASE_PATH . '/views/layouts/header.php';
                require_once BASE_PATH . '/views/clients/edit.php';
                require_once BASE_PATH . '/views/layouts/footer.php';
                return;
            }
            
            try {
                // Mettre à jour le client
                $this->clientModel->update($id, $clientData);
                
                // Rediriger vers la liste des clients avec un message de succès
                $_SESSION['success'] = "Le client a été mis à jour avec succès.";
                header('Location: ' . APP_URL . '/clients');
                exit;
            } catch (Exception $e) {
                // Afficher le formulaire avec l'erreur
                $error = "Erreur lors de la mise à jour du client : " . $e->getMessage();
                $pageTitle = 'Modifier le Client: ' . $client['nom'] . ' ' . $client['prenom'];
                
                require_once BASE_PATH . '/views/layouts/header.php';
                require_once BASE_PATH . '/views/clients/edit.php';
                require_once BASE_PATH . '/views/layouts/footer.php';
            }
        } else {
            // Rediriger vers le formulaire de modification
            header('Location: ' . APP_URL . '/clients/edit/' . $id);
            exit;
        }
    }
    
    /**
     * Traiter la suppression d'un client
     */
    public function delete($id) {
        // Vérifier les droits d'accès
        $this->authController->checkAccess('any');
        
        // Vérifier si le formulaire a été soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Vérifier si le client a des commandes
                $orders = $this->clientModel->getOrders($id);
                
                if (!empty($orders)) {
                    $_SESSION['error'] = "Impossible de supprimer ce client car il a des commandes associées.";
                    header('Location: ' . APP_URL . '/clients/show/' . $id);
                    exit;
                }
                
                // Supprimer le client
                $this->clientModel->delete($id);
                
                // Rediriger vers la liste des clients avec un message de succès
                $_SESSION['success'] = "Le client a été supprimé avec succès.";
                header('Location: ' . APP_URL . '/clients');
                exit;
            } catch (Exception $e) {
                // Rediriger vers la liste des clients avec un message d'erreur
                $_SESSION['error'] = "Erreur lors de la suppression du client : " . $e->getMessage();
                header('Location: ' . APP_URL . '/clients');
                exit;
            }
        } else {
            // Rediriger vers la liste des clients
            header('Location: ' . APP_URL . '/clients');
            exit;
        }
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
