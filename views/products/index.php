<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Liste des Produits</h6>
            <div class="dropdown no-arrow">
                <?php if (isset($actionButtons)) echo $actionButtons; ?>
            </div>
        </div>
        <div class="card-body">
            <!-- Filtres -->
            <div class="mb-4">
                <form action="<?= APP_URL ?>/products/filter" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <select name="category" class="form-control">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
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

            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Nom</th>
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
                                <tr>
                                    <td><?= $product['id'] ?></td>
                                    <td>
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="<?= APP_URL ?>/uploads/products/<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['nom']) ?>" class="img-thumbnail" style="max-width: 50px;">
                                        <?php else: ?>
                                            <img src="<?= APP_URL ?>/assets/img/no-image.png" alt="No Image" class="img-thumbnail" style="max-width: 50px;">
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($product['nom']) ?></td>
                                    <td>
                                        <?php 
                                            $categoryName = "Non catégorisé";
                                            foreach ($categories as $category) {
                                                if ($category['id'] == $product['categorie_id']) {
                                                    $categoryName = htmlspecialchars($category['nom']);
                                                    break;
                                                }
                                            }
                                            echo $categoryName;
                                        ?>
                                    </td>
                                    <td><?= formatPrice($product['prix']) ?></td>
                                    <td>
                                        <span class="badge <?= $product['stock'] > 0 ? 'badge-success' : 'badge-danger' ?>">
                                            <?= $product['stock'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= APP_URL ?>/products/show/<?= $product['id'] ?>" class="btn btn-info btn-sm">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if ($authController->isAdmin() || $authController->isStorekeeper()): ?>
                                            <a href="<?= APP_URL ?>/products/edit/<?= $product['id'] ?>" class="btn btn-primary btn-sm">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?= APP_URL ?>/operations/create?product_id=<?= $product['id'] ?>" class="btn btn-warning btn-sm">
                                                <i class="bi bi-box-arrow-in-down"></i>
                                            </a>
                                            <?php if ($authController->isAdmin()): ?>
                                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal<?= $product['id'] ?>">
                                                    <i class="bi bi-trash"></i>
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
                                                                Êtes-vous sûr de vouloir supprimer le produit "<?= htmlspecialchars($product['nom']) ?>" ?
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
        </div>
    </div>
</div>



<!-- <?php
// $pageTitle = 'Gestion des produits';
// ob_start();
// ?>

<div class="container-fluid">
    < Page Heading -->
    <!-- <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestion des produits</h1>
        <a href="/products/create" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Ajouter un produit
        </a>
    </div> -->

    <!-- Filtres et recherche -->
    <!-- <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtres et recherche</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="/products" class="row">
                <div class="col-md-4 mb-3">
                    <label for="search">Recherche</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Référence, désignation..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="category">Catégorie</label>
                    <select class="form-control" id="category" name="category">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($category == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="sort">Trier par</label>
                    <select class="form-control" id="sort" name="sort">
                        <option value="designation" <?= ($sort == 'designation') ? 'selected' : '' ?>>Désignation</option>
                        <option value="reference" <?= ($sort == 'reference') ? 'selected' : '' ?>>Référence</option>
                        <option value="prix_ht" <?= ($sort == 'prix_ht') ? 'selected' : '' ?>>Prix</option>
                        <option value="stock_actuel" <?= ($sort == 'stock_actuel') ? 'selected' : '' ?>>Stock</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="order">Ordre</label>
                    <select class="form-control" id="order" name="order">
                        <option value="asc" <?= ($order == 'asc') ? 'selected' : '' ?>>Croissant</option>
                        <option value="desc" <?= ($order == 'desc') ? 'selected' : '' ?>>Décroissant</option>
                    </select>
                </div>
                <div class="col-md-1 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block">Filtrer</button>
                </div>
            </form>
        </div>
    </div> -->

    <!-- Liste des produits -->
    <!-- <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Liste des produits</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                    <div class="dropdown-header">Actions:</div>
                    <a class="dropdown-item" href="/products/export">Exporter</a>
                    <a class="dropdown-item" href="/products/import">Importer</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="/products/stock-alert">Alertes de stock</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($products)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Référence</th>
                                <th>Désignation</th>
                                <th>Catégorie</th>
                                <th>Prix HT</th>
                                <th>Stock</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product['reference']) ?></td>
                                <td><?= htmlspecialchars($product['designation']) ?></td>
                                <td><?= htmlspecialchars($product['categorie_nom'] ?? 'Non catégorisé') ?></td>
                                <td><?= number_format($product['prix_ht'], 2, ',', ' ') ?> €</td>
                                <td class="<?= ($product['stock_actuel'] <= $product['stock_minimum']) ? 'text-danger font-weight-bold' : '' ?>">
                                    <?= $product['stock_actuel'] ?>
                                    <?php if ($product['stock_actuel'] <= $product['stock_minimum']): ?>
                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($product['actif']): ?>
                                        <span class="badge badge-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/products/view/<?= $product['id'] ?>" class="btn btn-info btn-sm" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/products/edit/<?= $product['id'] ?>" class="btn btn-primary btn-sm" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/operations/create?product_id=<?= $product['id'] ?>" class="btn btn-success btn-sm" title="Opération de stock">
                                        <i class="fas fa-exchange-alt"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm" title="Supprimer" 
                                            onclick="confirmDelete(<?= $product['id'] ?>, '<?= addslashes($product['designation']) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    Aucun produit trouvé. <a href="/products/create" class="alert-link">Ajouter un produit</a>.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div> -->

<!-- Modal de confirmation de suppression -->
<!-- <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir supprimer le produit <span id="productName" class="font-weight-bold"></span> ?
                Cette action est irréversible.
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST" action="">
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div> -->

<!-- <script>
function confirmDelete(id, name) {
    document.getElementById('productName').textContent = name;
    document.getElementById('deleteForm').action = '/products/delete/' + id;
    $('#deleteModal').modal('show');
}

// Initialisation de DataTables
$(document).ready(function() {
    $('#dataTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "pageLength": 25
    });
});
</script>

<?php
// $content = ob_get_clean();
// include BASE_PATH . '/views/layouts/main.php';
?>
