<?php
/**
 * Contrôleur des Produits
 * Gère toutes les opérations liées aux produits
 */
class ProductController {
    private $productModel;
    private $authController;
    
    public function __construct() {
        $this->productModel = new Product();
        $this->authController = new AuthController();
    }
    
    /**
     * Afficher la liste des produits
     */
    public function index() {
        // Vérifier les droits d'accès
        $this->authController->checkAccess('any');
        
        // Récupérer tous les produits
        $products = $this->productModel->getAll();
        
        // Définir le titre de la page
        $pageTitle = 'Gestion des Produits';
        
        // Définir les boutons d'action
        $actionButtons = '
            <a href="' . APP_URL . '/products/create" class="btn btn-primary">
                <i class="bi bi-plus"></i> Ajouter un produit
            </a>
        ';
        
        // Afficher la vue
        require_once BASE_PATH . '/views/layouts/header.php';
        require_once BASE_PATH . '/views/products/index.php';
        require_once BASE_PATH . '/views/layouts/footer.php';
    }
    
    /**
     * Afficher le formulaire d'ajout de produit
     */
    public function create() {
        // Vérifier les droits d'accès (seuls le directeur et le magasinier peuvent ajouter des produits)
        if (!$this->authController->isAdmin() && !$this->authController->isStorekeeper()) {
            $this->authController->checkAccess('admin');
        }
        
        // Définir le titre de la page
        $pageTitle = 'Ajouter un Produit';
        
        // Afficher la vue
        require_once BASE_PATH . '/views/layouts/header.php';
        require_once BASE_PATH . '/views/products/create.php';
        require_once BASE_PATH . '/views/layouts/footer.php';
    }
    
    /**
     * Traiter l'ajout d'un produit
     */
    public function store() {
        // Vérifier les droits d'accès
        if (!$this->authController->isAdmin() && !$this->authController->isStorekeeper()) {
            $this->authController->checkAccess('admin');
        }
        
        // Vérifier si le formulaire a été soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $productData = [
                'designation' => $_POST['designation'] ?? '',
                'quantite' => $_POST['quantite'] ?? 0,
                'prix_vente' => $_POST['prix_vente'] ?? 0,
                'prix_achat' => $_POST['prix_achat'] ?? 0
            ];
            
            // Valider les données
            $errors = [];
            
            if (empty($productData['designation'])) {
                $errors[] = "La désignation du produit est requise.";
            }
            
            if (!is_numeric($productData['prix_vente']) || $productData['prix_vente'] <= 0) {
                $errors[] = "Le prix de vente doit être un nombre positif.";
            }
            
            if (!is_numeric($productData['prix_achat']) || $productData['prix_achat'] <= 0) {
                $errors[] = "Le prix d'achat doit être un nombre positif.";
            }
            
            if (!is_numeric($productData['quantite']) || $productData['quantite'] < 0) {
                $errors[] = "La quantité doit être un nombre positif ou nul.";
            }
            
            // S'il y a des erreurs, afficher le formulaire avec les erreurs
            if (!empty($errors)) {
                $error = implode('<br>', $errors);
                $pageTitle = 'Ajouter un Produit';
                
                require_once BASE_PATH . '/views/layouts/header.php';
                require_once BASE_PATH . '/views/products/create.php';
                require_once BASE_PATH . '/views/layouts/footer.php';
                return;
            }
            
