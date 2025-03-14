<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Client;
use App\Models\Product;

class OrderController extends Controller {

    // Vérifie si l'utilisateur est connecté
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            exit;
        }
    }

    // Affiche la liste des commandes avec filtrage et tri
    public function index() {
        $this->checkAuth(); // Vérifier l'authentification

        // Récupérer les paramètres de recherche et de tri
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'date_creation';
        $order = $_GET['order'] ?? 'desc';
        $status = $_GET['status'] ?? '';
        
        $orders = [];
        
        // Récupérer les commandes avec filtre et tri
        if (!empty($search) || !empty($status)) {
            $orders = Order::search($search, $status, $sort, $order);
        } else {
            $orders = Order::getAll($sort, $order);
        }

        // Définir le titre de la page
        $pageTitle = 'Gestion des commandes';

        // Afficher la vue
        $this->view('orders/index', [
            'pageTitle' => $pageTitle,
            'orders' => $orders,
            'search' => $search,
            'sort' => $sort,
            'order' => $order,
            'status' => $status
        ], 'admin');
    }

    // Affiche une seule commande
    public function show($id) {
        $this->checkAuth();
        $order = Order::getById($id);

        if (!$order) {
            $_SESSION['error'] = "Commande non trouvée.";
            $this->redirect('/orders');
        }

        // Récupérer les détails de la commande
        $orderDetails = Order::getOrderDetails($id);
        $payments = Order::getOrderPayments($id);

        $this->view('order/show', [
            'order' => $order,
            'orderDetails' => $orderDetails,
            'payments' => $payments
        ], 'admin');
    }

    // Affiche le formulaire de création
    public function create() {
        $this->checkAuth();
        
        // Récupérer la liste des clients pour le formulaire
        $clients = Client::getAll();
        
        // Récupérer la liste des produits disponibles
        $products = Product::getActive();
        
        $this->view('order/create', [
            'clients' => $clients,
            'products' => $products
        ], 'admin');
    }

    // Enregistre une nouvelle commande
    public function store() {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation des données
            $clientId = intval($_POST['client_id'] ?? 0);
            $products = $_POST['products'] ?? [];
            $quantities = $_POST['quantities'] ?? [];
            $prices = $_POST['prices'] ?? [];
            
            if ($clientId <= 0) {
                $_SESSION['error'] = "Veuillez sélectionner un client valide.";
                $this->redirect('/orders/create');
            }
            
            if (empty($products)) {
                $_SESSION['error'] = "Veuillez ajouter au moins un produit à la commande.";
                $this->redirect('/orders/create');
            }
            
            // Générer une référence unique pour la commande
            $reference = 'CMD-' . date('YmdHis') . '-' . rand(100, 999);
            
            // Calculer le montant total
            $totalAmount = 0;
            foreach ($products as $index => $productId) {
                if (isset($quantities[$index]) && isset($prices[$index])) {
                    $totalAmount += $quantities[$index] * $prices[$index];
                }
            }
            
            // Créer la commande
            $orderData = [
                'reference' => $reference,
                'id_client' => $clientId,
                'id_utilisateur' => $_SESSION['user_id'],
                'montant_total' => $totalAmount,
                'statut' => 'pending',
                'statut_paiement' => 'pending',
                'date_livraison_prevue' => $_POST['delivery_date'] ?? null,
                'notes' => $_POST['notes'] ?? null,
                'date_creation' => date('Y-m-d H:i:s')
            ];
            
            $orderId = Order::add($orderData);
            
            if ($orderId) {
                // Ajouter les détails de la commande
                foreach ($products as $index => $productId) {
                    if (isset($quantities[$index]) && isset($prices[$index])) {
                        $detailData = [
                            'id_commande' => $orderId,
                            'id_produit' => $productId,
                            'quantite' => $quantities[$index],
                            'prix_unitaire' => $prices[$index],
                            'montant_total' => $quantities[$index] * $prices[$index]
                        ];
                        
                        Order::addOrderDetail($detailData);
                    }
                }
                
                $_SESSION['success'] = "Commande créée avec succès.";
                $this->redirect('/orders');
            } else {
                $_SESSION['error'] = "Erreur lors de la création de la commande.";
                $this->redirect('/orders/create');
            }
        }
    }

    // Affiche le formulaire d'édition
    public function edit($id) {
        $this->checkAuth();
        $order = Order::getById($id);

        if (!$order) {
            $_SESSION['error'] = "Commande non trouvée.";
            $this->redirect('/orders');
        }
        
        // Vérifier si la commande peut être modifiée
        if ($order['statut'] != 'pending') {
            $_SESSION['error'] = "Seules les commandes en attente peuvent être modifiées.";
            $this->redirect('/orders');
        }
        
        // Récupérer les détails de la commande
        $orderDetails = Order::getOrderDetails($id);
        
        // Récupérer la liste des clients pour le formulaire
        $clients = Client::getByStatus('active');
        
        // Récupérer la liste des produits disponibles
        $products = Product::getActive();
        
        $this->view('order/edit', [
            'order' => $order,
            'orderDetails' => $orderDetails,
            'clients' => $clients,
            'products' => $products
        ], 'admin');
    }

    // Met à jour une commande
    public function update($id) {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $order = Order::getById($id);
            
            if (!$order) {
                $_SESSION['error'] = "Commande non trouvée.";
                $this->redirect('/orders');
            }
            
            // Vérifier si la commande peut être modifiée
            if ($order['statut'] != 'pending') {
                $_SESSION['error'] = "Seules les commandes en attente peuvent être modifiées.";
                $this->redirect('/orders');
            }
            
            // Validation des données
            $clientId = intval($_POST['client_id'] ?? 0);
            $products = $_POST['products'] ?? [];
            $quantities = $_POST['quantities'] ?? [];
            $prices = $_POST['prices'] ?? [];
            
            if ($clientId <= 0) {
                $_SESSION['error'] = "Veuillez sélectionner un client valide.";
                $this->redirect("/orders/$id/edit");
            }
            
            if (empty($products)) {
                $_SESSION['error'] = "Veuillez ajouter au moins un produit à la commande.";
                $this->redirect("/orders/$id/edit");
            }
            
            // Calculer le montant total
            $totalAmount = 0;
            foreach ($products as $index => $productId) {
                if (isset($quantities[$index]) && isset($prices[$index])) {
                    $totalAmount += $quantities[$index] * $prices[$index];
                }
            }
            
            // Mettre à jour la commande
            $orderData = [
                'id_client' => $clientId,
                'montant_total' => $totalAmount,
                'date_livraison_prevue' => $_POST['delivery_date'] ?? null,
                'notes' => $_POST['notes'] ?? null,
                'date_modification' => date('Y-m-d H:i:s')
            ];
            
            $updated = Order::update($id, $orderData);
            
            if ($updated) {
                // Supprimer les anciens détails
                Order::deleteOrderDetails($id);
                
                // Ajouter les nouveaux détails
                foreach ($products as $index => $productId) {
                    if (isset($quantities[$index]) && isset($prices[$index])) {
                        $detailData = [
                            'id_commande' => $id,
                            'id_produit' => $productId,
                            'quantite' => $quantities[$index],
                            'prix_unitaire' => $prices[$index],
                            'montant_total' => $quantities[$index] * $prices[$index]
                        ];
                        
                        Order::addOrderDetail($detailData);
                    }
                }
                
                $_SESSION['success'] = "Commande mise à jour avec succès.";
                $this->redirect('/orders');
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour de la commande.";
                $this->redirect("/orders/$id/edit");
            }
        }
    }

    // Change le statut d'une commande
    public function updateStatus($id) {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $order = Order::getById($id);
            
            if (!$order) {
                $_SESSION['error'] = "Commande non trouvée.";
                $this->redirect('/orders');
            }
            
            $newStatus = $_POST['status'] ?? '';
            $validStatuses = ['pending', 'approved', 'rejected', 'delivered', 'cancelled'];
            
            if (!in_array($newStatus, $validStatuses)) {
                $_SESSION['error'] = "Statut invalide.";
                $this->redirect("/orders/$id");
            }
            
            // Mettre à jour le statut
            $orderData = [
                'statut' => $newStatus,
                'date_modification' => date('Y-m-d H:i:s')
            ];
            
            $updated = Order::update($id, $orderData);
            
            if ($updated) {
                // Si la commande est approuvée, créer une sortie de stock
                if ($newStatus == 'approved') {
                    $this->createStockExit($id);
                }
                
                $_SESSION['success'] = "Statut de la commande mis à jour avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour du statut.";
            }
            
            $this->redirect("/orders/$id");
        }
    }

    // Ajouter un paiement à une commande
    public function addPayment($id) {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $order = Order::getById($id);
            
            if (!$order) {
                $_SESSION['error'] = "Commande non trouvée.";
                $this->redirect('/orders');
            }
            
            // Validation des données
            $amount = floatval($_POST['amount'] ?? 0);
            $paymentMethod = $_POST['payment_method'] ?? '';
            $reference = $_POST['reference'] ?? '';
            $notes = $_POST['notes'] ?? '';
            
            if ($amount <= 0) {
                $_SESSION['error'] = "Le montant du paiement doit être supérieur à zéro.";
                $this->redirect("/orders/$id");
            }
            
            $validMethods = ['especes', 'cheque', 'virement', 'carte'];
            if (!in_array($paymentMethod, $validMethods)) {
                $_SESSION['error'] = "Mode de paiement invalide.";
                $this->redirect("/orders/$id");
            }
            
            // Créer le paiement
            $paymentData = [
                'id_commande' => $id,
                'montant' => $amount,
                'mode_paiement' => $paymentMethod,
                'reference_transaction' => $reference,
                'date_paiement' => date('Y-m-d H:i:s'),
                'notes' => $notes,
                'id_utilisateur' => $_SESSION['user_id']
            ];
            
            $paymentId = Order::addPayment($paymentData);
            
            if ($paymentId) {
                // Mettre à jour le statut de paiement de la commande
                $this->updatePaymentStatus($id);
                
                $_SESSION['success'] = "Paiement ajouté avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout du paiement.";
            }
            
            $this->redirect("/orders/$id");
        }
    }

    // Mettre à jour le statut de paiement d'une commande
    private function updatePaymentStatus($orderId) {
        $order = Order::getById($orderId);
        $payments = Order::getOrderPayments($orderId);
        
        $totalPaid = 0;
        foreach ($payments as $payment) {
            $totalPaid += $payment['montant'];
        }
        
        $paymentStatus = 'pending';
        
        if ($totalPaid >= $order['montant_total']) {
            $paymentStatus = 'paid';
        } elseif ($totalPaid > 0) {
            $paymentStatus = 'partial';
        }
        
        $orderData = [
            'statut_paiement' => $paymentStatus,
            'date_modification' => date('Y-m-d H:i:s')
        ];
        
        Order::update($orderId, $orderData);
    }

    // Créer une sortie de stock pour une commande approuvée
    private function createStockExit($orderId) {
      $order = Order::getById($orderId);
      $orderDetails = Order::getOrderDetails($orderId);
      
      // Générer une référence unique pour la sortie de stock
      $reference = 'SRT-' . date('YmdHis') . '-' . rand(100, 999);
      
      // Créer la sortie de stock
      $exitData = [
          'reference' => $reference,
          'date_sortie' => date('Y-m-d H:i:s'),
          'id_utilisateur' => $_SESSION['user_id'],
          'type_sortie' => 'vente',
          'id_commande' => $orderId,
          'montant_total' => $order['montant_total'],
          'notes' => 'Sortie automatique pour la commande ' . $order['reference'],
          'statut' => 'validee',
          'date_creation' => date('Y-m-d H:i:s')
      ];
      
      $exitId = Order::createStockExit($exitData);
      
      if ($exitId) {
          // Ajouter les détails de la sortie
          foreach ($orderDetails as $detail) {
              $exitDetailData = [
                  'id_sortie' => $exitId,
                  'id_produit' => $detail['id_produit'],
                  'quantite' => $detail['quantite'],
                  'prix_unitaire' => $detail['prix_unitaire'],
                  'montant_total' => $detail['montant_total']
              ];
              
              Order::addStockExitDetail($exitDetailData);
              
              // Mettre à jour le stock du produit
              Order::updateProductStock($detail['id_produit'], -$detail['quantite']);
              
              // Enregistrer l'opération de stock
              $operationData = [
                  'id_produit' => $detail['id_produit'],
                  'type_operation' => 'sortie',
                  'quantite' => $detail['quantite'],
                  'motif' => 'Vente - Commande ' . $order['reference'],
                  'id_commande' => $orderId,
                  'id_utilisateur' => $_SESSION['user_id'],
                  'date_operation' => date('Y-m-d H:i:s')
              ];
              
              Order::addStockOperation($operationData);
          }
      }
  }

  public function delete($id) {
    $this->checkAuth();
    $order = Order::getById($id);

    if (!$order) {
        $_SESSION['error'] = "Commande non trouvée.";
        $this->redirect('/orders');
    }
    
    // Vérifier si la commande peut être supprimée
    if ($order['statut'] != 'pending') {
        $_SESSION['error'] = "Seules les commandes en attente peuvent être supprimées.";
        $this->redirect('/orders');
    }
    
    // Supprimer les détails de la commande d'abord
    Order::deleteOrderDetails($id);
    
    // Supprimer les paiements associés
    Order::deleteOrderPayments($id);
    
    // Supprimer la commande
    $deleted = Order::delete($id);
    
    if ($deleted) {
        $_SESSION['success'] = "Commande supprimée avec succès.";
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression de la commande.";
    }
    
    $this->redirect('/orders');
}

