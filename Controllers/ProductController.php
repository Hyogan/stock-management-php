<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Utils\Auth;
use App\Models\Category;
use App\Models\Supplier;
class ProductController extends Controller{
    /**
     * Afficher la liste des produits
     */
    public function index()
     {
      // Vérifier si l'utilisateur est connecté
      if (!isset($_SESSION['user_id'])) {
          $this->redirect('/login');
          exit;
      }
      
      // Récupérer les paramètres de filtrage et de tri
      $search = $_GET['search'] ?? '';
      $category = $_GET['category'] ?? '';
      $sort = $_GET['sort'] ?? 'designation';
      $order = $_GET['order'] ?? 'asc';
      
      // Récupérer les produits
      $products = [];
      
      if (!empty($search)) {
          $products = Product::search($search);
      } elseif (!empty($category)) {
          $products = Product::getAllByCategory($category);
      } else {
          $products = Product::getAllSorted($sort, $order);
      }
      // Récupérer les catégories pour le filtre
      $categories = Category::getAll();
      // Définir le titre de la page
      $pageTitle = 'Gestion des produits';
    $authController = new AuthController();
      $data = [
        'pageTitle' => $pageTitle,
        'products' => $products,
        'categories' => $categories,
        'search' => $search,
        'category' => $category,
        'sort' => $sort,
        'order' => $order,
        'authController' => $authController
      ];
      // var_dump($data['products']);
      $this->view('products/index', $data,'admin');
  }
    
    /**
     * Afficher le formulaire d'ajout de produit
     */
   public function create() {
       // Vérifier si l'utilisateur est connecté
       if (!isset($_SESSION['user_id'])) {
           $this->redirect('/login');
           exit;
       }   
       // Vérifier les permissions
       if (!Auth::isAdmin() && !Auth::isStorekeeper()) {
           $this->redirect('/products');
           exit;
       }
       // Récupérer les catégories
       $categories = Category::getAll();
       // Récupérer les fournisseurs
       $suppliers = Supplier::getAll();
       // Définir le titre de la page
       $pageTitle = 'Ajouter un produit';
       
       // Afficher la vue
       $this->view('products/create', [
           'pageTitle' => $pageTitle,
           'categories' => $categories,
           'suppliers' => $suppliers
       ],'admin');
   }
    /**
     * Enregistre un nouveau produit
     */
    public function store() {
      // Vérifier si l'utilisateur est connecté
      if (!isset($_SESSION['user_id'])) {
          $this->redirect('/login');
          exit;
      }
      
      // Vérifier les permissions
      if (!Auth::isAdmin() && !Auth::isStorekeeper()) {
          $this->redirect('/products');
          exit;
      }
      
      // Vérifier si le formulaire a été soumis
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
          $this->redirect('/products/create');
          exit;
      }
      
      // Récupérer les données du formulaire
      $data = [
          'reference' => $_POST['reference'] ?? '',
          'designation' => $_POST['designation'] ?? '',
          'description' => $_POST['description'] ?? '',
          'unite' => $_POST['unite'] ?? '',
          'prix_achat' => $_POST['prix_achat'] ?? 0,
          'prix_vente' => $_POST['prix_vente'] ?? 0,
          'quantite_stock' => $_POST['quantite_stock'] ?? 0,
          'quantite_alerte' => $_POST['quantite_alerte'] ?? 5,
          'id_categorie' => $_POST['id_categorie'] ?? null,
          'id_fournisseur' => $_POST['id_fournisseur'] ?? null,
          'statut' => $_POST['statut'] ?? 'actif',
          'date_creation' => date('Y-m-d H:i:s'),
          'id_utilisateur' => $_SESSION['user_id']
      ];
      
      // Valider les données
      $errors = [];
      
      if (empty($data['reference'])) {
          $errors['reference'] = 'La référence est obligatoire';
      } elseif (Product::referenceExists($data['reference'])) {
          $errors['reference'] = 'Cette référence existe déjà';
      }
      
