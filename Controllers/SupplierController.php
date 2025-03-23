<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Supplier;
use App\Models\Product;
use App\Utils\Session;
use App\Utils\Validator;

class SupplierController extends Controller{
    
    /**
     * Affiche la liste des fournisseurs
     */
    public function index() {
        $suppliers = Supplier::getAll();
        
        // Rendu de la vue avec les données
        $this->view('suppliers/index',
        [
          'suppliers' => $suppliers,
          'title' => 'Gestion des fournisseurs'
        ],'admin');
    }
    
    /**
     * Affiche le formulaire d'ajout d'un fournisseur
     */
    public function create() 
    {
      $this->view('suppliers/create',
      [
        'title' => 'Ajouter un fournisseur',
        'action' => 'add'
      ],'admin');
      
        // return [
        //     'view' => 'suppliers/form',  
        //     'data' => [
        //         'title' => 'Ajouter un fournisseur',
        //         'action' => 'add'
        //     ],'admin'
        // ];
    }
    
    /**
     * Traite l'ajout d'un fournisseur
     */
    public function store() {
        // Validation des données
        $validator = new Validator($_POST);
        $validator->required('nom', 'Le nom est obligatoire');
        
        if (!$validator->isValid()) {
            Session::set('errors', $validator->getErrors());
            Session::set('old', $_POST);
            header('Location: /suppliers/create');
            exit;
        }
        
        // Vérifier si le nom existe déjà
        if (Supplier::existsByName($_POST['nom'])) {
            Session::set('error', 'Un fournisseur avec ce nom existe déjà');
            Session::set('old', $_POST);
            header('Location: /suppliers/create');
            exit;
        }
        
        // Ajouter le fournisseur
        $id = Supplier::add($_POST);
        
        if ($id) {
            Session::set('success', 'Fournisseur ajouté avec succès');
            header('Location: /suppliers');
        } else {
            Session::set('error', 'Erreur lors de l\'ajout du fournisseur');
            Session::set('old', $_POST);
            header('Location: /suppliers/create');
        }
        exit;
    }
    
    /**
     * Affiche le formulaire d'édition d'un fournisseur
     */
    public function edit($id) {
        $supplier = Supplier::getById($id);
        
        if (!$supplier) {
            Session::set('error', 'Fournisseur non trouvé');
            header('Location: /suppliers');
            exit;
        }
        $this->view('suppliers/edit',
                    [
                      'title' => 'Modifier un fournisseur',
                      'action' => 'update',
                      'supplier' => $supplier
                    ],
                    'admin');
    }
    
    /**
     * Traite la modification d'un fournisseur
     */
    public function update($id) {
        $supplier = Supplier::getById($id);
        
        if (!$supplier) {
            Session::set('error', 'Fournisseur non trouvé');
            header('Location: /suppliers');
            exit;
        }
        
        // Validation des données
        $validator = new Validator($_POST);
        $validator->required('nom', 'Le nom est obligatoire');
        
        if (!$validator->isValid()) {
            Session::set('errors', $validator->getErrors());
            Session::set('old', $_POST);
            header("Location: /suppliers/edit/{$id}");
            exit;
        }
        
        // Vérifier si le nom existe déjà
        if (Supplier::existsByName($_POST['nom'], $id)) {
            Session::set('error', 'Un fournisseur avec ce nom existe déjà');
            Session::set('old', $_POST);
            header("Location: /suppliers/edit/{$id}");
            exit;
        }
        
        // Mettre à jour le fournisseur
        $result = Supplier::update($id, $_POST);
        
        if ($result) {
            Session::set('success', 'Fournisseur modifié avec succès');
            header('Location: /suppliers');
        } else {
            Session::set('error', 'Erreur lors de la modification du fournisseur');
            Session::set('old', $_POST);
            header("Location: /suppliers/edit/{$id}");
        }
        exit;
    }
    
    /**
     * Affiche les détails d'un fournisseur
     */
    public function show($id) {
        $supplier = Supplier::getById($id);
        
        if (!$supplier) {
            Session::set('error', 'Fournisseur non trouvé');
            $this->redirect('/suppliers');
            // exit;
        }
        
        // Récupérer les produits du fournisseur
        $products = Supplier::getProducts($id);
        return $this->view('suppliers/show', [
                            'title' => 'Détails du fournisseur',
                            'supplier' => $supplier,
                            'products' => $products
                        ],
                        'admin');
    }
    
    /**
     * Supprime un fournisseur
     */
    public function delete($id) {
        $supplier = Supplier::getById($id);
        if (!$supplier) {
            Session::set('error', 'Fournisseur non trouvé');
            header('Location: /suppliers');
            exit;
        }
        
        // Vérifier si le fournisseur a des produits
        $products = Supplier::getProducts($id);
        if (count($products) > 0) {
            Session::set('error', 'Impossible de supprimer ce fournisseur car il a des produits associés');
            header('Location: /suppliers');
            exit;
        }
        
        // Supprimer le fournisseur
        $result = Supplier::delete($id);
        
        if ($result) {
            Session::set('success', 'Fournisseur supprimé avec succès');
        } else {
            Session::set('error', 'Erreur lors de la suppression du fournisseur');
        }
        
        header('Location: /suppliers');
        exit;
    }
    
    /**
     * Change le statut d'un fournisseur
     */
    public function changeStatus($id) {
        $supplier = Supplier::getById($id);
        
        if (!$supplier) {
            Session::set('error', 'Fournisseur non trouvé');
            header('Location: /suppliers');
            exit;
        }
        
        $newStatus = $supplier['statut'] === 'actif' ? 'inactif' : 'actif';
        $result = Supplier::changeStatus($id, $newStatus);
        
        if ($result) {
            Session::set('success', 'Statut du fournisseur modifié avec succès');
        } else {
            Session::set('error', 'Erreur lors de la modification du statut');
        }
        
        header('Location: /suppliers');
        exit;
    }
}
