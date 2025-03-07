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
