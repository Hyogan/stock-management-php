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
