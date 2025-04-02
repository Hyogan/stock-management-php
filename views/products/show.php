
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Détails du produit</h1>
    <a href="<?= APP_URL ?>/products" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour à la liste
    </a>
</div>

<!-- Détails du produit -->
<div class="row">
    <!-- Informations générales -->
    <div class="row">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Informations générales</h6>
                <?php if (App\Utils\Auth::isAdmin() || App\Utils\Auth::isStorekeeper()): ?>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                        <a class="dropdown-item" href="<?= APP_URL ?>/products/edit/<?= $product['id'] ?>">
                            <i class="fas fa-edit fa-sm fa-fw mr-2 text-gray-400"></i>
                            Modifier
                        </a>
                        <?php if (App\Utils\Auth::isAdmin()): ?>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#deleteModal">
                            <i class="fas fa-trash fa-sm fa-fw mr-2 text-gray-400"></i>
                            Supprimer
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['designation']) ?>" class="img-fluid mb-4 rounded">
                        <?php else: ?>
                            <div class="text-center p-4 mb-4 bg-light rounded">
                                <i class="fas fa-image fa-3x text-gray-400"></i>
                                <p class="mt-2 text-gray-500">Aucune image disponible</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h4 class="font-weight-bold"><?= htmlspecialchars($product['designation']) ?></h4>
                        <p class="text-muted">Référence: <?= htmlspecialchars($product['reference']) ?></p>
                        
                        <?php if (!empty($product['description'])): ?>
                            <h5 class="mt-3">Description</h5>
                            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                        <?php endif; ?>
                        
                        <div class="row mt-3">
                            <div class="col-6">
                                <h5>Prix d'achat</h5>
                                <p class="h4"><?= number_format($product['prix_achat'], 2, ',', ' ') ?> fcfa</p>
                            </div>
                            <div class="col-6">
                                <h5>Prix de vente</h5>
                                <p class="h4 text-primary"><?= number_format($product['prix_vente'], 2, ',', ' ') ?> fcfa</p>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <h5>Catégorie</h5>
                            <?php if ($category): ?>
                                <p><span class="badge bg-info"><?= htmlspecialchars($category['nom']) ?></span></p>
                            <?php else: ?>
                                <p class="text-muted">Aucune catégorie</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-3">
                            <h5>Fournisseur</h5>
                            <?php if ($supplier): ?>
                                <p><span class="badge bg-secondary"><?= htmlspecialchars($supplier['nom']) ?></span></p>
                            <?php else: ?>
                                <p class="text-muted">Aucun fournisseur</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Informations de stock -->
    <div class="row">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Informations de stock</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h5>Quantité en stock</h5>
                    <?php if ($product['quantite_stock'] > $product['quantite_alerte']): ?>
                        <p class="h2 text-success"><?= $product['quantite_stock'] ?></p>
                    <?php elseif ($product['quantite_stock'] > 0): ?>
                        <p class="h2 text-warning"><?= $product['quantite_stock'] ?></p>
                        <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Stock faible</p>
                    <?php else: ?>
                        <p class="h2 text-danger">0</p>
                        <p class="text-danger"><i class="fas fa-times-circle"></i> Rupture de stock</p>
                    <?php endif; ?>
                </div>
                
                <div class="mb-4">
                    <p>Seuil d'alerte : <h5><?= $product['quantite_alerte'] ?></h5></p>
                </div>
                
                <div class="mb-4">
                    <h5>Statut</h5>
                    <?php if ($product['statut'] == 'actif'): ?>
                        <p><span class="badge bg-success">Actif</span></p>
                    <?php else: ?>
                        <p><span class="badge bg-danger">Inactif</span></p>
                    <?php endif; ?>
                </div>
                
                <div class="mb-4">
                    <h5>Date de création</h5>
                    <p><?= date('d/m/Y H:i', strtotime($product['date_creation'])) ?></p>
                </div>
                
                <?php if (!empty($product['date_modification'])): ?>
                <div class="mb-4">
                    <h5>Dernière modification</h5>
                    <p><?= date('d/m/Y H:i', strtotime($product['date_modification'])) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (App\Utils\Auth::isAdmin() || App\Utils\Auth::isStorekeeper()): ?>
                <div class="mt-4">
                    <a href="<?= APP_URL ?>/products/addStockForm?id=<?= $product['id'] ?>" class="btn btn-success btn-block">
                        <i class="fas fa-plus-circle"></i> Ajouter du stock
                    </a>
                    <?php if ($product['quantite_stock'] > 0): ?>
                    <a href="<?= APP_URL ?>/products/removeStockForm?id=<?= $product['id'] ?>" class="btn btn-warning btn-block mt-2">
                        <i class="fas fa-minus-circle"></i> Retirer du stock
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Historique des mouvements de stock -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Historique des mouvements de stock</h6>
    </div>
    <div class="card-body">
        <?php if (!empty($stockMovements)): ?>
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Quantité</th>
                            <th>Motif</th>
                            <th>Utilisateur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stockMovements as $movement): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($movement['date_operation'])) ?></td>
                                <td>
                                    <?php if ($movement['type_operation'] == 'entry'): ?>
                                        <span class="badge bg-success">Entrée</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Sortie</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $movement['quantite'] ?></td>
                                <td><?= htmlspecialchars($movement['motif']) ?></td>
                                <td><?= htmlspecialchars($movement['nom_utilisateur'] ?? 'Système') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Aucun mouvement de stock enregistré pour ce produit.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Annuler</button>
                <form action="<?= APP_URL ?>/products/delete" method="POST">
                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>