            try {
                // Ajouter le produit
                $this->productModel->create($productData);
                
                // Rediriger vers la liste des produits avec un message de succès
                $_SESSION['success'] = "Le produit a été ajouté avec succès.";
                header('Location: ' . APP_URL . '/products');
                exit;
            } catch (Exception $e) {
                // Afficher le formulaire avec l'erreur
                $error = "Erreur lors de l'ajout du produit : " . $e->getMessage();
                $pageTitle = 'Ajouter un Produit';
                
                require_once BASE_PATH . '/views/layouts/header.php';
                require_once BASE_PATH . '/views/products/create.php';
                require_once BASE_PATH . '/views/layouts/footer.php';
            }
        } else {
            // Rediriger vers le formulaire d'ajout
            header('Location: ' . APP_URL . '/products/create');
            exit;
        }
    }
    
    /**
     * Afficher les détails d'un produit
     */
    public function show($id) {
        // Vérifier les droits d'accès
        $this->authController->checkAccess('any');
        
        // Récupérer le produit
        $product = $this->productModel->getById($id);
        
        if (!$product) {
            $_SESSION['error'] = "Le produit demandé n'existe pas.";
            header('Location: ' . APP_URL . '/products');
            exit;
        }
        
        // Récupérer l'historique des mouvements du produit
        $movements = $this->productModel->getMovementHistory($id);
        
        // Définir le titre de la page
        $pageTitle = 'Détails du Produit: ' . $product['designation'];
        
        // Définir les boutons d'action
        $actionButtons = '
            <a href="' . APP_URL . '/products/edit/' . $id . '" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Modifier
            </a>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="bi bi-trash"></i> Supprimer
            </button>
        ';
        
        // Afficher la vue
        require_once BASE_PATH . '/views/layouts/header.php';
        require_once BASE_PATH . '/views/products/show.php';
        require_once BASE_PATH . '/views/layouts/footer.php';
    }
    
    /**
     * Afficher le formulaire de modification d'un produit
     */
    public function edit($id) {
        // Vérifier les droits d'accès
        if (!$this->authController->isAdmin() && !$this->authController->isStorekeeper()) {
            $this->authController->checkAccess('admin');
        }
        
        // Récupérer le produit
        $product = $this->productModel->getById($id);
        
        if (!$product) {
            $_SESSION['error'] = "Le produit demandé n'existe pas.";
            header('Location: ' . APP_URL . '/products');
            exit;
        }
        
        // Définir le titre de la page
        $pageTitle = 'Modifier le Produit: ' . $product['designation'];
        
        // Afficher la vue
        require_once BASE_PATH . '/views/layouts/header.php';
        require_once BASE_PATH . '/views/products/edit.php';
        require_once BASE_PATH . '/views/layouts/footer.php';
    }
    
    /**
     * Traiter la modification d'un produit
     */
    public function update($id) {
        // Vérifier les droits d'accès
        if (!$this->authController->isAdmin() && !$this->authController->isStorekeeper()) {
            $this->authController->checkAccess('admin');
        }
        
        // Récupérer le produit
        $product = $this->productModel->getById($id);
        
        if (!$product) {
            $_SESSION['error'] = "Le produit demandé n'existe pas.";
            header('Location: ' . APP_URL . '/products');
            exit;
        }
        
        // Vérifier si le formulaire a été soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $productData = [
                'designation' => $_POST['designation'] ?? '',
                'prix_vente' => $_POST['prix_vente'] ?? 0,
                'prix_achat' => $_POST['prix_achat'] ?? 0
            ];
            
            // Valider les données
            $errors = [];
            
            if (empty($productData['designation'])) {
                $errors[] = "La désignation du produit est requise.";
            }
            
            if (!is_numeric($productData['prix_vente']) || $productData['prix_vente'] <= 0) {
                $errors[] = "Le prix de vente doit être un nombre positif.";
            }
            
            if (!is_numeric($productData['prix_achat']) || $productData['prix_achat'] <= 0) {
                $errors[] = "Le prix d'achat doit être un nombre positif.";
            }
            
            // S'il y a des erreurs, afficher le formulaire avec les erreurs
            if (!empty($errors)) {
                $error = implode('<br>', $errors);
                $pageTitle = 'Modifier le Produit: ' . $product['designation'];
                
                require_once BASE_PATH . '/views/layouts/header.php';
                require_once BASE_PATH . '/views/products/edit.php';
                require_once BASE_PATH . '/views/layouts/footer.php';
                return;
            }
            
            try {
                // Mettre à jour le produit
                $this->productModel->update($id, $productData);
                
                // Rediriger vers la liste des produits avec un message de succès
                $_SESSION['success'] = "Le produit a été mis à jour avec succès.";
                header('Location: ' . APP_URL . '/products');
                exit;
            } catch (Exception $e) {
                // Afficher le formulaire avec l'erreur
                $error = "Erreur lors de la mise à jour du produit : " . $e->getMessage();
                $pageTitle = 'Modifier le Produit: ' . $product['designation'];
                
                require_once BASE_PATH . '/views/layouts/header.php';
                require_once BASE_PATH . '/views/products/edit.php';
                require_once BASE_PATH . '/views/layouts/footer.php';
            }
        } else {
            // Rediriger vers le formulaire de modification
            header('Location: ' . APP_URL . '/products/edit/' . $id);
            exit;
        }
    }
    
    /**
     * Traiter la suppression d'un produit
     */
    public function delete($id) {
        // Vérifier les droits d'accès
        if (!$this->authController->isAdmin() && !$this->authController->isStorekeeper()) {
            $this->authController->checkAccess('admin');
        }
        
        // Vérifier si le formulaire a été soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Supprimer le produit
                $this->productModel->delete($id);
                
                // Rediriger vers la liste des produits avec un message de succès
                $_SESSION['success'] = "Le produit a été supprimé avec succès.";
                header('Location: ' . APP_URL . '/products');
                exit;
            } catch (Exception $e) {
                // Rediriger vers la liste des produits avec un message d'erreur
                $_SESSION['error'] = "Erreur lors de la suppression du produit : " . $e->getMessage();
                header('Location: ' . APP_URL . '/products');
                exit;
            }
        } else {
            // Rediriger vers la liste des produits
            header('Location: ' . APP_URL . '/products');
            exit;
        }
    }
    
    /**
     * Afficher la page de gestion du stock
     */
    public function stock() {
        // Vérifier les droits d'accès
        $this->authController->checkAccess('any');
        
        // Récupérer tous les produits
        $products = $this->productModel->getAll();
        
        // Récupérer les produits en rupture de stock
        $outOfStock = $this->productModel->getOutOfStock();
        
        // Récupérer les produits à faible stock
        $lowStock = $this->productModel->getLowStock();
        
        // Récupérer la valeur totale du stock
        $stockValue = $this->productModel->getTotalStockValue();
        
        // Définir le titre de la page
        $pageTitle = 'Gestion du Stock';
        
        // Afficher la vue
        require_once BASE_PATH . '/views/layouts/header.php';
        require_once BASE_PATH . '/views/products/stock.php';
        require_once BASE_PATH . '/views/layouts/footer.php';
    }
    
    /**
     * Afficher le formulaire d'ajustement de stock
     */
    public function adjustStock($id) {
        // Vérifier les droits d'accès
        if (!$this->authController->isAdmin() && !$this->authController->isStorekeeper()) {
            $this->authController->checkAccess('admin');
        }
        
        // Récupérer le produit
        $product = $this->productModel->getById($id);
        
        if (!$product) {
            $_SESSION['error'] = "Le produit demandé n'existe pas.";
            header('Location: ' . APP_URL . '/products/stock');
            exit;
        }
        
        // Définir le titre de la page
        $pageTitle = 'Ajuster le Stock: ' . $product['designation'];
        
        // Afficher la vue
        require_once BASE_PATH . '/views/layouts/header.php';
        require_once BASE_PATH . '/views/products/adjust_stock.php';
        require_once BASE_PATH . '/views/layouts/footer.php';
    }
    
    /**
     * Traiter l'ajustement de stock
     */
    public function updateStock($id) {
        // Vérifier les droits d'accès
        if (!$this->authController->isAdmin() && !$this->authController->isStorekeeper()) {
            $this->authController->checkAccess('admin');
        }
        
 // Récupérer le produit
 $product = $this->productModel->getById($id);
        
 if (!$product) {
     $_SESSION['error'] = "Le produit demandé n'existe pas.";
     header('Location: ' . APP_URL . '/products/stock');
     exit;
 }
 
 // Vérifier si le formulaire a été soumis
 if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     // Récupérer les données du formulaire
     $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
     $type = isset($_POST['type']) ? $_POST['type'] : '';
     $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
     
     // Valider les données
     $errors = [];
     
     if ($quantity <= 0) {
         $errors[] = "La quantité doit être un nombre positif.";
     }
     
     if (!in_array($type, ['add', 'remove'])) {
         $errors[] = "Le type d'ajustement est invalide.";
     }
     
     if (empty($reason)) {
         $errors[] = "La raison de l'ajustement est requise.";
     }
     
     // S'il y a des erreurs, afficher le formulaire avec les erreurs
     if (!empty($errors)) {
         $error = implode('<br>', $errors);
         $pageTitle = 'Ajuster le Stock: ' . $product['designation'];
         
         require_once BASE_PATH . '/views/layouts/header.php';
         require_once BASE_PATH . '/views/products/adjust_stock.php';
         require_once BASE_PATH . '/views/layouts/footer.php';
         return;
     }
     
     try {
         // Calculer la nouvelle quantité
         $newQuantity = ($type === 'add') 
             ? $product['quantite'] + $quantity 
             : $product['quantite'] - $quantity;
         
         // Vérifier que la quantité ne devient pas négative
         if ($newQuantity < 0) {
             $error = "La quantité en stock ne peut pas être négative.";
             $pageTitle = 'Ajuster le Stock: ' . $product['designation'];
             
             require_once BASE_PATH . '/views/layouts/header.php';
             require_once BASE_PATH . '/views/products/adjust_stock.php';
             require_once BASE_PATH . '/views/layouts/footer.php';
             return;
         }
         
         // Mettre à jour le stock
         $this->productModel->update($id, ['quantite' => $newQuantity]);
         
         // Enregistrer le mouvement de stock
         $movementData = [
             'id_produit' => $id,
             'quantite' => ($type === 'add') ? $quantity : -$quantity,
             'date_mouvement' => date('Y-m-d H:i:s'),
             'raison' => $reason,
             'id_utilisateur' => $_SESSION['user_id'] ?? null
         ];
         
         $this->productModel->recordStockMovement($movementData);
         
         // Rediriger vers la page de stock avec un message de succès
         $_SESSION['success'] = "Le stock a été ajusté avec succès.";
         header('Location: ' . APP_URL . '/products/stock');
         exit;
     } catch (Exception $e) {
         // Afficher le formulaire avec l'erreur
         $error = "Erreur lors de l'ajustement du stock : " . $e->getMessage();
         $pageTitle = 'Ajuster le Stock: ' . $product['designation'];
         
         require_once BASE_PATH . '/views/layouts/header.php';
         require_once BASE_PATH . '/views/products/adjust_stock.php';
         require_once BASE_PATH . '/views/layouts/footer.php';
     }
 } else {
     // Rediriger vers le formulaire d'ajustement
     header('Location: ' . APP_URL . '/products/adjust-stock/' . $id);
     exit;
 }
}