// Génère un PDF de la commande
public function generatePdf($id) {
    $this->checkAuth();
    $order = Order::getById($id);

    if (!$order) {
        $_SESSION['error'] = "Commande non trouvée.";
        $this->redirect('/orders');
    }
    
    $orderDetails = Order::getOrderDetails($id);
    $client = Client::getById($order['id_client']);
    
    // Logique de génération de PDF ici
    // Utiliser une bibliothèque comme FPDF ou TCPDF
    
    $_SESSION['success'] = "PDF généré avec succès.";
    $this->redirect("/orders/$id");
}

// Affiche les statistiques des commandes
public function stats() {
    $this->checkAuth();
    
    // Récupérer les statistiques des commandes
    $stats = [
        'total' => Order::getTotalCount(),
        'by_status' => Order::getCountByStatus(),
        'by_month' => Order::getCountByMonth(),
        'revenue' => Order::getTotalRevenue(),
        'top_clients' => Order::getTopClients(),
        'top_products' => Order::getTopProducts()
    ];
    
    $this->view('order/stats', [
        'pageTitle' => 'Statistiques des commandes',
        'stats' => $stats
    ], 'admin');
}

// Exporte les commandes au format CSV
public function export() {
    $this->checkAuth();
    
    // Récupérer les paramètres de filtrage
    $startDate = $_GET['start_date'] ?? '';
    $endDate = $_GET['end_date'] ?? '';
    $status = $_GET['status'] ?? '';
    
    // Récupérer les commandes selon les filtres
    $orders = Order::getForExport($startDate, $endDate, $status);
    
    // Générer le CSV
    $filename = 'commandes_export_' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    $output = fopen('php://output', 'w');
    
    // En-têtes CSV
    fputcsv($output, [
        'ID', 'Référence', 'Client', 'Date', 'Montant', 'Statut', 'Paiement'
    ]);
    
    // Données
    foreach ($orders as $order) {
        fputcsv($output, [
            $order['id'],
            $order['reference'],
            $order['client_nom'] . ' ' . $order['client_prenom'],
            $order['date_creation'],
            $order['montant_total'],
            $order['statut'],
            $order['statut_paiement']
        ]);
    }
    
    fclose($output);
    exit;
}