      if (empty($data['designation'])) {
          $errors['designation'] = 'La désignation est obligatoire';
      }
      
      if ($data['prix_achat'] < 0) {
          $errors['prix_achat'] = 'Le prix d\'achat doit être positif';
      }
      
      if ($data['prix_vente'] < 0) {
          $errors['prix_vente'] = 'Le prix de vente doit être positif';
      }
      
      if ($data['quantite_stock'] < 0) {
          $errors['quantite_stock'] = 'La quantité en stock doit être positive';
      }
      
      if ($data['quantite_alerte'] < 0) {
          $errors['quantite_alerte'] = 'La quantité d\'alerte doit être positive';
      }
      
      // S'il y a des erreurs, afficher le formulaire avec les erreurs
      if (!empty($errors)) {
          // Récupérer les catégories
          $categories = Category::getAll();
          
          // Récupérer les fournisseurs
          $suppliers = Supplier::getAll();
          
          $this->view('products/create', [
              'pageTitle' => 'Ajouter un produit',
              'categories' => $categories,
              'suppliers' => $suppliers,
              'data' => $data,
              'errors' => $errors
          ]);
          return;
      }
      
      // Traiter l'image si elle existe
      if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
          $uploadDir = BASE_PATH . '/public/uploads/products/';
          $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
          $uploadFile = $uploadDir . $fileName;
          
          // Vérifier si le répertoire existe, sinon le créer
          if (!is_dir($uploadDir)) {
              mkdir($uploadDir, 0777, true);
          }
          
