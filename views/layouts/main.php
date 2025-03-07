<?php
// Définir le titre de la page
$pageTitle = 'Gestion des Livraisons';

// Inclure le layout principal
require_once BASE_PATH . '/views/layouts/main.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Liste des Livraisons</h5>
                    <?php if ($this->authController->isAdmin() || $this->authController->isStorekeeper()): ?>
                    <a href="<?= APP_URL ?>/orders/pending" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouvelle Livraison
                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($deliveries)): ?>
                        <div class="alert alert-info">
                            Aucune livraison trouvée.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Commande</th>
                                        <th>Client</th>
                                        <th>Date de livraison</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($deliveries as $delivery): ?>
                                        <tr>
                                            <td><?= $delivery['id_livraison'] ?></td>
                                            <td><?= $delivery['numero_commande'] ?></td>
                                            <td><?= $delivery['nom_client'] . ' ' . $delivery['prenom_client'] ?></td>
                                            <td><?= date('d/m/Y', strtotime($delivery['date_livraison'])) ?></td>
                                            <td>
                                                <?php if ($delivery['statut'] === 'en_cours'): ?>
                                                    <span class="badge bg-warning">En cours</span>
                                                <?php elseif ($delivery['statut'] === 'terminee'): ?>
                                                    <span class="badge bg-success">Terminée</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?= ucfirst($delivery['statut']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?= APP_URL ?>/deliveries/show/<?= $delivery['id_livraison'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($delivery['statut'] === 'en_cours' && ($this->authController->isAdmin() || $this->authController->isStorekeeper())): ?>
                                                    <a href="<?= APP_URL ?>/deliveries/complete/<?= $delivery['id_livraison'] ?>" class="btn btn-sm btn-success">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?= APP_URL ?>/deliveries/generate-note/<?= $delivery['id_livraison'] ?>" class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                          </table>
                        </div>
        </main>
    </div>
</div>
?>
<?php require_once BASE_PATH . '/views/layouts/footer.php'; ?>
