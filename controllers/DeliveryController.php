<?php 
  class ClientController{

private $deliveryModel;
private $orderModel;
private $authController;

public function __construct() {
    $this->deliveryModel = new Delivery();
    $this->orderModel = new Order();
    $this->authController = new AuthController();
}

/**
 * Afficher la liste des livraisons
 */
public function index() {
    // Vérifier les droits d'accès
    $this->authController->checkAccess('any');
    
    // Récupérer toutes les livraisons
    $deliveries = $this->deliveryModel->getAll();
    
    // Définir le titre de la page
    $pageTitle = 'Gestion des Livraisons';
    
    // Afficher la vue
    require_once BASE_PATH . '/views/layouts/header.php';
    require_once BASE_PATH . '/views/deliveries/index.php';
    require_once BASE_PATH . '/views/layouts/footer.php';
}

/**
 * Afficher le formulaire de création d'une livraison
 */
public function create($orderId) {
    // Vérifier les droits d'accès
    if (!$this->authController->isAdmin() && !$this->authController->isStorekeeper()) {
        $this->authController->checkAccess('admin');
    }
    
    // Récupérer la commande
    $order = $this->orderModel->getById($orderId);
    
    if (!$order) {
        $_SESSION['error'] = "La commande demandée n'existe pas.";
        header('Location: ' . APP_URL . '/orders');
        exit;
    }
    
    // Vérifier si la commande est approuvée
    if ($order['statut'] !== ORDER_APPROVED) {
        $_SESSION['error'] = "Seules les commandes approuvées peuvent être livrées.";
        header('Location: ' . APP_URL . '/orders/show/' . $orderId);
        exit;
    }
    
    // Vérifier si la commande a déjà une livraison
    $existingDelivery = $this->deliveryModel->getByOrderId($orderId);
    if ($existingDelivery) {
        $_SESSION['error'] = "Cette commande a déjà une livraison associée.";
        header('Location: ' . APP_URL . '/deliveries/show/' . $existingDelivery['id_livraison']);
        exit;
    }
    
    // Récupérer les détails de la commande
    $orderItems = $this->orderModel->getItems($orderId);
    
    // Définir le titre de la page
    $pageTitle = 'Créer une Livraison pour la Commande #' . $order['numero_commande'];
    
    // Afficher la vue
    require_once BASE_PATH . '/views/layouts/header.php';
    require_once BASE_PATH . '/views/deliveries/create.php';
    require_once BASE_PATH . '/views/layouts/footer.php';
}

/**
 * Traiter la création d'une livraison
 */
public function store($orderId) {
    // Vérifier les droits d'accès
    if (!$this->authController->isAdmin() && !$this->authController->isStorekeeper()) {
        $this->authController->checkAccess('admin');
    }
    
    // Récupérer la commande
    $order = $this->orderModel->getById($orderId);
    
    if (!$order) {
        $_SESSION['error'] = "La commande demandée n'existe pas.";
        header('Location: ' . APP_URL . '/orders');
        exit;
    }
    
    // Vérifier si la commande est approuvée
    if ($order['statut'] !== ORDER_APPROVED) {
        $_SESSION['error'] = "Seules les commandes approuvées peuvent être livrées.";
        header('Location: ' . APP_URL . '/orders/show/' . $orderId);
        exit;
    }
    
    // Vérifier si la commande a déjà une livraison
    $existingDelivery = $this->deliveryModel->getByOrderId($orderId);
    if ($existingDelivery) {
        $_SESSION['error'] = "Cette commande a déjà une livraison associée.";
        header('Location: ' . APP_URL . '/deliveries/show/' . $existingDelivery['id_livraison']);
        exit;
    }
    
    // Vérifier si le formulaire a été soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupérer les données du formulaire
        $deliveryData = [
            'id_commande' => $orderId,
            'date_livraison' => $_POST['date_livraison'] ?? date('Y-m-d'),
            'adresse_livraison' => $_POST['adresse_livraison'] ?? $order['adresse_livraison'],
            'notes' => $_POST['notes'] ?? '',
            'statut' => 'en_cours',
            'id_utilisateur' => $_SESSION['user_id']
        ];
        
        // Valider les données
        $errors = [];
        
        if (empty($deliveryData['date_livraison'])) {
            $errors[] = "La date de livraison est requise.";
        }
        
        if (empty($deliveryData['adresse_livraison'])) {
            $errors[] = "L'adresse de livraison est requise.";
        }
        
        // S'il y a des erreurs, afficher le formulaire avec les erreurs
        if (!empty($errors)) {
            $error = implode('<br>', $errors);
            $pageTitle = 'Créer une Livraison pour la Commande #' . $order['numero_commande'];
            $orderItems = $this->orderModel->getItems($orderId);
            
            require_once BASE_PATH . '/views/layouts/header.php';
            require_once BASE_PATH . '/views/deliveries/create.php';
            require_once BASE_PATH . '/views/layouts/footer.php';
            return;
        }
        
        try {
            // Créer la livraison
            $deliveryId = $this->deliveryModel->create($deliveryData);
            
            // Mettre à jour le statut de la commande
            $this->orderModel->updateStatus($orderId, ORDER_DELIVERED);
            
            // Rediriger vers la page de détails de la livraison
            $_SESSION['success'] = "La livraison a été créée avec succès.";
            header('Location: ' . APP_URL . '/deliveries/show/' . $deliveryId);
            exit;
        } catch (Exception $e) {
            // Afficher le formulaire avec l'erreur
            $error = "Erreur lors de la création de la livraison : " . $e->getMessage();
            $pageTitle = 'Créer une Livraison pour la Commande #' . $order['numero_commande'];
            $orderItems = $this->orderModel->getItems($orderId);
            
            require_once BASE_PATH . '/views/layouts/header.php';
            require_once BASE_PATH . '/views/deliveries/create.php';
            require_once BASE_PATH . '/views/layouts/footer.php';
        }
    } else {
        // Rediriger vers le formulaire de création
        header('Location: ' . APP_URL . '/deliveries/create/' . $orderId);
        exit;
    }
}

