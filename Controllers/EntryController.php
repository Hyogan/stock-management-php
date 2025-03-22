<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Entry;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Client;
use App\Utils\Auth;

class EntryController extends Controller {
    private $entryModel;

    public function __construct() {
        $this->entryModel = new Entry();
    }

    /**
     * Afficher la liste des entrées
     */
    public function index() {
        Auth::checkAccess('any');

        $entries = Entry::getAll();
        $pageTitle = 'Liste des Entrées de Stock';
        // dd($entries);
        $this->view('stock/entry/index', [
            'pageTitle' => $pageTitle,
            'entries' => $entries,
        ], 'admin');
    }

    /**
     * Afficher le formulaire d'ajout d'une entrée de stock
     */
    public function create() {
        Auth::checkAccess('any');
        $products = Product::getAll();
        $fournisseurs = Supplier::getAll();
        $clients = Client::getAll();

        $pageTitle = 'Ajouter une Entrée de Stock';

        $this->view('stock/entry/create', [
            'pageTitle' => $pageTitle,
            'products' => $products,
            'fournisseurs' => $fournisseurs,
            'clients' => $clients,
        ], 'admin');
    }

    /**
     * Traiter l'ajout d'une entrée de stock
     */
    public function store() {
        Auth::checkAccess('any');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/entries');
            exit;
        }

        $data = [
            'reference' => $_POST['reference'] ?? '',
            'date_entree' => $_POST['date_entree'] ?? '',
            'id_fournisseur' => $_POST['id_fournisseur'] ?? null,
            'montant_total' => $_POST['montant_total'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'id_utilisateur' => $_SESSION['user_id'],
            'id_livraison' => $_POST['id_livraison'] ?? null,
            'id_client' => $_POST['id_client'] ?? null,
        ];

        $details = [];
        if (isset($_POST['details']) && is_array($_POST['details'])) {
          foreach ($_POST['details'] as $detail) {
              $details[] = [
                  'id_produit' => $detail['id_produit'],
                  'quantite' => $detail['quantite'],
                  'prix_unitaire' => $detail['prix_unitaire'],
              ];
          }
      }

      try {
          $entryId = Entry::create($data, $details);

          $_SESSION['success'] = 'L\'entrée de stock a été ajoutée avec succès';
      } catch (\Exception $e) {
          $_SESSION['error'] = 'Erreur lors de l\'ajout de l\'entrée de stock: ' . $e->getMessage();
      }

      $this->redirect('/stock-entries');
  }

  /**
   * Afficher le formulaire de modification d'une entrée de stock
   */
  public function edit($id) {
      Auth::checkAccess('any');

      $entry = Entry::getById($id);
      $products = Product::getAll();
      $fournisseurs = Supplier::getAll();
      $clients = Client::getAll();
      $entryProducts = $this->entryModel->getProducts($id);

      $pageTitle = 'Modifier une Entrée de Stock';

      $this->view('stock/entry/edit', [
          'pageTitle' => $pageTitle,
          'entry' => $entry,
          'products' => $products,
          'fournisseurs' => $fournisseurs,
          'clients' => $clients,
          'entryProducts' => $entryProducts,
      ], 'admin');
  }

  /**
   * Traiter la modification d'une entrée de stock
   */
  public function update($id) {
      Auth::checkAccess('any');

      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
          $this->redirect('/entries');
          exit;
      }

      $data = [
          'reference' => $_POST['reference'] ?? '',
          'date_entree' => $_POST['date_entree'] ?? '',
          'id_fournisseur' => $_POST['id_fournisseur'] ?? null,
          'montant_total' => $_POST['montant_total'] ?? '',
          'notes' => $_POST['notes'] ?? '',
          'id_livraison' => $_POST['id_livraison'] ?? null,
          'id_client' => $_POST['id_client'] ?? null,
      ];

      $details = [];
      if (isset($_POST['details']) && is_array($_POST['details'])) {
          foreach ($_POST['details'] as $detail) {
              $details[] = [
                  'id_produit' => $detail['id_produit'],
                  'quantite' => $detail['quantite'],
                  'prix_unitaire' => $detail['prix_unitaire'],
              ];
          }
      }

      try {
          $this->entryModel->update($id, $data, $details);

          $_SESSION['success'] = 'L\'entrée de stock a été modifiée avec succès';
      } catch (\Exception $e) {
          $_SESSION['error'] = 'Erreur lors de la modification de l\'entrée de stock: ' . $e->getMessage();
      }

      $this->redirect('/entries');
  }

  /**
   * Supprimer une entrée de stock
   */
  public function delete($id) {
      Auth::checkAccess('any');

      try {
          $this->entryModel->delete($id);

          $_SESSION['success'] = 'L\'entrée de stock a été supprimée avec succès';
      } catch (\Exception $e) {
          $_SESSION['error'] = 'Erreur lors de la suppression de l\'entrée de stock: ' . $e->getMessage();
      }

      $this->redirect('/entries');
  }
}
