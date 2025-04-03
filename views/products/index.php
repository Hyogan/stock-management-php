
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Liste des Produits</h6>
            <div class="dropdown no-arrow">
                <?php if (isset($actionButtons)) echo $actionButtons; ?>
                <a href="/products/create" class="btn btn-success">+ Ajouter un produit</a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filtres -->
            <div class="mb-4">
                <form action="<?= APP_URL ?>/products/filter" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <select name="category" class="form-control">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="sort" class="form-control">
                            <option value="designation" <?= (isset($sort) && $sort == 'designation') ? 'selected' : '' ?>>Nom</option>
                            <option value="prix" <?= (isset($sort) && $sort == 'prix') ? 'selected' : '' ?>>Prix</option>
                            <option value="stock" <?= (isset($sort) && $sort == 'stock') ? 'selected' : '' ?>>Stock</option>
                            <option value="date_creation" <?= (isset($sort) && $sort == 'date_creation') ? 'selected' : '' ?>>Date d'ajout</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="order" class="form-control">
                            <option value="asc" <?= (isset($order) && $order == 'asc') ? 'selected' : '' ?>>Croissant</option>
                            <option value="desc" <?= (isset($order) && $order == 'desc') ? 'selected' : '' ?>>Décroissant</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    </div>
                </form>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Statistiques rapides si disponibles -->
            <?php if (isset($totalProducts) && isset($totalValue)): ?>
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Produits</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalProducts ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-boxes fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Valeur du Stock</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= formatPrice($totalValue) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Rupture de Stock</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($outOfStockCount) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Stock Faible</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($lowStockCount) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-arrow-down fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Désignation</th>
                            <th>Catégorie</th>
                            <th>Prix</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Aucun produit trouvé</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr class="<?= (isset($product['quantite_stock']) && $product['quantite_stock'] <= 0) ? 'table-danger' : '' ?>">
                                    <td><?= $product['id'] ?></td>
                                    <td>
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="<?= APP_URL ?>/uploads/products/<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['designation']) ?>" class="img-thumbnail" style="max-width: 50px;">
                                        <?php else: ?>
                                            <img src="<?= APP_URL ?>/assets/img/no-image.png" alt="No Image" class="img-thumbnail" style="max-width: 50px;">
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($product['designation'] ?? $product['nom']) ?></td>
                                    <td>
                                        <?php 
                                            $categoryName = "Non catégorisé";
                                            foreach ($categories as $category) {
                                                if ($category['id'] == ($product['id_categorie'] ?? $product['categorie_id'])) {
                                                    $categoryName = htmlspecialchars($category['nom']);
                                                    break;
                                                }
                                            }
                                            echo $categoryName;
                                        ?>
                                    </td>
                                    <td><?= formatPrice($product['prix_vente'] ?? $product['prix']) ?></td>
                                    <td style="color: black;">
                                        <span class="badge <?= ($product['quantite_stock'] ?? $product['stock']) > 0 ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $product['quantite_stock'] ?? $product['stock'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= APP_URL ?>/products/show/<?= $product['id'] ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> voir
                                        </a>
                                        <?php if ($authController->isAdmin() || $authController->isStorekeeper()): ?>
                                            <a href="<?= APP_URL ?>/products/edit/<?= $product['id'] ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-pencil"></i>Edit
                                            </a>
                                            <!-- <a href="<?= APP_URL ?>/operations/create?product_id=<?= $product['id'] ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-box-arrow-in-down"></i> creer une e
                                            </a> -->
                                            <?php if ($authController->isAdmin()): ?>
                                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal<?= $product['id'] ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <!-- Modal de confirmation de suppression -->
                                                <div class="modal fade" id="deleteModal<?= $product['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?= $product['id'] ?>" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel<?= $product['id'] ?>">Confirmer la suppression</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Êtes-vous sûr de vouloir supprimer le produit "<?= htmlspecialchars($product['designation'] ?? $product['nom']) ?>" ?
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                                <a href="<?= APP_URL ?>/products/delete/<?= $product['id'] ?>" class="btn btn-danger">Supprimer</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Boutons d'export -->
            <?php if (!empty($products) && ($authController->isAdmin() || $authController->isStorekeeper())): ?>
            <div class="mt-4 text-right">
                <a href="<?= APP_URL ?>/products/exportCsv" class="btn btn-outline-primary">
                    <i class="fas fa-file-csv mr-2"></i>Exporter en CSV
                </a>
                <?php if ($authController->isAdmin()): ?>
                <a href="<?= APP_URL ?>/products/generateStockReport" class="btn btn-outline-success ml-2">
                    <i class="fas fa-file-pdf mr-2"></i>Rapport PDF
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Voici les principales modifications apportées au fichier `products/index.php` :

1. **Ajout des options de tri** : J'ai ajouté deux listes déroulantes permettant de choisir :
   - Le champ de tri (désignation, prix, stock ou date de création)
   - L'ordre de tri (croissant ou décroissant)

2. **Adaptation du formulaire de filtrage** pour qu'il soit compatible avec la méthode `index()` du contrôleur `ProductController`. Le formulaire utilise maintenant les paramètres de recherche, de catégorie, de tri et d'ordre.

3. **Ajout d'une section de statistiques rapides** qui s'affiche lorsque ces données sont disponibles (cas où la méthode `statistics()` est appelée). Cette section montre :
   - Le nombre total de produits
   - La valeur totale du stock
   - Le nombre de produits en rupture de stock
   - Le nombre de produits à faible stock

4. **Gestion de noms de champs différents** : Certains champs peuvent avoir des noms différents selon la méthode du contrôleur qui est appelée (`designation`/`nom`, `quantite_stock`/`stock`, etc.). J'ai ajouté une logique de fallback pour gérer ces différences.

5. **Ajout de boutons d'export** au bas de la page pour exporter les données au format CSV ou générer un rapport PDF, comme prévu dans le contrôleur.

6. **Mise en évidence des produits en rupture de stock** avec une classe CSS pour les rendre plus visibles dans le tableau.

Ces modifications permettent une meilleure cohérence entre la vue et le contrôleur, tout en améliorant l'expérience utilisateur avec des fonctionnalités plus avancées de filtrage, de tri et d'export. -->