/**
* Rechercher des produits
*/
public function search() {
 // Vérifier les droits d'accès
 $this->authController->checkAccess('any');
 
 // Récupérer le terme de recherche
 $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
 
 if (empty($keyword)) {
     header('Location: ' . APP_URL . '/products');
     exit;
 }
 
 // Rechercher les produits
 $products = $this->productModel->search($keyword);
 
 // Définir le titre de la page
 $pageTitle = 'Résultats de recherche pour: ' . htmlspecialchars($keyword);
 
 // Définir les boutons d'action
 $actionButtons = '
     <a href="' . APP_URL . '/products" class="btn btn-secondary">
         <i class="bi bi-arrow-left"></i> Retour à la liste
     </a>
     <a href="' . APP_URL . '/products/create" class="btn btn-primary">
         <i class="bi bi-plus"></i> Ajouter un produit
     </a>
 ';
 
 // Afficher la vue
 require_once BASE_PATH . '/views/layouts/header.php';
 require_once BASE_PATH . '/views/products/search_results.php';
 require_once BASE_PATH . '/views/layouts/footer.php';
}

/**
* Exporter la liste des produits au format CSV
*/
public function export() {
 // Vérifier les droits d'accès
 if (!$this->authController->isAdmin() && !$this->authController->isStorekeeper()) {
     $this->authController->checkAccess('admin');
 }
 
 // Récupérer tous les produits
 $products = $this->productModel->getAll();
 
 // Définir les en-têtes pour le téléchargement
 header('Content-Type: text/csv; charset=utf-8');
 header('Content-Disposition: attachment; filename="produits_' . date('Y-m-d') . '.csv"');
 
 // Créer le flux de sortie
 $output = fopen('php://output', 'w');
 
 // Ajouter l'en-tête UTF-8 BOM pour Excel
 fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
 
 // Écrire les en-têtes du CSV
 fputcsv($output, ['ID', 'Désignation', 'Quantité', 'Prix d\'achat', 'Prix de vente', 'Date d\'ajout']);
 
 // Écrire les données des produits
 foreach ($products as $product) {
     fputcsv($output, [
         $product['id_produit'],
         $product['designation'],
         $product['quantite'],
         $product['prix_achat'],
         $product['prix_vente'],
         $product['date_ajout']
     ]);
 }
 
 // Fermer le flux
 fclose($output);
 exit;
}

