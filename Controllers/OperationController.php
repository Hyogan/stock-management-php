<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Entry;
use App\Models\ExitOp;
use App\Models\Product;
use App\Utils\Auth;

class OperationController extends Controller {
    private $entryModel;
    private $exitModel;

    public function __construct() {
        $this->entryModel = new Entry();
        $this->exitModel = new ExitOp();
    }

    /**
     * Afficher la liste des opérations
     */
    public function index() {
        // Vérifier les droits d'accès
        Auth::checkAccess('any');
        // Récupérer toutes les opérations
        $entries = $this->entryModel->getAll();
        $exits = $this->exitModel->getAll();
        // Définir le titre de la page
        $pageTitle = 'Gestion des Opérations';
        // Afficher la vue
        $this->view('operations/index', [
            'entries' => $entries,
            'exits' => $exits,
            'pageTitle' => $pageTitle
        ], 'admin');
    }

    /**
     * Afficher le formulaire d'ajout d'une entrée de stock
     */
    public function createEntry() {
        // Vérifier les droits d'accès
        Auth::checkAccess('any');
        // Définir le titre de la page
        $pageTitle = 'Ajouter une Entrée de Stock';
        // Afficher la vue
        $this->view('stock/create_entry', [
            'pageTitle' => $pageTitle
        ], 'admin');
    }



    /**
     * Traiter l'ajout d'une entrée de stock
     */
    public function storeEntry() {
        // Vérifier les droits d'accès
        Auth::checkAccess('any');
        // Vérifier si le formulaire a été soumis
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/entries');
            exit;
        }
        // Récupérer les données du formulaire
        $data = [
            'reference' => $_POST['reference'] ?? '',
            'id_fournisseur' => $_POST['id_fournisseur'] ?? '',
            'date_entree' => $_POST['date_entree'] ?? '',
            'montant_total' => $_POST['montant_total'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'id_utilisateur' => $_SESSION['user_id']
        ];
        // Ajouter l'entrée de stock
        $this->entryModel->create($data);
        // Rediriger vers la liste des opérations
        $_SESSION['success'] = 'L\'entrée de stock a été ajoutée avec succès';
        $this->redirect('/entries');
    }

    /**
     * Afficher le formulaire de modification d'une entrée de stock
     */
    public function editEntry($entryId) {
        // Vérifier les droits d'accès
        Auth::checkAccess('any');
        // Récupérer l'entrée de stock
        $entry = $this->entryModel->getById($entryId);
        // Définir le titre de la page
        $pageTitle = 'Modifier une Entrée de Stock';
        // Afficher la vue
        $this->view('stock/edit_entry', [
            'entry' => $entry,
            'pageTitle' => $pageTitle
        ], 'admin');
    }

