<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;

class CategoryController extends Controller{

    // Vérifie si l'utilisateur est connecté
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            exit;
        }
    }

    // Affiche la liste des catégories avec filtrage et tri
    public function index() {
        $this->checkAuth(); // Vérifier l'authentification

        // Récupérer les paramètres de recherche et de tri
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'name';
        $order = $_GET['order'] ?? 'asc';
        $categories = [];
        // Récupérer les catégories avec filtre et tri
        if (!empty($search)) {
            // $categories = Category::search($search);
            $categories = Category::getAll();
        } else {
            // $categories = Category::getAll($sort, $order);
            $categories = Category::getAll();
        }

        // Définir le titre de la page
        $pageTitle = 'Gestion des catégories';

        // Afficher la vue
        $this->view('category/index', [
            'pageTitle' => $pageTitle,
            'categories' => $categories,
            'search' => $search,
            'sort' => $sort,
            'order' => $order
        ], 'admin');
    }

    // Affiche une seule catégorie
    public function show($id) {
        $this->checkAuth();
        $category = Category::getById($id);

        if (!$category) {
            $_SESSION['error'] = "Catégorie non trouvée.";
            $this->redirect('/categories');
        }

        $this->view('category/show', ['category' => $category],'admin');
    }

    // Affiche le formulaire de création
    public function create() {
        $this->checkAuth();
        $this->view('category/create',[],'admin');
    }

    // Enregistre une nouvelle catégorie
    public function store() {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim(htmlspecialchars($_POST['nom'] ?? ''));

            if (empty($nom)) {
                $_SESSION['error'] = "Le nom de la catégorie est requis.";
                $this->redirect('/categories/create');
            }
            $data = [
              'nom' => $_POST['nom'] ?? '',
              'description' => $_POST['description'] ?? '',
              'statut' => $_POST['statut'] ?? 'actif',
              'date_creation' => date_create('now')
            ];
            // dd($data);
            Category::create($data);

            $_SESSION['success'] = "Catégorie ajoutée avec succès.";
            $this->redirect('/categories');
        }
    }

    // Affiche le formulaire d'édition
    public function edit($id) {
        $this->checkAuth();
        $category = Category::getById($id);

        if (!$category) { 
            $_SESSION['error'] = "Catégorie non trouvée.";
            $this->redirect('/categories');
        }

        $this->view('category/edit', ['category' => $category],'admin');
    }

    // Met à jour une catégorie
    public function update($id) {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim(htmlspecialchars($_POST['name'] ?? ''));
            $description = trim(htmlspecialchars($_POST['description'] ?? ''));
            $statut = trim(htmlspecialchars($_POST['statut'] ?? ''));

            if (empty($name)) {
                $_SESSION['error'] = "Le nom de la catégorie est requis.";
                $this->redirect("/categories/edit/$id/");
            }
            if (empty($description)) {
              $_SESSION['error'] = "La description de la catégorie est requis.";
              $this->redirect("/categories/edit/$id/");
          }

            $category = Category::getById($id);
            if (!$category) {
                $_SESSION['error'] = "Catégorie non trouvée.";
                $this->redirect('/categories');
            }
            $category['nom'] = $name;
            $category['description'] = $description;
            $category['statut'] = !empty($statut) ? $statut : 'actif'  ;

            Category::update($id,$category);
            // $category->update();

            $_SESSION['success'] = "Catégorie mise à jour avec succès.";
            $this->redirect('/categories');
        }
    }

    // Supprime une catégorie
    public function delete($id) {
        $this->checkAuth();
        $category = Category::getById($id);

        if (!$category) {
            $_SESSION['error'] = "Catégorie non trouvée.";
            $this->redirect('/categories');
        }

        Category::delete($category['id']);
        $_SESSION['success'] = "Catégorie supprimée avec succès.";
        $this->redirect('/categories');
    }

}