          // Déplacer le fichier
          if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
              $data['image'] = '/uploads/products/' . $fileName;
          }
      }
      
      // Enregistrer le produit
      $productId = Product::add($data);
      // Ajouter un mouvement de stock initial si la quantité est > 0
      if ($data['quantite_stock'] > 0) {
          Product::addStockMovement($productId, $data['quantite_stock'], 'entree', 'Stock initial', $_SESSION['user_id']);
      }
      
      // Rediriger vers la liste des produits avec un message de succès
      flash('success', 'Le produit a été ajouté avec succès');
      $this->redirect('/products');
  }
   /**
     * Affiche les détails d'un produit
     */
    public function show($productId) {
      // Vérifier si l'utilisateur est connecté
      if (!isset($_SESSION['user_id'])) {
          $this->redirect('/login');
          exit;
      }
      if (!$productId) {
          $this->redirect('/products');
          exit;
      }
      
      // Récupérer le produit
      $product = Product::getById($productId);
      
      if (!$product) {
          flash('error', 'Produit non trouvé');
          $this->redirect('/products');
          exit;
      }
      
      // Récupérer la catégorie
      $category = null;
      if ($product['id_categorie']) {
          $category = Category::getById($product['id_categorie']);
      }
      
      // Récupérer le fournisseur
      $supplier = null;
      if ($product['id_fournisseur']) {
          $supplier = Supplier::getById($product['id_fournisseur']);
      }
      
      // Récupérer les mouvements de stock
      $stockMovements = Product::getStockMovements($productId);
      // Définir le titre de la page
      $pageTitle = 'Détails du produit: ' . $product['designation'];
      // Afficher la vue
      // dd($stockMovements);
      $this->view('products/show', [
          'pageTitle' => $pageTitle,
          'product' => $product,
          'category' => $category,
          'supplier' => $supplier,
          'stockMovements' => $stockMovements
      ],'admin');
  }
  
    
    /**
     * Affiche le formulaire de modification d'un produit
     */
    public function edit($productId) 
    {
      // Vérifier si l'utilisateur est connecté
      if (!isset($_SESSION['user_id'])) {
          $this->redirect('/login');
          exit;
      }
      
      // Vérifier les permissions
      if (!Auth::isAdmin() && !Auth::isStorekeeper()) {
          $this->redirect('/products');
          exit;
      }
      
      // Récupérer l'ID du produit
      $id = $productId ?? null;
      if (!$id) {
          $this->redirect('/products');
          exit;
      }
      // Récupérer le produit
      $product = Product::getById($id);
      
      if (!$product) {
          flash('error', 'Produit non trouvé');
          $this->redirect('/products');
          exit;
      }
      
      // Récupérer les catégories
      $categories = Category::getAll();
      
      // Récupérer les fournisseurs
      $suppliers = Supplier::getAll();
      
      // Définir le titre de la page
      $pageTitle = 'Modifier le produit: ' . $product['designation'];
      
      // Afficher la vue
      $this->view('products/edit', [
          'pageTitle' => $pageTitle,
          'product' => $product,
          'categories' => $categories,
          'suppliers' => $suppliers
      ], 'admin');
  }
    
    /**
     * Traiter la modification d'un produit
     */
  /**
     * Met à jour un produit
     */
    public function update($productId) {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            exit;
        }
        
        // Vérifier les permissions
        if (!Auth::isAdmin() && !Auth::isStorekeeper()) {
            $this->redirect('/products');
            exit;
        }
        
        // Vérifier si le formulaire a été soumis
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/products');
            exit;
        }
        
        // Récupérer l'ID du produit
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            $this->redirect('/products');
            exit;
        }
        
        // Récupérer le produit existant
        $product = Product::getById($id);
        
        if (!$product) {
            flash('error', 'Produit non trouvé');
            $this->redirect('/products');
            exit;
        }
        
        // Récupérer les données du formulaire
        $data = [
            'reference' => $_POST['reference'] ?? '',
            'designation' => $_POST['designation'] ?? '',
            'description' => $_POST['description'] ?? '',
            'prix_achat' => $_POST['prix_achat'] ?? 0,
            'prix_vente' => $_POST['prix_vente'] ?? 0,
            'quantite_alerte' => $_POST['quantite_alerte'] ?? 5,
            'id_categorie' => $_POST['id_categorie'] ?? null,
            'id_fournisseur' => $_POST['id_fournisseur'] ?? null,
            'statut' => $_POST['statut'] ?? 'actif',
            'date_modification' => date('Y-m-d H:i:s')
        ];
        
        // Valider les données
        $errors = [];
        
        if (empty($data['reference'])) {
            $errors['reference'] = 'La référence est obligatoire';
        } elseif ($data['reference'] !== $product['reference'] && Product::referenceExists($data['reference'])) {
            $errors['reference'] = 'Cette référence existe déjà';
        }
        
        if (empty($data['designation'])) {
            $errors['designation'] = 'La désignation est obligatoire';
        }
        
        if ($data['prix_achat'] < 0) {
            $errors['prix_achat'] = 'Le prix d\'achat doit être positif';
        }
        
        if ($data['prix_vente'] < 0) {
            $errors['prix_vente'] = 'Le prix de vente doit être positif';
        }
        
        if ($data['quantite_alerte'] < 0) {
            $errors['quantite_alerte'] = 'La quantité d\'alerte doit être positive';
        }
        
        // S'il y a des erreurs, afficher le formulaire avec les erreurs
        if (!empty($errors)) {
            // Récupérer les catégories
  // Récupérer les catégories
  $categories = Category::getAll();
              
  // Récupérer les fournisseurs
  $suppliers = Supplier::getAll();
  
  $this->view('products/edit', [
      'pageTitle' => 'Modifier le produit',
      'product' => $product,
      'categories' => $categories,
      'suppliers' => $suppliers,
      'errors' => $errors
  ]);
  return;
  }

  // Traiter l'image si elle existe
  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
  $uploadDir = BASE_PATH . '/public/uploads/products/';
  $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
  $uploadFile = $uploadDir . $fileName;
  
  // Vérifier si le répertoire existe, sinon le créer
  if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
  }
  
  // Déplacer le fichier
  if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
      // Supprimer l'ancienne image si elle existe
      if (!empty($product['image']) && file_exists(BASE_PATH . '/public' . $product['image'])) {
          unlink(BASE_PATH . '/public' . $product['image']);
      }      // Rediriger vers la liste des produits avec un message de succès
      flash('success', 'Le produit a été ajouté avec succès');
      $data['image'] = '/uploads/products/' . $fileName;
  }
}

  // Mettre à jour le produit
  Product::update($id, $data);

  // Rediriger vers la liste des produits avec un message de succès
  flash('success', 'Le produit a été mis à jour avec succès');
  $this->redirect('/products');
}    
    /**
     * Traiter la suppression d'un produit
     */
 /**
     * Supprime un produit
     */
    public function delete() {
      // Vérifier si l'utilisateur est connecté
      if (!isset($_SESSION['user_id'])) {
          $this->redirect('/login');
          exit;
      }
      
      // Vérifier les permissions
      if (!Auth::isAdmin()) {
          $this->redirect('/products');
          exit;
      }
      
      // Vérifier si le formulaire a été soumis
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
          $this->redirect('/products');
          exit;
      }
      
      // Récupérer l'ID du produit
      $id = $_POST['id'] ?? null;
      
      if (!$id) {
          $this->redirect('/products');
          exit;
      }
      
      // Récupérer le produit
      $product = Product::getById($id);
      
      if (!$product) {
          flash('error', 'Produit non trouvé');
          $this->redirect('/products');
          exit;
      }
      
      // Supprimer l'image si elle existe
      if (!empty($product['image']) && file_exists(BASE_PATH . '/public' . $product['image'])) {
          unlink(BASE_PATH . '/public' . $product['image']);
      }
      
      // Supprimer le produit
      Product::delete($id);
      
      // Rediriger vers la liste des produits avec un message de succès
      flash('success', 'Le produit a été supprimé avec succès');
      $this->redirect('/products');
  }
    
    /**
     * Afficher la page de gestion du stock
     */
  /**
     * Affiche le formulaire d'ajout de stock
     */
    public function addStockForm() {
      // Vérifier si l'utilisateur est connecté
      if (!isset($_SESSION['user_id'])) {
          $this->redirect('/login');
          exit;
      }
      
      // Vérifier les permissions
      if (!Auth::isAdmin() && !Auth::isStorekeeper()) {
          $this->redirect('/products');
          exit;
      }
      
      // Récupérer l'ID du produit
      $id = $_GET['id'] ?? null;
      
      if (!$id) {
          $this->redirect('/products');
          exit;
      }
      
      // Récupérer le produit
      $product = Product::getById($id);
      
      if (!$product) {
          flash('error', 'Produit non trouvé');
          $this->redirect('/products');
          exit;
      }
      
      // Définir le titre de la page
      $pageTitle = 'Ajouter du stock: ' . $product['designation'];
      
      // Afficher la vue
      $this->view('products/add_stock', [
          'pageTitle' => $pageTitle,
          'product' => $product
      ]);
  }
    
    /**
     * Afficher le formulaire d'ajustement de stock
     */
      /**
     * Ajoute du stock à un produit
     */
    public function addStock() {
      // Vérifier si l'utilisateur est connecté
      if (!isset($_SESSION['user_id'])) {
          $this->redirect('/login');
          exit;
      }
      
      // Vérifier les permissions
      if (!Auth::isAdmin() && !Auth::isStorekeeper()) {
          $this->redirect('/products');
          exit;
      }
      
      // Vérifier si le formulaire a été soumis
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
          $this->redirect('/products');
          exit;
      }
      
      // Récupérer les données du formulaire
      $productId = $_POST['product_id'] ?? null;
      $quantity = $_POST['quantity'] ?? 0;
      $reason = $_POST['reason'] ?? 'Approvisionnement';
      
      if (!$productId) {
          $this->redirect('/products');
          exit;
      }
      
      // Récupérer le produit
      $product = Product::getById($productId);
      
      if (!$product) {
          flash('error', 'Produit non trouvé');
          $this->redirect('/products');
          exit;
      }
      
      // Valider la quantité
      if ($quantity <= 0) {
          $this->view('products/add_stock', [
              'pageTitle' => 'Ajouter du stock: ' . $product['designation'],
              'product' => $product,
              'quantity' => $quantity,
              'reason' => $reason,
              'error' => 'La quantité doit être supérieure à 0'
          ]);
          return;
      }
      
      // Ajouter le mouvement de stock
      Product::addStockMovement($productId, $quantity, 'entree', $reason, $_SESSION['user_id']);
      
      // Rediriger vers la page du produit avec un message de succès
      flash('success', 'Le stock a été ajouté avec succès');
      $this->redirect('/products/show?id=' . $productId);
  }

     /**
     * Affiche le formulaire de retrait de stock
     */
    public function removeStockForm() {
      // Vérifier si l'utilisateur est connecté
      if (!isset($_SESSION['user_id'])) {
          $this->redirect('/login');
          exit;
      }
      
      // Vérifier les permissions
      if (!Auth::isAdmin() && !Auth::isStorekeeper()) {
          $this->redirect('/products');
          exit;
      }
      
      // Récupérer l'ID du produit
      $id = $_GET['id'] ?? null;
      
      if (!$id) {
          $this->redirect('/products');
          exit;
      }
      
      // Récupérer le produit
      $product = Product::getById($id);
      
      if (!$product) {
          flash('error', 'Produit non trouvé');
          $this->redirect('/products');
          exit;
      }
      
      // Définir le titre de la page
      $pageTitle = 'Retirer du stock: ' . $product['designation'];
      
      // Afficher la vue
      $this->view('products/remove_stock', [
          'pageTitle' => $pageTitle,
          'product' => $product
      ]);
  }
    
    /**
     * Traiter l'ajustement de stock
     */
   /**
   /**
     * Retire du stock d'un produit
     */
    public function removeStock() {
      // Vérifier si l'utilisateur est connecté
      if (!isset($_SESSION['user_id'])) {
          $this->redirect('/login');
          exit;
      }
      
      // Vérifier les permissions
      if (!Auth::isAdmin() && !Auth::isStorekeeper()) {
          $this->redirect('/products');
          exit;
      }
      
      // Vérifier si le formulaire a été soumis
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
          $this->redirect('/products');
          exit;
      }
      
      // Récupérer les données du formulaire
      $productId = $_POST['product_id'] ?? null;
      $quantity = $_POST['quantity'] ?? 0;
      $reason = $_POST['reason'] ?? 'Sortie manuelle';
      
      if (!$productId) {
          $this->redirect('/products');
          exit;
      }
      
      // Récupérer le produit
      $product = Product::getById($productId);
      
      if (!$product) {
          flash('error', 'Produit non trouvé');
          $this->redirect('/products');
          exit;
      }
      
      // Valider la quantité
      if ($quantity <= 0) {
          $this->view('products/remove_stock', [
              'pageTitle' => 'Retirer du stock: ' . $product['designation'],
              'product' => $product,
              'quantity' => $quantity,
              'reason' => $reason,
              'error' => 'La quantité doit être supérieure à 0'
          ]);
          return;
      }
      
      // Vérifier si la quantité est disponible
      if ($quantity > $product['quantite_stock']) {
          $this->view('products/remove_stock', [
              'pageTitle' => 'Retirer du stock: ' . $product['designation'],
              'product' => $product,
              'quantity' => $quantity,
              'reason' => $reason,
              'error' => 'La quantité demandée dépasse le stock disponible'
          ]);
          return;
      }
      
      // Ajouter le mouvement de stock
      Product::addStockMovement($productId, $quantity, 'sortie', $reason, $_SESSION['user_id']);
      
      // Rediriger vers la page du produit avec un message de succès
      flash('success', 'Le stock a été retiré avec succès');
      $this->redirect('/products/show?id=' . $productId);
  }

   /**
     * Affiche l'historique des mouvements de stock
     */
    public function stockHistory() {
      // Vérifier si l'utilisateur est connecté
      if (!isset($_SESSION['user_id'])) {
          $this->redirect('/login');
          exit;
      }
      
      // Récupérer les mouvements de stock récents
      $movements = Product::getRecentMovements(50);
      
      // Définir le titre de la page
      $pageTitle = 'Historique des Mouvements de Stock';
      
      // Afficher la vue
      $this->view('products/stock_history', [
          'pageTitle' => $pageTitle,
          'movements' => $movements
      ]);
    }

    
