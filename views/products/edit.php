
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Modifier un produit</h1>
        <a href="<?= APP_URL ?>/products" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour à la liste
        </a>
    </div>

    <!-- Formulaire de modification de produit -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informations du produit</h6>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['flash'])): ?>
                <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show" role="alert">
                    <?= $_SESSION['flash']['message'] ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <form method="POST" action="<?= APP_URL ?>/products/update/<?= $product['id'] ?>" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                
                <div class="row">
                    <!-- Informations générales -->
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="reference">Référence <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="reference" name="reference" required 
                                   value="<?= htmlspecialchars($product['reference']) ?>">
                            <small class="form-text text-muted">Référence unique du produit</small>
                            <?php if (isset($errors['reference'])): ?>
                                <div class="text-danger"><?= $errors['reference'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="designation">Désignation <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="designation" name="designation" required
                                   value="<?= htmlspecialchars($product['designation']) ?>">
                            <?php if (isset($errors['designation'])): ?>
                                <div class="text-danger"><?= $errors['designation'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="id_categorie">Catégorie</label>
                            <select class="form-control" id="id_categorie" name="id_categorie">
                                <option value="">Sélectionner une catégorie</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= ($product['id_categorie'] == $category['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="id_fournisseur">Fournisseur</label>
                            <select class="form-control" id="id_fournisseur" name="id_fournisseur">
                                <option value="">Sélectionner un fournisseur</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= $supplier['id'] ?>" <?= ($product['id_fournisseur'] == $supplier['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($supplier['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="unite">Unité</label>
                            <input type="text" class="form-control" id="unite" name="unite" 
                                   value="<?= htmlspecialchars($product['unite']) ?>">
                            <small class="form-text text-muted">Ex: pièce, kg, litre, etc.</small>
                        </div>
                    </div>
                    
                    <!-- Prix et stock -->
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="prix_achat">Prix d'achat</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="prix_achat" name="prix_achat" step="0.01" min="0"
                                       value="<?= htmlspecialchars($product['prix_achat']) ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">fcfa</span>
                                </div>
                            </div>
                            <?php if (isset($errors['prix_achat'])): ?>
                                <div class="text-danger"><?= $errors['prix_achat'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="prix_vente">Prix de vente <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="prix_vente" name="prix_vente" step="0.01" min="0" required
                                       value="<?= htmlspecialchars($product['prix_vente']) ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">fcfa</span>
                                </div>
                            </div>
                            <?php if (isset($errors['prix_vente'])): ?>
                                <div class="text-danger"><?= $errors['prix_vente'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="quantite_stock">Stock actuel</label>
                            <input type="number" class="form-control" id="quantite_stock" readonly
                                   value="<?= htmlspecialchars($product['quantite_stock']) ?>">
                            <small class="form-text text-muted">Pour modifier le stock, utilisez les opérations de stock</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="quantite_alerte">Stock minimum (alerte)</label>
                            <input type="number" class="form-control" id="quantite_alerte" name="quantite_alerte" min="0" step="1"
                                   value="<?= htmlspecialchars($product['quantite_alerte']) ?>">
                            <small class="form-text text-muted">Une alerte sera affichée lorsque le stock sera inférieur à cette valeur</small>
                            <?php if (isset($errors['quantite_alerte'])): ?>
                                <div class="text-danger"><?= $errors['quantite_alerte'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Image du produit</label>
                            <?php if (!empty($product['image'])): ?>
                                <div class="mb-2">
                                    <img src="<?= APP_URL . $product['image'] ?>" alt="<?= htmlspecialchars($product['designation']) ?>" class="img-thumbnail" style="max-height: 100px;">
                                    <div class="custom-control custom-checkbox mt-1">
                                        <input type="checkbox" class="custom-control-input" id="supprimer_image" name="supprimer_image" value="1">
                                        <label class="custom-control-label" for="supprimer_image">Supprimer l'image</label>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="image" name="image" accept="image/*">
                                <label class="custom-file-label" for="image">Choisir un fichier</label>
                            </div>
                            <small class="form-text text-muted">Format recommandé: JPG, PNG. Taille max: 2MB</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="statut" name="statut" value="actif" 
                                       <?= ($product['statut'] == 'actif') ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="statut">Produit actif</label>
                            </div>
                            <small class="form-text text-muted">Les produits inactifs n'apparaissent pas dans le catalogue client</small>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        <a href="<?= APP_URL ?>/products" class="btn btn-secondary">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Historique des opérations de stock -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Historique des opérations de stock</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($stockOperations)): ?>
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
                            <?php foreach ($stockOperations as $operation): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($operation['date_operation'])) ?></td>
                                    <td>
                                        <?php if ($operation['type_operation'] == 'entry'): ?>
                                            <span class="badge bg-success">Entrée</span>
                                        <?php elseif ($operation['type_operation'] == 'exit'): ?>
                                            <span class="badge bg-danger">Sortie</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($operation['type_operation']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $operation['quantite'] ?></td>
                                    <td><?= htmlspecialchars($operation['motif']) ?></td>
                                    <td><?= htmlspecialchars($operation['nom_utilisateur'] . ' ' . $operation['prenom_utilisateur']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    Aucune opération de stock n'a été enregistrée pour ce produit.
                </div>
            <?php endif; ?>
            
            <a href="<?= APP_URL ?>/products/addStockForm/<?= $product['id'] ?>" class="btn btn-sm btn-success">
                <i class="fas fa-plus fa-sm"></i> Ajouter du stock
            </a>
            <a href="<?= APP_URL ?>/products/removeStockForm/<?= $product['id'] ?>" class="btn btn-sm btn-danger">
                <i class="fas fa-minus fa-sm"></i> Retirer du stock
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Afficher le nom du fichier sélectionné
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            const fileName = e.target.files[0].name;
            const label = e.target.nextElementSibling;
            label.textContent = fileName;
        }
    });
});
</script>
</qodoArtifact>