/**
 * Afficher les détails d'une livraison
 */
public function show($id) {
    // Vérifier les droits d'accès
    $this->authController->checkAccess('any');
    
    // Récupérer la livraison
    $delivery = $this->deliveryModel->getById($id);
    
    if (!$delivery) {
        $_SESSION['error'] = "La livraison demandée n'existe pas.";
        header('Location: ' . APP_URL . '/deliveries');
        exit;
    }
    
    // Récupérer la commande associée
    $order = $this->orderModel->getById($delivery['id_commande']);
    
    // Récupérer les détails de la commande
    $orderItems = $this->orderModel->getItems($delivery['id_commande']);
    
    // Définir le titre de la page
    $pageTitle = 'Détails de la Livraison #' . $delivery['id_livraison'];
    
    // Définir les boutons d'action
    $actionButtons = '';
    
    if ($delivery['statut'] === 'en_cours') {
        $actionButtons .= '
            <a href="' . APP_URL . '/deliveries/complete/' . $id . '" class="btn btn-success">
                <i class="bi bi-check-circle"></i> Marquer comme livrée
            </a>
        ';
    }
    
    if ($this->authController->isAdmin()) {
        $actionButtons .= '
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="bi bi-trash"></i> Supprimer
            </button>
        ';
    }
    
    // Afficher la vue
    require_once BASE_PATH . '/views/layouts/header.php';
    require_once BASE_PATH . '/views/deliveries/show.php';
    require_once BASE_PATH . '/views/layouts/footer.php';
}

/**
 * Marquer une livraison comme terminée
 */
public function complete($id) {
    // Vérifier les droits d'accès
    if (!$this->authController->isAdmin() && !$this->authController->isStorekeeper()) {
        $this->authController->checkAccess('admin');
    }
    
    // Récupérer la livraison
    $delivery = $this->deliveryModel->getById($id);
    
    if (!$delivery) {
        $_SESSION['error'] = "La livraison demandée n'existe pas.";
        header('Location: ' . APP_URL . '/deliveries');
        exit;
    }
    
    // Vérifier si la livraison est en cours
    if ($delivery['statut'] !== 'en_cours') {
        $_SESSION['error'] = "Cette livraison ne peut pas être marquée comme terminée.";
        header('Location: ' . APP_URL . '/deliveries/show/' . $id);
        exit;
    }
    
    try {
        // Mettre à jour le statut de la livraison
        $this->deliveryModel->updateStatus($id, 'terminee');
        
        // Rediriger vers la page de détails de la livraison
        $_SESSION['success'] = "La livraison a été marquée comme terminée avec succès.";
        header('Location: ' . APP_URL . '/deliveries/show/' . $id);
        exit;
    } catch (Exception $e) {
        // Rediriger avec un message d'erreur
        $_SESSION['error'] = "Erreur lors de la mise à jour de la livraison : " . $e->getMessage();
        header('Location: ' . APP_URL . '/deliveries/show/' . $id);
        exit;
    }
}

/**
 * Supprimer une livraison
 */
public function delete($id) {
    // Vérifier les droits d'accès
    $this->authController->checkAccess('admin');
    
    // Récupérer la livraison
    $delivery = $this->deliveryModel->getById($id);
    
    if (!$delivery) {
        $_SESSION['error'] = "La livraison demandée n'existe pas.";
        header('Location: ' . APP_URL . '/deliveries');
        exit;
    }
    
    // Vérifier si le formulaire a été soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Récupérer l'ID de la commande associée
            $orderId = $delivery['id_commande'];
            
            // Supprimer la livraison
            $this->deliveryModel->delete($id);
            
            // Mettre à jour le statut de la commande
            $this->orderModel->updateStatus($orderId, ORDER_APPROVED);
            
            // Rediriger vers la liste des livraisons
            $_SESSION['success'] = "La livraison a été supprimée avec succès.";
            header('Location: ' . APP_URL . '/deliveries');
            exit;
        } catch (Exception $e) {
            // Rediriger avec un message d'erreur
            $_SESSION['error'] = "Erreur lors de la suppression de la livraison : " . $e->getMessage();
            header('Location: ' . APP_URL . '/deliveries/show/' . $id);
            exit;
        }
    } else {
        // Rediriger vers la page de détails
        header('Location: ' . APP_URL . '/deliveries/show/' . $id);
        exit;
    }
}

/**
 * Générer un bon de livraison au format PDF
 */
public function generateDeliveryNote($id) {
    // Vérifier les droits d'accès
    $this->authController->checkAccess('any');
    
    // Récupérer la livraison
    $delivery = $this->deliveryModel->getById($id);
    
    if (!$delivery) {
        $_SESSION['error'] = "La livraison demandée n'existe pas.";
        header('Location: ' . APP_URL . '/deliveries');
        exit;
    }
    
    // Récupérer la commande associée
    $order = $this->orderModel->getById($delivery['id_commande']);
    
    // Récupérer les détails de la commande
    $orderItems = $this->orderModel->getItems($delivery['id_commande']);
    
    // Récupérer le client
    $client = $this->orderModel->getClient($order['id_client']);
    
    // Créer le PDF
    require_once BASE_PATH . '/vendor/fpdf/fpdf.php';
    
    $pdf = new FPDF();
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
    $pdf->Cell(0, 6, 'Email: contact@example.com', 0, 1,

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