/**
* Exporter la liste des produits au format CSV
*/
public function export() {
 // Vérifier les droits d'accès
 $authController = new AuthController();
 if (!Auth::isAdmin() && !Auth::isStorekeeper()) {
     $authController->checkAccess('admin');
 }
 
 // Récupérer tous les produits
 $products = Product::getAll();
 
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
  if (!Auth::isAdmin()) {
      // checkAccess('admin');
  }
 
 // Récupérer les statistiques
  $stats = [
      'total_products' => Product::countProducts(),
      'out_of_stock' => count(Product::getOutOfStock()),
      'low_stock' => count(Product::getLowStock()),
      'total_value' => Product::getTotalStockValue(),
      'most_expensive' => Product::getMostExpensiveProduct(),
      'most_in_stock' => Product::getMostInStockProduct(),
      'recent_movements' => Product::getRecentMovements(10)
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
  $authController = new AuthController();
  $authController->checkAccess('any');
  
  // Récupérer les produits à faible stock
  $lowStock = Product::getLowStock();
  
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
  $authController = new AuthController();
  $authController->checkAccess('any');

  
  // Récupérer les produits en rupture de stock
  $outOfStock = Product::getOutOfStock();
  
  // Définir le titre de la page
  $pageTitle = 'Produits en Rupture de Stock';
  
  // Afficher la vue
  require_once BASE_PATH . '/views/layouts/header.php';
  require_once BASE_PATH . '/views/products/out_of_stock.php';
  require_once BASE_PATH . '/views/layouts/footer.php';
}

/**
* Exporter la liste des produits au format CSV
*/
public function exportCsv() {
  // Vérifier les droits d'accès
  $authController = new AuthController();
  $authController->checkAccess('any');
  
  // Récupérer tous les produits
  $products = Product::getAll();
  
  // Définir les en-têtes pour le téléchargement
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=produits_' . date('Y-m-d') . '.csv');
  
  // Créer le flux de sortie
  $output = fopen('php://output', 'w');
  
  // Ajouter l'en-tête UTF-8 BOM pour Excel
  fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
  
  // Ajouter les en-têtes de colonnes
  fputcsv($output, ['ID', 'Désignation','unite','Catégorie','Fournisseur', 'Quantité Stock','Quantité Alerte', 'Prix d\'achat', 'Prix de vente', 'Date de création']);
  
  // Ajouter les données
  foreach ($products as $product) {
      fputcsv($output, [
          $product['id'],
          $product['designation'],
          $product['unite'],
          $product['categorie_nom'],
          $product['fournisseur_nom'],
          $product['quantite_stock'],
          $product['quantite_alerte'],
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
  $authController =  new AuthController();
  $authController->checkAccess('any');
  
  // Récupérer les données nécessaires
  $products = Product::getAll();
  $outOfStock = Product::getOutOfStock();
  $lowStock = Product::getLowStock();
  $stockValue = Product::getTotalStockValue();
  
  // Créer le PDF (utilisation d'une bibliothèque comme FPDF ou TCPDF)
  require_once BASE_PATH . '/lib/fpdf/fpdf.php';
  
  $pdf = new \FPDF();
  $pdf->AddPage();
  
  // En-tête
  $pdf->SetFont('Arial', 'B', 16);
  $pdf->Cell(0, 10, 'Rapport de Stock', 0, 1, 'C');
  $pdf->Cell(0, 10, 'Date: ' . date('d/m/Y'), 0, 1, 'C');
  $pdf->Ln(10);
  
  // Résumé
  $pdf->SetFont('Arial', 'B', 12);
  $pdf->Cell(0, 10, 'Resume du Stock', 0, 1, 'L');
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
  $pdf->Cell(80, 10, 'Designation', 1, 0, 'C');
  $pdf->Cell(30, 10, 'Quantite', 1, 0, 'C');
  $pdf->Cell(30, 10, 'Prix d\'achat', 1, 0, 'C');
  $pdf->Cell(30, 10, 'Prix de vente', 1, 1, 'C');
  
  $pdf->SetFont('Arial', '', 10);
  foreach ($products as $product) {
      $pdf->Cell(10, 10, $product['id'], 1, 0, 'C');
      $pdf->Cell(80, 10, $product['designation'], 1, 0, 'L');
      $pdf->Cell(30, 10, $product['quantite_stock'], 1, 0, 'C');
      $pdf->Cell(30, 10, formatPrice($product['prix_achat']), 1, 0, 'R');
      $pdf->Cell(30, 10, formatPrice($product['prix_vente']), 1, 1, 'R');
  }
  
  // Sortie du PDF
  $pdf->Output('rapport_stock_' . date('Y-m-d') . '.pdf', 'D');
  // return $this->redirect('/products');
  exit;
}

/**
* Rechercher des produits
*/
public function search() {
  // Vérifier les droits d'accès
  if(!Auth::isAdmin())  {
    return ;
  }
  // Récupérer le terme de recherche
  $searchTerm = $_GET['q'] ?? '';
  if (empty($searchTerm)) {
      header('Location: ' . APP_URL . '/products');
      // return view('products/')
      exit;
  }
}
/**
 * Recherche avancée de produits
 */
public function searchAdvanced() {
  // Vérifier si l'utilisateur est connecté
  if (!isset($_SESSION['user_id'])) {
      return $this->view('login');
  }

  // Récupérer les paramètres de recherche
  $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
  $category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
  $minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
  $maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
  $inStock = isset($_GET['in_stock']) ? (int)$_GET['in_stock'] : -1;
  // Effectuer la recherche
  $products = Product::searchAdvanced($keyword, $category, $minPrice, $maxPrice, $inStock);
  // Récupérer les catégories pour le formulaire de recherche
  $categories = Category::getAll();
  // Afficher la vue des résultats de recherche
  return $this->view('products/search_results', [
    'categories' => $categories,
    'products' => $products
  ]);
}
/**
* Afficher les statistiques des produits
*/
public function statistics() {
    // Vérifier les droits d'accès
    $authController = new AuthController();
    $authController->checkAccess('any');
    // Définir le titre de la page
    $pageTitle = 'Statistiques des Produits';
    $data = [
        'pageTitle' => $pageTitle,
        'totalProducts' => Product::countProducts(),
        'totalValue' => Product::getTotalStockValue(),
        'outOfStocks' => Product::getOutOfStock(),
        'outOfStocksCount' => count(Product::getOutOfStock()),
        'lowStockCount' => count(Product::getLowStock()),
        'lowStock' => Product::getLowStock(),
        'mostSoldProducts' => Product::getMostSold(5),
        'leastSoldProducts' => Product::getLeastSoldProducts(5),
        'mostProfitableProducts' => Product::getMostProfitableProducts(5),
    ];
    // dd($data);
    return $this->view('products/stats',$data,'admin');
  }

  
}
