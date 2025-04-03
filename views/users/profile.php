<div class="container">
        <h1>Mettez à jour votre Profil</h1>
        <?php if (isset($errors) && !empty($errors)): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?php foreach ($errors as $error): ?>
            <?= $error ?>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
            <?php endforeach ?>
          </div>
      <?php endif; ?>

        <form action="/users/profile/update" method="post">
            <div class="mb-3">
                <label for="nom" class="form-label">Nom :</label>
                <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="prenom" class="form-label">Prénom :</label>
                <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur :</label>
                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email :</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Nouveau mot de passe :</label>
                <input type="password" class="form-control" id="password" name="password">'
                <small>Laissez le mot de passe vide pour conserver le mot de passe actuel </small>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe :</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
            </div>

            <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </form>
    </div>
