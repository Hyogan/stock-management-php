<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Ajouter un Produit</h6>
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

            <form action="<?= APP_URL ?>/products/store" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nom">Nom du produit <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nom" name="nom" required value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="categorie_id">Catégorie <span class="text-danger">*</span></label>
                            <select class="form-control" id="categorie_id" name="categorie_id" required>
                                <option value="">Sélectionner une catégorie</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= (isset($_POST['categorie_id']) && $_POST['categorie_id'] == $category['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="prix">Prix <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">€</span>
                                </div>
                                <input type="number" class="form-control" id="prix" name="prix" step="0.01" min="0" required value="<?= isset($_POST['prix']) ? htmlspecialchars($_POST['prix']) : '' ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="stock">Stock initial <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="stock" name="stock" min="0" required value="<?= isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : '0' ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">Image du produit</label>
                    <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
                    <small class="form-text text-muted">Formats acceptés: JPG, PNG, GIF. Taille maximale: 2 Mo.</small>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="<?= APP_URL ?>/products" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>


<?php
$pageTitle = 'Ajouter un produit';
ob_start();
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Ajouter un produit</h1>
        <a href="/products" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour à la liste
        </a>
    </div>

    <!-- Formulaire d'ajout de produit -->
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

            <form method="POST" action="/products/store" enctype="multipart/form-data">
                <div class="row">
                    <!-- Informations générales -->
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="reference">Référence <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="reference" name="reference" required 
                                   value="<?= htmlspecialchars($product['reference'] ?? '') ?>">
                            <small class="form-text text-muted">Référence unique du produit</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="designation">Désignation <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="designation" name="designation" required
                                   value="<?= htmlspecialchars($product['designation'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="id_categorie">Catégorie</label>
                            <select class="form-control" id="id_categorie" name="id_categorie">
                                <option value="">Sélectionner une catégorie</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= (isset($product['id_categorie']) && $product['id_categorie'] == $category['id']) ? 'selected' : '' ?>>
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
                                    <option value="<?= $supplier['id'] ?>" <?= (isset($product['id_fournisseur']) && $product['id_fournisseur'] == $supplier['id']) ? 'selected' : '' ?>>
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
                                value="<?= htmlspecialchars($product['prix_ht'] ?? '') ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">€</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="tva">TVA (%)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="tva" name="tva" step="0.1" min="0" max="100"
                                       value="<?= htmlspecialchars($product['tva'] ?? '20') ?>">
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
                            <label for="stock_initial">Stock initial</label>
                            <input type="number" class="form-control" id="stock_initial" name="stock_initial" min="0" step="1"
                                   value="<?= htmlspecialchars($product['stock_initial'] ?? '0') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="stock_minimum">Stock minimum (alerte)</label>
                            <input type="number" class="form-control" id="stock_minimum" name="stock_minimum" min="0" step="1"
                                   value="<?= htmlspecialchars($product['stock_minimum'] ?? '5') ?>">
                            <small class="form-text text-muted">Une alerte sera affichée lorsque le stock sera inférieur à cette valeur</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Image du produit</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="image" name="image" accept="image/*">
                                <label class="custom-file-label" for="image">Choisir un fichier</label>
                            </div>
                            <small class="form-text text-muted">Format recommandé: JPG, PNG. Taille max: 2MB</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="actif" name="actif" value="1" 
                                       <?= (isset($product['actif']) && $product['actif'] == 0) ? '' : 'checked' ?>>
                                <label class="custom-control-label" for="actif">Produit actif</label>
                            </div>
                            <small class="form-text text-muted">Les produits inactifs n'apparaissent pas dans le catalogue client</small>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <a href="/products" class="btn btn-secondary">Annuler</a>
                    </div>
                </div>
            </form>
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
        const fileName = e.target.files[0].name;
        const label = e.target.nextElementSibling;
        label.textContent = fileName;
    });
});
</script>

<?php
$content = ob_get_clean();
include BASE_PATH . '/views/layouts/main.php';
?>
