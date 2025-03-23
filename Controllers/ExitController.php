<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ExitOp;
use App\Models\Product;
use App\Models\Delivery;
use App\Models\Order;
use App\Utils\Auth;

class ExitController extends Controller {
    private $exitModel;

    public function __construct() {
        $this->exitModel = new ExitOp();
    }

    /**
     * Afficher la liste des sorties
     */
    public function index() {
        Auth::checkAccess('any');

        $exits = ExitOp::getAll();

        $pageTitle = 'Liste des Sorties de Stock';

        $this->view('stock/exit/index', [
            'pageTitle' => $pageTitle,
            'exits' => $exits,
        ], 'admin');
    }

    /**
     * Afficher le formulaire d'ajout d'une sortie de stock
     */
    public function create() {
        Auth::checkAccess('any');

        $products = Product::getAll();
        $deliveries = Delivery::where('statut','!=','annulee')->get();
        $orders = Order::where('statut','!=','rejected')
                          ->where('statut','!=','delivered')
                          ->where('statut','!=','cancelled')
                          ->get();
                        // dd(count($orders));
        
        $pageTitle = 'Ajouter une Sortie de Stock';

        $this->view('stock/exit/create', [
            'pageTitle' => $pageTitle,
            'products' => $products,
            'orders' => $orders,
            'deliveries' => $deliveries
        ], 'admin');
    }

    /**
     * Traiter l'ajout d'une sortie de stock
     */
    public function store() {
        Auth::checkAccess('any');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/stock-exits');
            exit;
        }

        $data = [
            'reference' => $_POST['reference'] ?? '',
            'type_sortie' => $_POST['type_sortie'] ?? '',
            'date_sortie' => $_POST['date_sortie'] ?? '',
            'id_commande' => empty($_POST['id_commande']) ? null : $_POST['id_commande'],
            'destination' => $_POST['destination'] ?? 'null',
            'montant_total' => $_POST['montant_total'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'id_utilisateur' => $_SESSION['user_id'],
            'id_livraison' =>  empty($_POST['id_livraison']) ? null : $_POST['id_livraison'],
        ];
        if(
              $data['type_sortie'] == 'vente' && ($data['id_commande']  || $data['id_livraison'])) {
              flash('error','Les champs livraison et commande ne peuvent pas etre vides pour une vente');
              $this->redirect(url: '/exits/create');
        }
        // var_dump($data);

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
            $exitId = ExitOp::create($data, $details);

            $_SESSION['success'] = 'La sortie de stock a été ajoutée avec succès';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur lors de l\'ajout de la sortie de stock: ' . $e->getMessage();
        }

        $this->redirect('/stock-exits');
    }

    /**
     * Afficher le formulaire de modification d'une sortie de stock
     */
    public function edit($id) {
        Auth::checkAccess('any');

        $exit = $this->exitModel->getById($id);
        $products = Product::getAll();
        $exitProducts = $this->exitModel->getProducts($id);

        $pageTitle = 'Modifier une Sortie de Stock';

        $this->view('stock/exit/edit', [
            'pageTitle' => $pageTitle,
            'exit' => $exit,
            'products' => $products,
            'exitProducts' => $exitProducts,
        ], 'admin');
    }

    /**
     * Traiter la modification d'une sortie de stock
     */
    public function update($id) {
        Auth::checkAccess('any');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/exits');
            exit;
        }

        $data = [
            'reference' => $_POST['reference'] ?? '',
            'type_sortie' => $_POST['type_sortie'] ?? '',
            'date_sortie' => $_POST['date_sortie'] ?? '',
            'id_commande' => $_POST['id_commande'] ?? null,
            'destination' => $_POST['destination'] ?? null,
            'montant_total' => $_POST['montant_total'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'id_livraison' => $_POST['id_livraison'] ?? null,
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
            $this->exitModel->update($id, $data, $details);

            $_SESSION['success'] = 'La sortie de stock a été modifiée avec succès';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur lors de la modification de la sortie de stock: ' . $e->getMessage();
        }

        $this->redirect('/exits');
    }

    /**
     * Supprimer une sortie de stock
     */
    public function delete($id) {
        Auth::checkAccess('any');

        try {
            $this->exitModel->delete($id);

            $_SESSION['success'] = 'La sortie de stock a été supprimée avec succès';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur lors de la suppression de la sortie de stock: ' . $e->getMessage();
        }

        $this->redirect('/exits');
    }
}
