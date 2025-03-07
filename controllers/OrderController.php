<?php
/**
 * Contrôleur de Commande
 * Gère les opérations CRUD pour les commandes
 */
class OrderController {
    private $orderModel;
    private $productModel;
    private $userModel;
    private $authController;
    
    public function __construct() {
        $this->orderModel = new Order();
        $this->productModel = new Product();
        $this->userModel = new User();
        $this->authController = new AuthController();
    }
    
    /**
     * Afficher la liste des commandes
     */
    public function index() {
        // Vérifier si l'utilisateur est connecté
        $this->authController->checkLogin();
        
        // Récupérer les commandes selon le rôle de l'utilisateur
        if ($this->authController->isAdmin()) {
            // L'administrateur voit toutes les commandes
            $orders = $this->orderModel->getAll();
        } elseif ($this->authController->isStorekeeper()) {
            // Le magasinier voit les commandes approuvées et en attente de livraison
            $orders = $this->orderModel->getByStatus([ORDER_APPROVED, ORDER_PENDING]);
        } elseif ($this->authController->isSecretary()) {
            // La secrétaire voit les commandes qu'elle a créées
            $orders = $this->orderModel->getByUserId($_SESSION['user_id']);
        } else {
            // Rediriger vers la page d'accueil si le rôle n'est pas reconnu
            header('Location: ' . APP_URL);
            exit;
        }
        
        // Afficher la vue
        require_once BASE_PATH . '/views/orders/index.php';
    }
    
    /**
     * Afficher les détails d'une commande
     */
    public function show($id) {
        // Vérifier si l'utilisateur est connecté
        $this->authController->checkLogin();
        
        // Récupérer la commande
        $order = $this->orderModel->getById($id);
        
        if (!$order) {
            $_SESSION['error_message'] = "La commande demandée n'existe pas.";
            header('Location: ' . APP_URL . '/orders');
            exit;
        }
        
        // Vérifier les droits d'accès
        if (!$this->authController->isAdmin() && 
            !($this->authController->isStorekeeper() && in_array($order['statut'], [ORDER_APPROVED, ORDER_PENDING])) && 
            !($this->authController->isSecretary() && $order['user_id'] == $_SESSION['user_id'])) {
            $_SESSION['error_message'] = "Vous n'avez pas les droits pour accéder à cette commande.";
            header('Location: ' . APP_URL . '/orders');
            exit;
        }
        
        // Récupérer les détails de la commande
        $orderDetails = $this->orderModel->getOrderDetails($id);
        
        // Récupérer l'utilisateur qui a créé la commande
        $user = $this->userModel->getById($order['user_id']);
        
        // Afficher la vue
        require_once BASE_PATH . '/views/orders/show.php';
    }
    
    /**
     * Afficher le formulaire de création de commande
     */
    public function showCreateForm() {
        // Vérifier les droits d'accès (seule la secrétaire peut créer des commandes)
        $this->authController->checkAccess('secretary');
        
        // Récupérer les produits disponibles
        $products = $this->productModel->getAll();
        
        // Afficher la vue
        require_once BASE_PATH . '/views/orders/create.php';
    }
    
