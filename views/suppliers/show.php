<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Informations du fournisseur</h3>
                </div>
                <div class="card-body">
                    <strong><i class="fas fa-user mr-1"></i> Nom</strong>
                    <p class="text-muted"><?= htmlspecialchars($supplier['nom']) ?></p>
                    <hr>
                    
                    <?php if (!empty($supplier['adresse'])): ?>
                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Adresse</strong>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($supplier['adresse'])) ?></p>
                    <hr>
                    <?php endif; ?>
                    
                    <?php if (!empty($supplier['telephone'])): ?>
                    <strong><i class="fas fa-phone mr-1"></i> Téléphone</strong>
                    <p class="text-muted"><?= htmlspecialchars($supplier['telephone']) ?></p>
                    <hr>
                    <?php endif; ?>
                    
                    <?php if (!empty($supplier['email'])): ?>
                    <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                    <p class="text-muted"><?= htmlspecialchars($supplier['email']) ?></p>
                    <hr>
                    <?php endif; ?>
                    
                    <strong><i class="fas fa-circle mr-1"></i> Statut</strong>
                    <p>
                        <span class="badge bg-<?= $supplier['statut'] === 'actif' ? 'success' : 'danger' ?>">
                            <?= ucfirst($supplier['statut']) ?>
                        </span>
                    </p>
                    <hr>
                    
                    <strong><i class="fas fa-calendar mr-1"></i> Date de création</strong>
                    <p class="text-muted"><?= date('d/m/Y H:i', strtotime($supplier['date_creation'])) ?></p>
                    
                    <?php if (!empty($supplier['date_modification'])): ?>
                    <hr>
                    <strong><i class="fas fa-edit mr-1"></i> Dernière modification</strong>
                    <p class="text-muted"><?= date('d/m/Y H:i', strtotime($supplier['date_modification'])) ?></p>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="/suppliers/edit/<?= $supplier['id'] ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                    <a href="/suppliers" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Produits associés</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($products)): ?>
                        <div class="alert alert-info">
                            Aucun produit associé à ce fournisseur.
                        </div>
                    <?php else: ?>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Désignation</th>
                                    <th>Catégorie</th>
                                    <th>Prix</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= $product['id'] ?></td>
                                    <td><?= htmlspecialchars($product['designation']) ?></td>
                                    <td><?= htmlspecialchars($product['categorie_nom'] ?? '-') ?></td>
                                    <td><?= number_format($product['prix_vente'], 2, ',', ' ') ?> fcfa</td>
                                    <td>
                                        <span class="badge bg-<?= $product['quantite_stock'] > 0 ? 'success' : 'danger' ?>">
                                            <?= $product['quantite_stock'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/products/show/<?= $product['id'] ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</qodoArtifact>
