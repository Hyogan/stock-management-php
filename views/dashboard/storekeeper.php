<?php
$pageTitle = 'Tableau de bord Magasinier';
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tableau de bord Magasinier</h1>
        <div>
            <a href="/products/create" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-2">
                <i class="fas fa-plus fa-sm text-white-50"></i> Nouveau produit
            </a>
            <a href="/operations/create" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
                <i class="fas fa-exchange-alt fa-sm text-white-50"></i> Nouvelle opération
            </a>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Total Produits Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Produits</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($products) ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Produits en rupture Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Produits en rupture</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($outOfStock)?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bons de sortie Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Bons de sortie</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['exit_notes'] ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sign-out-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Entrées en stock Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Entrées en stock</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($entries)?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sign-in-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Produits en alerte de stock -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Produits en alerte de stock</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($lowStockProducts)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Référence</th>
                                        <th>Désignation</th>
                                        <th>Stock actuel</th>
                                        <th>Stock minimum</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lowStockProducts as $product): ?>
                                    <tr>
                                        <td><?= $product['reference'] ?></td>
                                        <td><?= htmlspecialchars($product['designation']) ?></td>
                                        <td>
                                            <span class="font-weight-bold text-danger"><?= $product['quantite_stock'] ?></span>
                                        </td>
                                        <td><?= $product['quantite_alerte'] ?></td>
                                        <td>
                                            <a href="/products/view/<?= $product['id'] ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/operations/create?product_id=<?= $product['id'] ?>&type=entree" class="btn btn-success btn-sm">
                                                <i class="fas fa-plus"></i> Stock
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center">Aucun produit en alerte de stock.</p>
                    <?php endif; ?>
                    <div class="mt-3 text-center">
                        <a href="/products" class="btn btn-primary">Voir tous les produits</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dernières opérations -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dernières opérations</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentOperations)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Produit</th>
                                        <th>Quantité</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOperations as $operation): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($operation['created_at'])) ?></td>
                                        <td>
                                            <?php if ($operation['type'] == 'entree'): ?>
                                                <span class="badge bg-success">Entrée</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Sortie</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($operation['product_name']) ?></td>
                                        <td><?= $operation['quantite'] ?></td>
                                        <td>
                                            <a href="/operations/view/<?= $operation['id'] ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center">Aucune opération récente.</p>
                    <?php endif; ?>
                    <div class="mt-3 text-center">
                        <a href="/operations" class="btn btn-primary">Voir toutes les opérations</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Statistiques par catégorie -->
        <div class="col-lg-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Répartition des produits par catégorie</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if (!empty($categoryStats)): ?>
                            <?php foreach ($categoryStats as $category): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card border-left-primary shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    <?= htmlspecialchars($category['nom']) ?></div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $category['total'] ?> produits</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-folder fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <p class="text-center">Aucune donnée disponible.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