    /**
     * Traiter la création d'une commande
     */
    public function create() {
        // Vérifier les droits d'accès
        $this->authController->checkAccess('secretary');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $orderData = [
                'user_id' => $_SESSION['user_id'],
                'date_commande' => date('Y-m-d H:i:s'),
                'statut' => ORDER_PENDING,
                'commentaire' => $_POST['commentaire'] ?? '',
                'products' => []
            ];
            
            // Récupérer les produits de la commande
            $productIds = $_POST['product_id'] ?? [];
            $quantities = $_POST['quantity'] ?? [];
            
            // Vérifier qu'il y a au moins un produit
            if (empty($productIds)) {
                $error = "Veuillez ajouter au moins un produit à la commande.";
                $products = $this->productModel->getAll();
                require_once BASE_PATH . '/views/orders/create.php';
                return;
            }
            
            // Préparer les détails de la commande
            for ($i = 0; $i < count($productIds); $i++) {
                $productId = $productIds[$i];
                $quantity = $quantities[$i];
                
                // Vérifier que la quantité est valide
                if ($quantity <= 0) {
                    continue;
                }
                
                // Récupérer le produit
                $product = $this->productModel->getById($productId);
                
                if ($product) {
                    $orderData['products'][] = [
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $product['prix']
                    ];
                }
            }
            
            // Vérifier qu'il y a au moins un produit valide
            if (empty($orderData['products'])) {
                $error = "Veuillez ajouter au moins un produit valide à la commande.";
                $products = $this->productModel->getAll();
                require_once BASE_PATH . '/views/orders/create.php';
                return;
            }
            
            // Créer la commande
            $orderId = $this->orderModel->create($orderData);
            
            if ($orderId) {
                $_SESSION['success_message'] = "La commande a été créée avec succès.";
                header('Location: ' . APP_URL . '/orders');
                exit;
            } else {
                $error = "Une erreur s'est produite lors de la création de la commande.";
                $products = $this->productModel->getAll();
                require_once BASE_PATH . '/views/orders/create.php';
            }
        } else {
            // Rediriger vers le formulaire de création
            header('Location: ' . APP_URL . '/orders/create');
            exit;
        }
    }
    
    /**
     * Approuver une commande
     */
    public function approve($id) {
        // Vérifier les droits d'accès (seul l'admin peut approuver les commandes)
        $this->authController->checkAccess('admin');
        
        // Récupérer la commande
        $order = $this->orderModel->getById($id);
        
        if (!$order) {
            $_SESSION['error_message'] = "La commande demandée n'existe pas.";
            header('Location: ' . APP_URL . '/orders');
            exit;
        }
        
        // Vérifier que la commande est en attente
        if ($order['statut'] !== ORDER_PENDING) {
            $_SESSION['error_message'] = "Seules les commandes en attente peuvent être approuvées.";
            header('Location: ' . APP_URL . '/orders/' . $id);
            exit;
        }
        
        // Approuver la commande
        $success = $this->orderModel->updateStatus($id, ORDER_APPROVED);
        
        if ($success) {
            $_SESSION['success_message'] = "La commande a été approuvée avec succès.";
        } else {
            $_SESSION['error_message'] = "Une erreur s'est produite lors de l'approbation de la commande.";
        }
        
        header('Location: ' . APP_URL . '/orders/' . $id);
        exit;
    }
    
    /**
     * Rejeter une commande
     */
    public function reject($id) {
        // Vérifier les droits d'accès (seul l'admin peut rejeter les commandes)
        $this->authController->checkAccess('admin');
        
        // Récupérer la commande
        $order = $this->orderModel->getById($id);
        
        if (!$order) {
            $_SESSION['error_message'] = "La commande demandée n'existe pas.";
            header('Location: ' . APP_URL . '/orders');
            exit;
        }
        
        // Vérifier que la commande est en attente
        if ($order['statut'] !== ORDER_PENDING) {
            $_SESSION['error_message'] = "Seules les commandes en attente peuvent être rejetées.";
            header('Location: ' . APP_URL . '/orders/' . $id);
            exit;
        }
        
        // Rejeter la commande
        $success = $this->orderModel->updateStatus($id, ORDER_REJECTED);
        
        if ($success) {
            $_SESSION['success_message'] = "La commande a été rejetée avec succès.";
        } else {
            $_SESSION['error_message'] = "Une erreur s'est produite lors du rejet de la commande.";
        }
        
        header('Location: ' . APP_URL . '/orders/' . $id);
        exit;
    }
    
    /**
     * Marquer une commande comme livrée
     */
     /**
     * Marquer une commande comme livrée
     */
    public function deliver($id) {
      // Vérifier les droits d'accès (seul le magasinier peut livrer les commandes)
      $this->authController->checkAccess('storekeeper');
      
      // Récupérer la commande
      $order = $this->orderModel->getById($id);
      
      if (!$order) {
          $_SESSION['error_message'] = "La commande demandée n'existe pas.";
          header('Location: ' . APP_URL . '/orders');
          exit;
      }
      
      // Vérifier que la commande est approuvée
      if ($order['statut'] !== ORDER_APPROVED) {
          $_SESSION['error_message'] = "Seules les commandes approuvées peuvent être livrées.";
          header('Location: ' . APP_URL . '/orders/' . $id);
          exit;
      }
      
      // Récupérer les détails de la commande
      $orderDetails = $this->orderModel->getOrderDetails($id);
      
      // Vérifier la disponibilité des produits
      $outOfStock = [];
      foreach ($orderDetails as $detail) {
          if (!$this->productModel->isInStock($detail['produit_id'], $detail['quantite'])) {
              $product = $this->productModel->getById($detail['produit_id']);
              $outOfStock[] = $product['nom'];
          }
      }
      
      if (!empty($outOfStock)) {
          $_SESSION['error_message'] = "Certains produits ne sont pas disponibles en stock : " . implode(', ', $outOfStock);
          header('Location: ' . APP_URL . '/orders/' . $id);
          exit;
      }
      
      // Mettre à jour le stock des produits
      foreach ($orderDetails as $detail) {
          $this->productModel->updateStock($detail['produit_id'], -$detail['quantite']);
      }
      
      // Marquer la commande comme livrée
      $success = $this->orderModel->updateStatus($id, ORDER_DELIVERED);
      
      if ($success) {
          // Enregistrer la date de livraison
          $this->orderModel->updateDeliveryDate($id, date('Y-m-d H:i:s'));
          
          $_SESSION['success_message'] = "La commande a été marquée comme livrée avec succès.";
      } else {
          $_SESSION['error_message'] = "Une erreur s'est produite lors de la livraison de la commande.";
      }
      
      header('Location: ' . APP_URL . '/orders/' . $id);
      exit;
  }
  
  /**
   * Annuler une commande
   */
  public function cancel($id) {
      // Vérifier si l'utilisateur est connecté
      $this->authController->checkLogin();
      
      // Récupérer la commande
      $order = $this->orderModel->getById($id);
      
      if (!$order) {
          $_SESSION['error_message'] = "La commande demandée n'existe pas.";
          header('Location: ' . APP_URL . '/orders');
          exit;
      }
      
      // Vérifier les droits d'accès
      $canCancel = false;
      
      if ($this->authController->isAdmin()) {
          // L'admin peut annuler n'importe quelle commande non livrée
          $canCancel = $order['statut'] !== ORDER_DELIVERED;
      } elseif ($this->authController->isSecretary() && $order['user_id'] == $_SESSION['user_id']) {
          // La secrétaire peut annuler ses propres commandes en attente
          $canCancel = $order['statut'] === ORDER_PENDING;
      }
      
      if (!$canCancel) {
          $_SESSION['error_message'] = "Vous n'avez pas les droits pour annuler cette commande.";
          header('Location: ' . APP_URL . '/orders/' . $id);
          exit;
      }
      
      // Annuler la commande
      $success = $this->orderModel->updateStatus($id, ORDER_CANCELLED);
      
      if ($success) {
          $_SESSION['success_message'] = "La commande a été annulée avec succès.";
      } else {
          $_SESSION['error_message'] = "Une erreur s'est produite lors de l'annulation de la commande.";
      }
      
      header('Location: ' . APP_URL . '/orders/' . $id);
      exit;
  }
  
  /**
   * Générer un rapport des commandes
  */
  public function report() {
    // ... (suite de la méthode précédente)
    
    // Calculer les statistiques
    $stats = [
        'total' => count($orders),
        'total_amount' => 0,
        'by_status' => [
            ORDER_PENDING => 0,
            ORDER_APPROVED => 0,
            ORDER_REJECTED => 0,
            ORDER_DELIVERED => 0,
            ORDER_CANCELLED => 0
        ]
    ];
    
    foreach ($orders as $order) {
        $stats['total_amount'] += $order['montant_total'];
        $stats['by_status'][$order['statut']]++;
    }
    
    // Récupérer les produits les plus commandés
    $topProducts = $this->orderModel->getTopProducts($startDate, $endDate, 10);
    
    // Afficher la vue
    require_once BASE_PATH . '/views/orders/report.php';
}