    /**
     * Traiter la modification d'une entrée de stock
     */
    public function updateEntry($entryId) {
        // Vérifier les droits d'accès
        Auth::checkAccess('any');
        // Vérifier si le formulaire a été soumis
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/entries');
            exit;
        }
        // Récupérer les données du formulaire
        $data = [
            'reference' => $_POST['reference'] ?? '',
            'id_fournisseur' => $_POST['id_fournisseur'] ?? '',
            'date_entree' => $_POST['date_entree'] ?? '',
            'montant_total' => $_POST['montant_total'] ?? '',
            'notes' => $_POST['notes'] ?? ''
        ];
        // Mettre à jour l'entrée de stock
        $this->entryModel->update($entryId, $data);
        // Rediriger vers la liste des opérations
        $_SESSION['success'] = 'L\'entrée de stock a été mise à jour avec succès';
        $this->redirect('/entries');
    }

    /**
     * Afficher les détails d'une entrée de stock
     */
    public function showEntry($entryId) {
        // Vérifier les droits d'accès
        Auth::checkAccess('any');
        // Récupérer l'entrée de stock
        $entry = $this->entryModel->getById($entryId);
        // Définir le titre de la page
        $pageTitle = 'Détails de l\'Entrée de Stock';
        // Afficher la vue
        $this->view('stock/entry', [
            'entry' => $entry,
            'pageTitle' => $pageTitle
        ], 'admin');
    }

    /**
     * Supprimer une entrée de stock
     */
    public function deleteEntry($entryId) {
        // Vérifier les droits d'accès
        Auth::checkAccess('any');
        // Supprimer l'entrée de stock
        $this->entryModel->delete($entryId);
        // Rediriger vers la liste des opérations
        $_SESSION['success'] = 'L\'entrée de stock a été supprimée avec succès';
        $this->redirect('/entries');
    }

    /**
     * Afficher le formulaire d'ajout d'une sortie de stock
     */
    public function createExit() {
        // Vérifier les droits d'accès
        Auth::checkAccess('any');
        // Définir le titre de la page
        $pageTitle = 'Ajouter une Sortie de Stock';
        // Afficher la vue
        $this->view('stock/create_exit', [
            'pageTitle' => $pageTitle
        ], 'admin');
    }

    /**
     * Traiter l'ajout d'une sortie de stock
     */
    public function storeExit() {
        // Vérifier les droits d'accès
        Auth::checkAccess('any');
        // Vérifier si le formulaire a été soumis
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/exits');
            exit;
        }
        // Récupérer les données du formulaire
        $data = [
            'reference' => $_POST['reference'] ?? '',
            'type_sortie' => $_POST['type_sortie'] ?? '',
            'date_sortie' => $_POST['date_sortie'] ?? '',
            'montant_total' => $_POST['montant_total'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'id_utilisateur' => $_SESSION['user_id']
        ];
        // Ajouter la sortie de stock
        $this->exitModel->create($data);
        // Rediriger vers la liste des opérations
        $_SESSION['success'] = 'La sortie de stock a été ajoutée avec succès';
        $this->redirect('/exits');
    }

    /**
     * Afficher le formulaire de modification d'une sortie de stock
     */
    public function editExit($exitId) {
        // Vérifier les droits d'accès
        Auth::checkAccess('any');
        // Récupérer la sortie de stock
        $exit = $this->exitModel->getById($exitId);
        // Définir le titre de la page
        $pageTitle = 'Modifier une Sortie de Stock';
        // Afficher la vue
        $this->view('stock/edit_exit', [
            'exit' => $exit,
            'pageTitle' => $pageTitle
        ], 'admin');
    }

    /**
     * Traiter la modification d'une sortie de stock
     */
    public function updateExit($exitId) {
        // Vérifier les droits d'accès
        Auth::checkAccess('any');
        // Vérifier si le formulaire a été soumis
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/exits');
            exit;
        }
        // Récupérer les données du formulaire
        $data = [
            'reference' => $_POST['reference'] ?? '',
            'type_sortie' => $_POST['type_sortie'] ?? '',
            'date_sortie' => $_POST['date_sortie'] ?? '',
            'montant_total' => $_POST['montant_total'] ?? '',
            'notes' => $_POST['notes'] ?? ''
        ];
        // Mettre à jour la sortie de stock
        $this->exitModel->update($exitId, $data);
        // Rediriger vers la liste des opérations
        $_SESSION['success'] = 'La sortie de stock a été mise à jour avec succès';
        $this->redirect('/exits');
    }

    /**
     * Afficher les détails d'une sortie de stock
     */
    public function showExit($exitId) {
        // Vérifier les droits d'accès
        Auth::checkAccess('any');
        // Récupérer la sortie de stock
        $exit = $this->exitModel->getById($exitId);
        // Définir le titre de la page
        $pageTitle = 'Détails de la Sortie de Stock';
        // Afficher la vue
        $this->view('stock/exit', [
            'exit' => $exit,
            'pageTitle' => $pageTitle
        ], 'admin');
    }

    /**
     * Supprimer une sortie de stock
     */
    public function deleteExit($exitId) {
        // Vérifier les droits d'accès
        Auth::checkAccess('any');
        // Supprimer la sortie de stock
        $this->exitModel->delete($exitId);
        // Rediriger vers la liste des opérations
        $_SESSION['success'] = 'La sortie de stock a été supprimée avec succès';
        $this->redirect('/exits');
    }


    public function createExitFromDelivery($delivery, $order, $orderItems) 
    {
      return ExitOp::createExitFromDelivery($delivery, $order, $orderItems);
    }
}
