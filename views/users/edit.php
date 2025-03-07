<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Modifier un Utilisateur</h6>
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

            <form action="<?= APP_URL ?>/users/update/<?= $user['id'] ?>" method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nom">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nom" name="nom" required value="<?= htmlspecialchars($user['nom']) ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="prenom">Prénom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="prenom" name="prenom" required value="<?= htmlspecialchars($user['prenom']) ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="username">Nom d'utilisateur <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" required value="<?= htmlspecialchars($user['username']) ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <small class="form-text text-muted">Laissez vide pour conserver le mot de passe actuel.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password_confirm">Confirmer le mot de passe</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="type">Type d'utilisateur <span class="text-danger">*</span></label>
                    <select class="form-control" id="type" name="type" required>
                        <option value="">Sélectionner un type</option>
                        <option value="admin" <?= ($user['type'] == 'admin') ? 'selected' : '' ?>>Administrateur</option>
                        <option value="storekeeper" <?= ($user['type'] == 'storekeeper') ? 'selected' : '' ?>>Magasinier</option>
                        <option value="secretary" <?= ($user['type'] == 'secretary') ? 'selected' : '' ?>>Secrétaire</option>
                    </select>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    <a href="<?= APP_URL ?>/users" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