/**
* Générer un rapport de stock
*/
public function report() {
  // Vérifier les droits d'accès
  if (!$this->authController->isAdmin()) {
      $this->authController->checkAccess('admin');
  }
 
 // Récupérer les statistiques
  $stats = [
      'total_products' => $this->productModel->countProducts(),
      'out_of_stock' => count($this->productModel->getOutOfStock()),
      'low_stock' => count($this->productModel->getLowStock()),
      'total_value' => $this->productModel->getTotalStockValue(),
      'most_expensive' => $this->productModel->getMostExpensiveProduct(),
      'most_in_stock' => $this->productModel->getMostInStockProduct(),
      'recent_movements' => $this->productModel->getRecentMovements(10)
  ];
  
  // Définir le titre de la page
  $pageTitle = 'Rapport de Stock';
  
  // Afficher la vue
  require_once BASE_PATH . '/views/layouts/header.php';
  require_once BASE_PATH . '/views/products/report.php';
  require_once BASE_PATH . '/views/layouts/footer.php';
  }
  /**
 * Récupérer les produits à faible stock
 */
public function getLowStock() {
  // Vérifier les droits d'accès
  $this->authController->checkAccess('any');
  
  // Récupérer les produits à faible stock
  $lowStock = $this->productModel->getLowStock();
  
  // Définir le titre de la page
  $pageTitle = 'Produits à Faible Stock';
  
  // Afficher la vue
  require_once BASE_PATH . '/views/layouts/header.php';
  require_once BASE_PATH . '/views/products/low_stock.php';
  require_once BASE_PATH . '/views/layouts/footer.php';
}