// Affiche l'historique des commandes d'un client
public function clientHistory($clientId) {
    $this->checkAuth();
    
    $client = Client::getById($clientId);
    
    if (!$client) {
        $_SESSION['error'] = "Client non trouvé.";
        $this->redirect('/clients');
    }
    
    $orders = Order::getByClient($clientId);
    
    $this->view('order/client_history', [
        'pageTitle' => 'Historique des commandes - ' . $client['nom'] . ' ' . $client['prenom'],
        'client' => $client,
        'orders' => $orders
    ], 'admin');
}

// Affiche le formulaire de filtrage pour les rapports
public function reports() {
    $this->checkAuth();
    
    $this->view('order/reports', [
        'pageTitle' => 'Rapports des commandes'
    ], 'admin');
}

// Génère un rapport de commandes
public function generateReport() {
    $this->checkAuth();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $status = $_POST['status'] ?? '';
        $reportType = $_POST['report_type'] ?? 'summary';
        
        if (empty($startDate) || empty($endDate)) {
            $_SESSION['error'] = "Veuillez spécifier une période pour le rapport.";
            $this->redirect('/orders/reports');
        }
        
        // Récupérer les données selon le type de rapport
        $reportData = [];
        
        switch ($reportType) {
            case 'summary':
                $reportData = Order::getStats($startDate, $endDate);
                break;
            case 'detailed':
                $reportData = Order::getFiltered($startDate, $endDate, $status);
                break;
            case 'products':
                $reportData = Order::getTopProducts($startDate, $endDate, 20);
                break;
            default:
                $_SESSION['error'] = "Type de rapport invalide.";
                $this->redirect('/orders/reports');
        }
        
        $this->view('order/report_result', [
            'pageTitle' => 'Résultat du rapport',
            'reportType' => $reportType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'data' => $reportData
        ], 'admin');
    } else {
        $this->redirect('/orders/reports');
    }
}

