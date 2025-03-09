<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Modifier un Produit</h6>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $error ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <form action="<?= APP_URL ?>/products/update/<?= $product['id'] ?>" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nom">Nom du produit <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nom" name="nom" required value="<?= htmlspecialchars($product['nom']) ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="categorie_id">Catégorie <span class="text-danger">*</span></label>
                            <select class="form-control" id="categorie_id" name="categorie_id" required>
                                <option value="">Sélectionner une catégorie</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= ($product['categorie_id'] == $category['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="prix">Prix <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">DH</span>
                                </div>
                                <input type="number" class="form-control" id="prix" name="prix" step="0.01" min="0" required value="<?= htmlspecialchars($product['prix']) ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="stock">Stock actuel <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="stock" name="stock" min="0" required value="<?= htmlspecialchars($product['stock']) ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">Image du produit</label>
                    <?php if (!empty($product['image'])): ?>
                        <div class="mb-2">
                            <img src="<?= APP_URL ?>/uploads/products/<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['nom']) ?>" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
                    <small class="form-text text-muted">Formats acceptés: JPG, PNG, GIF. Taille maximale: 2 Mo. Laissez vide pour conserver l'image actuelle.</small>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    <a href="<?= APP_URL ?>/products" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>


<?php
$pageTitle = 'Modifier un produit';
ob_start();
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Modifier un produit</h1>
        <a href="/products" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
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

            <form method="POST" action="/products/update/<?= $product['id'] ?>" enctype="multipart/form-data">
                <div class="row">
                    <!-- Informations générales -->
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="reference">Référence <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="reference" name="reference" required 
                                   value="<?= htmlspecialchars($product['reference']) ?>">
                            <small class="form-text text-muted">Référence unique du produit</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="designation">Désignation <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="designation" name="designation" required
                                   value="<?= htmlspecialchars($product['designation']) ?>">
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
                    </div>
                    
                    <!-- Prix et stock -->
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="prix_ht">Prix HT <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="prix_ht" name="prix_ht" step="0.01" min="0" required
                                       value="<?= htmlspecialchars($product['prix_ht']) ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">€</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="tva">TVA (%)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="tva" name="tva" step="0.1" min="0" max="100"
                                       value="<?= htmlspecialchars($product['tva']) ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="prix_ttc">Prix TTC (calculé automatiquement)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="prix_ttc" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text">€</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="stock_actuel">Stock actuel</label>
                            <input type="number" class="form-control" id="stock_actuel" readonly
                                   value="<?= htmlspecialchars($product['stock_actuel']) ?>">
                            <small class="form-text text-muted">Pour modifier le stock, utilisez les opérations de stock</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="stock_minimum">Stock minimum (alerte)</label>
                            <input type="number" class="form-control" id="stock_minimum" name="stock_minimum" min="0" step="1"
                                   value="<?= htmlspecialchars($product['stock_minimum']) ?>">
                            <small class="form-text text-muted">Une alerte sera affichée lorsque le stock sera inférieur à cette valeur</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Image du produit</label>
                            <?php if (!empty($product['image_url'])): ?>
                                <div class="mb-2">
                                    <img src="<?= $product['image_url'] ?>" alt="<?= htmlspecialchars($product['designation']) ?>" class="img-thumbnail" style="max-height: 100px;">
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
                                <input type="checkbox" class="custom-control-input" id="actif" name="actif" value="1" 
                                       <?= ($product['actif'] == 1) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="actif">Produit actif</label>
                            </div>
                            <small class="form-text text-muted">Les produits inactifs n'apparaissent pas dans le catalogue client</small>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        <a href="/products" class="btn btn-secondary">Annuler</a>
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
                                <th>Commentaire</th>
                                <th>Utilisateur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stockOperations as $operation): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($operation['date_operation'])) ?></td>
                                    <td>
                                        <?php if ($operation['type_operation'] == 'entree'): ?>
                                            <span class="badge badge-success">Entrée</span>
                                        <?php elseif ($operation['type_operation'] == 'sortie'): ?>
                                            <span class="badge badge-danger">Sortie</span>
                                        <?php elseif ($operation['type_operation'] == 'ajustement'): ?>
                                            <span class="badge badge-warning">Ajustement</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><?= htmlspecialchars($operation['type_operation']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $operation['quantite'] ?></td>
                                    <td><?= htmlspecialchars($operation['commentaire']) ?></td>
                                    <td><?= htmlspecialchars($operation['nom_utilisateur']) ?></td>
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
            
            <a href="/stock/operation/<?= $product['id'] ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus fa-sm"></i> Nouvelle opération de stock
            </a>
        </div>
    </div>
</div>

<script>
// Calcul automatique du prix TTC
document.addEventListener('DOMContentLoaded', function() {
    const prixHtInput = document.getElementById('prix_ht');
    const tvaInput = document.getElementById('tva');
    const prixTtcInput = document.getElementById('prix_ttc');
    
    function calculerPrixTTC() {
        const prixHt = parseFloat(prixHtInput.value) || 0;
        const tva = parseFloat(tvaInput.value) || 0;
        const prixTtc = prixHt * (1 + (tva / 100));
        prixTtcInput.value = prixTtc.toFixed(2);
    }
    
    prixHtInput.addEventListener('input', calculerPrixTTC);
    tvaInput.addEventListener('input', calculerPrixTTC);
    
    // Calcul initial
    calculerPrixTTC();
    
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

<?php
$content = ob_get_clean();
include BASE_PATH . '/views/layouts/main.php';
?>
