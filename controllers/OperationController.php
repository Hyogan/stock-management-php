<?php
class OperationController{
  /**
 * Contrôleur des Opérations
 * Gère toutes les opérations liées aux mouvements de stock
 */
class OperationController {
  private $operationModel;
  private $productModel;
  private $authController;
  
  public function __construct() {
      $this->operationModel = new Operation();
      $this->productModel = new Product();
      $this->authController = new AuthController();
  }
  
  /**
   * Afficher la liste des opérations
   */
  public function index() {
      // Vérifier les droits d'accès
      $this->authController->checkAccess('any');
      
      // Récupérer toutes les opérations
      $operations = $this->operationModel->getAll();
      
      // Définir le titre de la page
      $pageTitle = 'Journal des Opérations';
      
      // Définir les boutons d'action
      $actionButtons = '';
      
      if ($this->authController->isAdmin() || $this->authController->isStorekeeper()) {
          $actionButtons .= '
              <a href="' . APP_URL . '/operations/create" class="btn btn-primary">
                  <i class="bi bi-plus"></i> Nouvelle opération
              </a>
          ';
      }
      
      // Afficher la vue
      require_once BASE_PATH . '/views/layouts/header.php';
      require_once BASE_PATH . '/views/operations/index.php';
      require_once BASE_PATH . '/views/layouts/footer.php';
  }
  
  /**
   * Afficher le formulaire de création d'une opération
   */
  public function create() {
      // Vérifier les droits d'accès
      if (!$this->authController->isAdmin() && !$this->authController->isStorekeeper()) {
          $this->authController->checkAccess('admin');
      }
      
      // Récupérer tous les produits
      $products = $this->productModel->getAll();
      
      // Définir le titre de la page
      $pageTitle = 'Nouvelle Opération de Stock';
      
      // Afficher la vue
      require_once BASE_PATH . '/views/layouts/header.php';
      require_once BASE_PATH . '/views/operations/create.php';
      require_once BASE_PATH . '/views/layouts/footer.php';
  }
  
  /**
   * Traiter la création d'une opération
   */
  public function store() {
      // Vérifier les droits d'accès
      if (!$this->authController->isAdmin() && !$this->authController->isStorekeeper()) {
          $this->authController->checkAccess('admin');
      }
      
      // Vérifier si le formulaire a été soumis
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          // Récupérer les données du formulaire
          $operationData = [
              'id_produit' => $_POST['id_produit'] ?? 0,
              'type_operation' => $_POST['type_operation'] ?? '',
              'quantite' => $_POST['quantite'] ?? 0,
              'motif' => $_POST['motif'] ?? '',
              'id_utilisateur' => $_SESSION['user_id']
          ];
          
          // Valider les données
          $errors = [];
          
          if (empty($operationData['id_produit']) || $operationData['id_produit'] <= 0) {
              $errors[] = "Veuillez sélectionner un produit.";
          }
          
          if (empty($operationData['type_operation'])) {
              $errors[] = "Veuillez sélectionner un type d'opération.";
          }
          
          if (!is_numeric($operationData['quantite']) || $operationData['quantite'] <= 0) {
              $errors[] = "La quantité doit être un nombre positif.";
          }
          
          // Vérifier si le produit existe
          $product = $this->productModel->getById($operationData['id_produit']);
          if (!$product) {
              $errors[] = "Le produit sélectionné n'existe pas.";
          }
          
          // Vérifier si la quantité est suffisante pour une sortie
          if ($operationData['type_operation'] === 'sortie' && $product && $operationData['quantite'] > $product['quantite']) {
              $errors[] = "La quantité en stock est insuffisante pour cette opération.";
          }
          
          // S'il y a des erreurs, afficher le formulaire avec les erreurs
          if (!empty($errors)) {
              $error = implode('<br>', $errors);
              $pageTitle = 'Nouvelle Opération de Stock';
              $products = $this->productModel->getAll();
              
              require_once BASE_PATH . '/views/layouts/header.php';
              require_once BASE_PATH . '/views/operations/create.php';
              require_once BASE_PATH . '/views/layouts/footer.php';
              return;
          }
          
          try {
              // Créer l'opération
              $operationId = $this->operationModel->create($operationData);
              
              // Mettre à jour le stock du produit
              if ($operationData['type_operation'] === 'entree') {
                  $this->productModel->updateStock($operationData['id_produit'], $operationData['quantite'], '+');
              } else {
                  $this->productModel->updateStock($operationData['id_produit'], $operationData['quantite'], '-');
              }
              
              // Rediriger vers la liste des opérations
              $_SESSION['success'] = "L'opération a été enregistrée avec succès.";
              header('Location: ' . APP_URL . '/operations');
              exit;
          } catch (Exception $e) {
              // Afficher le formulaire avec l'erreur
              $error = "Erreur lors de l'enregistrement de l'opération : " . $e->getMessage();
              $pageTitle = 'Nouvelle Opération de Stock';
              $products = $this->productModel->getAll();
              
              require_once BASE_PATH . '/views/layouts/header.php';
              require_once BASE_PATH . '/views/operations/create.php';
              require_once BASE_PATH . '/views/layouts/footer.php';
          }
      } else {
          // Rediriger vers le formulaire de création
          header('Location: ' . APP_URL . '/operations/create');
          exit;
      }
  }
  