/**
* Récupérer les produits en rupture de stock
*/
public function getOutOfStock() {
  // Vérifier les droits d'accès
  $this->authController->checkAccess('any');
  
  // Récupérer les produits en rupture de stock
  $outOfStock = $this->productModel->getOutOfStock();
  
  // Définir le titre de la page
  $pageTitle = 'Produits en Rupture de Stock';
  
  // Afficher la vue
  require_once BASE_PATH . '/views/layouts/header.php';
  require_once BASE_PATH . '/views/products/out_of_stock.php';
  require_once BASE_PATH . '/views/layouts/footer.php';
}

/**
* Afficher l'historique des mouvements de stock
*/
public function stockHistory() {
  // Vérifier les droits d'accès
  $this->authController->checkAccess('any');
  
  // Récupérer les mouvements de stock récents
  $movements = $this->productModel->getRecentMovements(50);
  
  // Définir le titre de la page
  $pageTitle = 'Historique des Mouvements de Stock';
  
  // Afficher la vue
  require_once BASE_PATH . '/views/layouts/header.php';
  require_once BASE_PATH . '/views/products/stock_history.php';
  require_once BASE_PATH . '/views/layouts/footer.php';
}

/**
* Exporter la liste des produits au format CSV
*/
public function exportCsv() {
  // Vérifier les droits d'accès
  $this->authController->checkAccess('any');
  
  // Récupérer tous les produits
  $products = $this->productModel->getAll();
  
  // Définir les en-têtes pour le téléchargement
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=produits_' . date('Y-m-d') . '.csv');
  
  // Créer le flux de sortie
  $output = fopen('php://output', 'w');
  
  // Ajouter l'en-tête UTF-8 BOM pour Excel
  fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
  
  // Ajouter les en-têtes de colonnes
  fputcsv($output, ['ID', 'Désignation', 'Quantité', 'Prix d\'achat', 'Prix de vente', 'Date de création']);
  
  // Ajouter les données
  foreach ($products as $product) {
      fputcsv($output, [
          $product['id_produit'],
          $product['designation'],
          $product['quantite'],
          $product['prix_achat'],
          $product['prix_vente'],
          $product['date_creation']
      ]);
  }
  
  fclose($output);
  exit;
}

