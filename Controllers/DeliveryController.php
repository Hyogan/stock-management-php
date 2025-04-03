<?php
namespace App\Controllers;
use App\Models\User;
use App\Core\Controller;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Client;
use App\Models\Product;
use App\Utils\Auth;
use Exception;
  class DeliveryController extends Controller
  {
      public function __construct()
      {
          // Check if user is logged in
          if (!isset($_SESSION['user_id'])) {
            $this->redirect('/auth/login');
          }
      }

      public function index()
      {
          // Get filter parameters
          $search = $_GET['search'] ?? '';
          $status = $_GET['status'] ?? '';
          $dateFrom = $_GET['date_from'] ?? '';
          $dateTo = $_GET['date_to'] ?? '';
          $sort = $_GET['sort'] ?? 'date_creation';
          $order = $_GET['order'] ?? 'desc';
          
          // Get deliveries with filters
          // $deliveries = Delivery::getAll($search, $status, $dateFrom, $dateTo, $sort, $order);
          $deliveries = Delivery::getAll();
          
          $data = [
              'deliveries' => $deliveries,
              'search' => $search,
              'status' => $status,
              'date_from' => $dateFrom,
              'date_to' => $dateTo,
              'sort' => $sort,
              'order' => $order,
              'authController' => new AuthController(),
              'currentPage' => 'deliveries'
          ];
          
          $this->view('deliveries/index', $data);
      }
      
      public function create()
      {
          // Check if user has permission
          if (!Auth::isAdmin() && !Auth::isSecretary()) {
              $_SESSION['error_message'] = "Vous n'avez pas l'autorisation d'accéder à cette page.";
              $this->redirect('/dashboard');
          }
          
          if ($_SERVER['REQUEST_METHOD'] === 'POST') {
              // Process form
              $data = [
                  'order_id' => trim($_POST['order_id']),
                  'delivery_date' => trim($_POST['delivery_date']),
                  'notes' => trim($_POST['notes'] ?? ''),
                  'status' => 'pending',
                  'user_id' => $_SESSION['user_id'],
                  'order_id_err' => '',
                  'delivery_date_err' => ''
              ];
              
              // Validate order
              if (empty($data['order_id'])) {
                  $data['order_id_err'] = 'Veuillez sélectionner une commande';
              } else {
                  // Check if order exists and is approved
                  $order = Order::getById($data['order_id']);
                  if (!$order) {
                      $data['order_id_err'] = 'Commande introuvable';
                  } elseif ($order['status'] !== 'approved') {
                      $data['order_id_err'] = 'Seules les commandes approuvées peuvent avoir un bon de livraison';
                  }
              }
              
              // Validate delivery date
              if (empty($data['delivery_date'])) {
                  $data['delivery_date_err'] = 'Veuillez sélectionner une date de livraison';
              }
              
              // If no errors, create delivery
              if (empty($data['order_id_err']) && empty($data['delivery_date_err'])) {
                  $deliveryId = Delivery::create($data);
                  
                  if ($deliveryId) {
                      $_SESSION['success_message'] = 'Bon de livraison créé avec succès';
                      $this->redirect('/deliveries/show/' . $deliveryId);
                  } else {
                      $_SESSION['error_message'] = 'Une erreur est survenue lors de la création du bon de livraison';
                      $this->view('deliveries/create', $data);
                  }
              } else {
                  // Load view with errors
                  $this->view('deliveries/create', $data);
              }
          } else {
              // Get approved orders for form
              $approvedOrders = Order::getByStatus('approved');
              
              $data = [
                  'approvedOrders' => $approvedOrders,
                  'authController' => new AuthController(),
                  'currentPage' => 'deliveries'
              ];
              
              $this->view('deliveries/create', $data);
          }
      }
      
      public function createFromOrder($orderId)
      {
          // Check if order exists and is approved
          $order = Order::getById($orderId);
          if (!$order || $order['status'] !== 'approved') {
              return false;
          }
          
          // Create delivery data
          $data = [
              'order_id' => $orderId,
              'delivery_date' => date('Y-m-d', strtotime('+3 days')), // Default delivery date: 3 days from now
              'notes' => 'Généré automatiquement depuis la commande #' . $orderId,
              'status' => 'pending',
              'user_id' => $_SESSION['user_id']
          ];
          
          // Create delivery
          return Delivery::create($data);
      }
      
      public function show($id)
      {
          $delivery = Delivery::getById($id);
          
          if (!$delivery) {
              $_SESSION['error_message'] = 'Bon de livraison introuvable';
              $this->redirect('/deliveries');
          }
          
          $order = Order::getById($delivery['order_id']);
          $orderItems = Order::getOrderItems($delivery['order_id']);
          $client = Client::getById($order['client_id']);
          
          $data = [
              'delivery' => $delivery,
              'order' => $order,
              'orderItems' => $orderItems,
              'client' => $client,
              'authController' => new AuthController(),
              'currentPage' => 'deliveries'
          ];
          
          $this->view('deliveries/show', $data);
      }
      
      public function edit($id)
      {
          // Check if user has permission
          if (!Auth::isAdmin() && !Auth::isSecretary()) {
              $_SESSION['error_message'] = "Vous n'avez pas l'autorisation d'accéder à cette page.";
              $this->redirect('/dashboard');
          }
          
          $delivery = Delivery::getById($id);
          
          if (!$delivery) {
              $_SESSION['error_message'] = 'Bon de livraison introuvable';
              $this->redirect('/deliveries');
          }
          
          if ($_SERVER['REQUEST_METHOD'] === 'POST') {
              // Process form
              $data = [
                  'id' => $id,
                  'delivery_date' => trim($_POST['delivery_date']),
                  'notes' => trim($_POST['notes'] ?? ''),
                  'status' => trim($_POST['status']),
                  'delivery_date_err' => ''
              ];
              
              // Validate delivery date
              if (empty($data['delivery_date'])) {
                  $data['delivery_date_err'] = 'Veuillez sélectionner une date de livraison';
              }
              
              // If no errors, update delivery
              if (empty($data['delivery_date_err'])) {
                  if (Delivery::update($id,$data)) {
                      $_SESSION['success_message'] = 'Bon de livraison mis à jour avec succès';
                      $this->redirect('/deliveries/show/' . $id);
                  } else {
                      $_SESSION['error_message'] = 'Une erreur est survenue lors de la mise à jour du bon de livraison';
                      $this->view('deliveries/edit', $data);
                  }
              } else {
                  // Load view with errors
                  $this->view('deliveries/edit', $data);
              }
          } else {
              $order = Order::getById($delivery['order_id']);
              
              $data = [
                  'delivery' => $delivery,
                  'order' => $order,
                  'authController' => new AuthController(),
                  'currentPage' => 'deliveries'
              ];
              
              $this->view('deliveries/edit', $data);
          }
      }
      
      public function delete($id)
      {
          // Check if user has permission
          if (!Auth::isAdmin()) {
              $_SESSION['error_message'] = "Vous n'avez pas l'autorisation d'effectuer cette action.";
              $this->redirect('/deliveries');
          }
          
          if ($_SERVER['REQUEST_METHOD'] === 'POST') {
              if (Delivery::delete($id)) {
                  $_SESSION['success_message'] = 'Bon de livraison supprimé avec succès';
              } else {
                  $_SESSION['error_message'] = 'Une erreur est survenue lors de la suppression du bon de livraison';
              }
          }
          
          $this->redirect('/deliveries');
      }
      
      public function complete($id)
      {
          // Check if user has permission
          if (!Auth::isAdmin() && !Auth::isStorekeeper()) {
              $_SESSION['error_message'] = "Vous n'avez pas l'autorisation d'effectuer cette action.";
              $this->redirect('/deliveries');
          }
          
          $delivery = Delivery::getById($id);
          
          if (!$delivery) {
              $_SESSION['error_message'] = 'Bon de livraison introuvable';
              $this->redirect('/deliveries');
          }
          
          // Update delivery status to completed
          if (Delivery::updateStatus($id, 'completed')) {
              // Create exit operation for the products
              $order = Order::getById($delivery['order_id']);
              $orderItems = Order::getOrderItems($delivery['order_id']);
              
              $exitController = new OperationController();
              $exitId = $exitController->createExitFromDelivery($delivery, $order, $orderItems);
              // ($delivery, $order, $orderItems);
              
              if ($exitId) {
                  $_SESSION['success_message'] = 'Bon de livraison marqué comme complété et bon de sortie généré';
              } else {
                  $_SESSION['success_message'] = 'Bon de livraison marqué comme complété mais erreur lors de la génération du bon de sortie';
              }
          } else {
              $_SESSION['error_message'] = 'Une erreur est survenue lors de la mise à jour du bon de livraison';
          }
          
          $this->redirect('/deliveries/show/' . $id);
      }
      
      public function generatePdf($id)
      {
          $delivery = Delivery::getById($id);
          
          if (!$delivery) {
              $_SESSION['error_message'] = 'Bon de livraison introuvable';
              $this->redirect('/deliveries');
          }
          
          $order = Order::getById($delivery['order_id']);
          $orderItems = Order::getOrderItems($delivery['order_id']);
          $client = Client::getById($order['client_id']);
          
          // Generate PDF using a library like FPDF or TCPDF
          // This is a placeholder for the actual PDF generation code
          
          $_SESSION['success_message'] = 'PDF du bon de livraison généré avec succès';
          $this->redirect('/deliveries/show/' . $id);
      }

      /**
 * Générer un bon de livraison au format PDF
 */
      public function generateDeliveryNote($id) {
        // Vérifier les droits d'accès
        Auth::checkAccess('any');
        
        // Récupérer la livraison
        $delivery = Delivery::getById($id);
        
        if (!$delivery) {
            $_SESSION['error'] = "La livraison demandée n'existe pas.";
            header('Location: ' . APP_URL . '/deliveries');
            exit;
        }
        
        // Récupérer la commande associée
        $order = Order::getById($delivery['id_commande']);
        
        // Récupérer les détails de la commande
        $orderItems = Order::getItems($delivery['id_commande']);
        
        // Récupérer le client
        $client = Order::getClient($order['id_client']);
        
        // Créer le PDF
        require_once BASE_PATH . '/vendor/fpdf/fpdf.php';
        
        $pdf = new \FPDF();
        $pdf->AddPage();
        
        // En-tête
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'BON DE LIVRAISON', 0, 1, 'C');
        $pdf->Cell(0, 10, 'N° ' . $delivery['id_livraison'], 0, 1, 'C');
        $pdf->Ln(10);
        
        // Informations de la société
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, APP_NAME, 0, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, 'Adresse: 123 Rue Principale', 0, 1, 'L');
        $pdf->Cell(0, 6, 'Téléphone: +221 33 123 45 67', 0, 1, 'L');
        $pdf->Cell(0, 6, 'Email: contact@example.com', 0, 1,'L');
        $pdf->Cell(0, 6, 'Email: contact@example.com', 0, 1, 'L');
        $pdf->Ln(10);
        // Informations du client
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Client:', 0, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, $client['nom'] . ' ' . $client['prenom'], 0, 1, 'L');
        $pdf->Cell(0, 6, $client['adresse'], 0, 1, 'L');
        $pdf->Cell(0, 6, $client['telephone'], 0, 1, 'L');
        $pdf->Cell(0, 6, $client['email'], 0, 1, 'L');
        $pdf->Ln(10);
        
        // Informations de la livraison
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Détails de la livraison:', 0, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, 'Date de livraison: ' . formatDate($delivery['date_livraison'], 'd/m/Y'), 0, 1, 'L');
        $pdf->Cell(0, 6, 'Adresse de livraison: ' . $delivery['adresse_livraison'], 0, 1, 'L');
        $pdf->Cell(0, 6, 'Numéro de commande: ' . $order['numero_commande'], 0, 1, 'L');
        $pdf->Cell(0, 6, 'Statut: ' . ucfirst($delivery['statut']), 0, 1, 'L');
        $pdf->Ln(10);
        
        // Tableau des produits
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(10, 10, '#', 1, 0, 'C');
        $pdf->Cell(90, 10, 'Produit', 1, 0, 'C');
        $pdf->Cell(30, 10, 'Quantité', 1, 0, 'C');
        $pdf->Cell(30, 10, 'Prix unitaire', 1, 0, 'C');
        $pdf->Cell(30, 10, 'Total', 1, 1, 'C');
        
        $pdf->SetFont('Arial', '', 10);
        $i = 1;
        $total = 0;
        
        foreach ($orderItems as $item) {
            $pdf->Cell(10, 10, $i, 1, 0, 'C');
            $pdf->Cell(90, 10, $item['designation'], 1, 0, 'L');
            $pdf->Cell(30, 10, $item['quantite'], 1, 0, 'C');
            $pdf->Cell(30, 10, formatPrice($item['prix_unitaire']), 1, 0, 'R');
            $pdf->Cell(30, 10, formatPrice($item['prix_unitaire'] * $item['quantite']), 1, 1, 'R');
            
            $total += $item['prix_unitaire'] * $item['quantite'];
            $i++;
        }
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(160, 10, 'Total', 1, 0, 'R');
        $pdf->Cell(30, 10, formatPrice($total), 1, 1, 'R');
        
        // Notes
        if (!empty($delivery['notes'])) {
            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'Notes:', 0, 1, 'L');
            $pdf->SetFont('Arial', '', 10);
            $pdf->MultiCell(0, 6, $delivery['notes'], 0, 'L');
        }
        
        // Signatures
        $pdf->Ln(20);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(95, 10, 'Signature du livreur:', 0, 0, 'L');
        $pdf->Cell(95, 10, 'Signature du client:', 0, 1, 'R');
        $pdf->Ln(20);
        $pdf->Cell(95, 0, '', 'T', 0, 'L');
        $pdf->Cell(10, 0, '', 0, 0, 'L');
        $pdf->Cell(95, 0, '', 'T', 1, 'R');
        
        // Pied de page
        $pdf->Ln(20);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, 'Document généré le ' . date('d/m/Y à H:i'), 0, 0, 'C');
        
        // Sortie du PDF
        $pdf->Output('bon_livraison_' . $delivery['id_livraison'] . '.pdf', 'D');
        exit;
    }

      }