  /**
   * Afficher les détails d'une opération
   */
  public function show($id) {
      // Vérifier les droits d'accès
      $this->authController->checkAccess('any');
      
      // Récupérer l'opération
      $operation = $this->operationModel->getById($id);
      
      if (!$operation) {
          $_SESSION['error'] = "L'opération demandée n'existe pas.";
          header('Location: ' . APP_URL . '/operations');
          exit;
      }
      
      // Récupérer le produit associé
      $product = $this->productModel->getById($operation['id_produit']);
      
      // Récupérer l'utilisateur qui a effectué l'opération
      $user = $this->operationModel->getUser($operation['id_utilisateur']);
      
      // Définir le titre de la page
      $pageTitle = 'Détails de l\'Opération #' . $operation['id_operation'];
      
      // Afficher la vue
      require_once BASE_PATH . '/views/layouts/header.php';
      require_once BASE_PATH . '/views/operations/show.php';
      require_once BASE_PATH . '/views/layouts/footer.php';
  }
  
  /**
   * Filtrer les opérations par date
   */
  public function filter() {
      // Vérifier les droits d'accès
      $this->authController->checkAccess('any');
      
      // Récupérer les dates du formulaire
      $startDate = $_GET['start_date'] ?? '';
      $endDate = $_GET['end_date'] ?? '';
      $productId = $_GET['product_id'] ?? '';
      $operationType = $_GET['operation_type'] ?? '';
      
      // Valider les dates
      if (empty($startDate) || empty($endDate)) {
          $_SESSION['error'] = "Veuillez spécifier une période.";
          header('Location: ' . APP_URL . '/operations');
          exit;
      }
      
      // Récupérer les opérations filtrées
      $operations = $this->operationModel->filter($startDate, $endDate, $productId, $operationType);
      
      // Définir le titre de la page
      $pageTitle = 'Opérations du ' . formatDate($startDate, 'd/m/Y') . ' au ' . formatDate($endDate, 'd/m/Y');
      
      // Définir les boutons d'action
      $actionButtons = '';
      
      if ($this->authController->isAdmin() || $this->authController->isStorekeeper()) {
          $actionButtons .= '
              <a href="' . APP_URL . '/operations/create" class="btn btn-primary">
                  <i class="bi bi-plus"></i> Nouvelle opération
              </a>
          ';
      }
      
      // Afficher la vue
      require_once BASE_PATH . '/views/layouts/header.php';
      require_once BASE_PATH . '/views/operations/index.php';
      require_once BASE_PATH . '/views/layouts/footer.php';
  }
  
  /**
   * Exporter les opérations au format CSV
   */
  public function exportCsv() {
      // Vérifier les droits d'accès
      $this->authController->checkAccess('any');
      
      // Récupérer les paramètres de filtre
      // <!-- $startDate = $_GET['start_date'] ?? '';
      // $endDate = $_GET['en ']
  }
}