/**
* Générer un rapport de stock au format PDF
*/
public function generateStockReport() {
  // Vérifier les droits d'accès
  $this->authController->checkAccess('any');
  
  // Récupérer les données nécessaires
  $products = $this->productModel->getAll();
  $outOfStock = $this->productModel->getOutOfStock();
  $lowStock = $this->productModel->getLowStock();
  $stockValue = $this->productModel->getTotalStockValue();
  
  // Créer le PDF (utilisation d'une bibliothèque comme FPDF ou TCPDF)
  require_once BASE_PATH . '/vendor/fpdf/fpdf.php';
  
  $pdf = new FPDF();
  $pdf->AddPage();
  
  // En-tête
  $pdf->SetFont('Arial', 'B', 16);
  $pdf->Cell(0, 10, 'Rapport de Stock', 0, 1, 'C');
  $pdf->Cell(0, 10, 'Date: ' . date('d/m/Y'), 0, 1, 'C');
  $pdf->Ln(10);
  
  // Résumé
  $pdf->SetFont('Arial', 'B', 12);
  $pdf->Cell(0, 10, 'Résumé du Stock', 0, 1, 'L');
  $pdf->SetFont('Arial', '', 10);
  $pdf->Cell(0, 10, 'Nombre total de produits: ' . count($products), 0, 1, 'L');
  $pdf->Cell(0, 10, 'Produits en rupture de stock: ' . count($outOfStock), 0, 1, 'L');
  $pdf->Cell(0, 10, 'Produits à faible stock: ' . count($lowStock), 0, 1, 'L');
  $pdf->Cell(0, 10, 'Valeur totale du stock: ' . formatPrice($stockValue), 0, 1, 'L');
  $pdf->Ln(10);
  
  // Liste des produits
  $pdf->SetFont('Arial', 'B', 12);
  $pdf->Cell(0, 10, 'Liste des Produits', 0, 1, 'L');
  
  $pdf->SetFont('Arial', 'B', 10);
  $pdf->Cell(10, 10, 'ID', 1, 0, 'C');
  $pdf->Cell(80, 10, 'Désignation', 1, 0, 'C');
  $pdf->Cell(30, 10, 'Quantité', 1, 0, 'C');
  $pdf->Cell(30, 10, 'Prix d\'achat', 1, 0, 'C');
  $pdf->Cell(30, 10, 'Prix de vente', 1, 1, 'C');
  
  $pdf->SetFont('Arial', '', 10);
  foreach ($products as $product) {
      $pdf->Cell(10, 10, $product['id_produit'], 1, 0, 'C');
      $pdf->Cell(80, 10, $product['designation'], 1, 0, 'L');
      $pdf->Cell(30, 10, $product['quantite'], 1, 0, 'C');
      $pdf->Cell(30, 10, formatPrice($product['prix_achat']), 1, 0, 'R');
      $pdf->Cell(30, 10, formatPrice($product['prix_vente']), 1, 1, 'R');
  }
  
  // Sortie du PDF
  $pdf->Output('rapport_stock_' . date('Y-m-d') . '.pdf', 'D');
  exit;
}

/**
* Rechercher des produits
*/
public function search() {
  // Vérifier les droits d'accès
  $this->authController->checkAccess('any');
  
  // Récupérer le terme de recherche
  $searchTerm = $_GET['q'] ?? '';
  
  if (empty($searchTerm)) {
      header('Location: ' . APP_URL . '/products');
      exit;
  }
  
  // Rechercher les produits
  $products = $this->productModel->search($searchTerm);
  
  // Définir le titre de la page
  $pageTitle = 'Résultats de recherche pour "' . htmlspecialchars($searchTerm) . '"';
  
  // Définir les boutons d'action
  $actionButtons = '
      <a href="' . APP_URL . '/products/create" class="btn btn-primary">
          <i class="bi bi-plus"></i> Ajouter un produit
      </a>
  ';
  
  // Afficher la vue
  require_once BASE_PATH . '/views/layouts/header.php';
  require_once BASE_PATH . '/views/products/index.php';
  require_once BASE_PATH . '/views/layouts/footer.php';
}

/**
* Afficher les statistiques des produits
*/
public function statistics() {
    // Vérifier les droits d'accès
    $this->authController->checkAccess('any');
    
    // Récupérer les statistiques
    $totalProducts = $this->productModel->countProducts();
    $totalValue = $this->productModel->getTotalStockValue();
    $outOfStockCount = $this->productModel->getOutOfStock();
    $lowStockCount = $this->productModel->getLowStock();
    $mostSoldProducts = $this->productModel->getMostSold(5);
    $leastSoldProducts = $this->productModel->getLeastSoldProducts(5);
    $mostProfitableProducts = $this->productModel->getMostProfitableProducts(5);
    
    // Définir le titre de la page
    $pageTitle = 'Statistiques des Produits';
    
    // Afficher la vue
    require_once BASE_PATH . '/views/layouts/header.php';
    require_once BASE_PATH . '/views/products/statistics.php';
    require_once BASE_PATH . '/views/layouts/footer.php';
  }

  
}