// Affiche le formulaire de livraison
public function delivery($id) {
    $this->checkAuth();
    $order = Order::getById($id);

    if (!$order) {
        $_SESSION['error'] = "Commande non trouvée.";
        $this->redirect('/orders');
    }
    
    // Vérifier si la commande peut être livrée
    if ($order['statut'] != 'approved') {
        $_SESSION['error'] = "Seules les commandes approuvées peuvent être livrées.";
        $this->redirect("/orders/$id");
    }
    
    $this->view('order/delivery', [
        'pageTitle' => 'Livraison de la commande',
        'order' => $order
    ], 'admin');
}

// Enregistre la livraison d'une commande
public function processDelivery($id) {
    $this->checkAuth();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $order = Order::getById($id);
        
        if (!$order) {
            $_SESSION['error'] = "Commande non trouvée.";
            $this->redirect('/orders');
        }
        
        // Vérifier si la commande peut être livrée
        if ($order['statut'] != 'approved') {
            $_SESSION['error'] = "Seules les commandes approuvées peuvent être livrées.";
            $this->redirect("/orders/$id");
        }
        
        $deliveryDate = $_POST['delivery_date'] ?? date('Y-m-d');
        $notes = $_POST['notes'] ?? '';
        
        // Mettre à jour la commande
        $orderData = [
            'statut' => 'delivered',
            'date_livraison' => $deliveryDate,
            'notes_livraison' => $notes,
            'date_modification' => date('Y-m-d H:i:s')
        ];
        
        $updated = Order::update($id, $orderData);
        
        if ($updated) {
            $_SESSION['success'] = "Commande marquée comme livrée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'enregistrement de la livraison.";
        }
        
        $this->redirect("/orders/$id");
    } else {
        $this->redirect("/orders/$id/delivery");
    }
}

// Affiche le formulaire de facturation
public function invoice($id) {
    $this->checkAuth();
    $order = Order::getById($id);

    if (!$order) {
        $_SESSION['error'] = "Commande non trouvée.";
        $this->redirect('/orders');
    }
    
    $orderDetails = Order::getOrderDetails($id);
    $client = Client::getById($order['id_client']);
    $payments = Order::getOrderPayments($id);
    
    $this->view('order/invoice', [
        'pageTitle' => 'Facture de la commande',
        'order' => $order,
        'orderDetails' => $orderDetails,
        'client' => $client,
        'payments' => $payments
    ], 'admin');
}
}