/**
 * Exporter les commandes au format CSV
 */
public function export() {
    // Vérifier les droits d'accès (seul l'admin peut exporter des données)
    $this->authController->checkAccess('admin');
    
    // Récupérer les paramètres d'exportation
    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    $status = $_GET['status'] ?? '';
    
    // Récupérer les commandes selon les filtres
    $orders = $this->orderModel->getFiltered($startDate, $endDate, $status);
    
    // Préparer l'en-tête du fichier CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="commandes_' . date('Y-m-d') . '.csv"');
    
    // Créer le flux de sortie
    $output = fopen('php://output', 'w');
    
    // Ajouter l'en-tête UTF-8 BOM
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Écrire l'en-tête des colonnes
    fputcsv($output, [
        'ID',
        'Date',
        'Client',
        'Statut',
        'Montant Total',
        'Produits',
        'Commentaire'
    ]);
    
    // Écrire les données
    foreach ($orders as $order) {
        // Récupérer les détails de la commande
        $orderDetails = $this->orderModel->getOrderDetails($order['id']);
        
        // Formater la liste des produits
        $products = [];
        foreach ($orderDetails as $detail) {
            $product = $this->productModel->getById($detail['produit_id']);
            $products[] = $product['nom'] . ' (x' . $detail['quantite'] . ')';
        }
        
        // Récupérer l'utilisateur qui a créé la commande
        $user = $this->userModel->getById($order['user_id']);
        
        // Écrire la ligne
        fputcsv($output, [
            $order['id'],
            formatDate($order['date_commande']),
            $user['nom'] . ' ' . $user['prenom'],
            $order['statut'],
            formatPrice($order['montant_total']),
            implode(', ', $products),
            $order['commentaire']
        ]);
    }
    
    // Fermer le flux
    fclose($output);
    exit;
}

/**
 * Afficher le formulaire de recherche de commandes
 */
public function search() {
    // Vérifier si l'utilisateur est connecté
    $this->authController->isLoggedIn();
    
    // Récupérer les paramètres de recherche
    $keyword = $_GET['keyword'] ?? '';
    $status = $_GET['status'] ?? '';
    $startDate = $_GET['start_date'] ?? '';
    $endDate = $_GET['end_date'] ?? '';
    
    // Effectuer la recherche
    $orders = $this->orderModel->search($keyword, $status, $startDate, $endDate);
    
    // Afficher la vue
    require_once BASE_PATH . '/views/orders/search.php';
}
}
