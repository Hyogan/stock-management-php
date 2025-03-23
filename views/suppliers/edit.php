<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><?= $title ?></h3>
                </div>

                <form method="post" action="/suppliers/<?= isset($supplier['id']) ? 'update/' . $supplier['id'] : 'store' ?>">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nom">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['nom']) ? 'is-invalid' : '' ?>" id="nom" name="nom" value="<?= htmlspecialchars($old['nom'] ?? ($supplier['nom'] ?? '')) ?>" required>
                            <?php if (isset($errors['nom'])): ?>
                                <div class="invalid-feedback"><?= $errors['nom'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="adresse">Adresse</label>
                            <textarea class="form-control" id="adresse" name="adresse" rows="3"><?= htmlspecialchars($old['adresse'] ?? ($supplier['adresse'] ?? '')) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="telephone">Téléphone</label>
                            <input type="text" class="form-control" id="telephone" name="telephone" value="<?= htmlspecialchars($old['telephone'] ?? ($supplier['telephone'] ?? '')) ?>">
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? ($supplier['email'] ?? '')) ?>">
                        </div>

                        <div class="form-group">
                            <label for="statut">Statut</label>
                            <select class="form-control" id="statut" name="statut">
                                <option value="actif" <?= (isset($old['statut']) && $old['statut'] === 'actif') || (isset($supplier['statut']) && $supplier['statut'] === 'actif') ? 'selected' : '' ?>>Actif</option>
                                <option value="inactif" <?= (isset($old['statut']) && $old['statut'] === 'inactif') || (isset($supplier['statut']) && $supplier['statut'] === 'inactif') ? 'selected' : '' ?>>Inactif</option>
                            </select>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <a href="/suppliers" class="btn btn-default">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
