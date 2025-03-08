<?php require_once VIEW_PATH . '/layouts/auth_header.php'; ?>

  <div class="auth-form">
    <h2 class="text-center mb-4">Connexion</h2>

  <?php if (isset($error)): ?>
      <div class="alert alert-danger">
          <?php echo $error; ?>
      </div>
  <?php endif; ?>

    <form method="post" action="<?php echo APP_URL; ?>/auth/authenticate" class="needs-validation" novalidate>
        <div class="form-group mb-3">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
            <div class="invalid-feedback">
                Veuillez entrer une adresse email valide.
            </div>
        </div>

        <div class="form-group mb-3">
            <label for="password">Mot de passe</label>
            <input type="password" class="form-control" id="password" name="password" required>
            <div class="invalid-feedback">
                Veuillez entrer votre mot de passe.
            </div>
        </div>

        <div class="form-group mb-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Se souvenir de moi</label>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
        </div>

        <div class="text-center mt-3">
            <a href="<?php echo APP_URL; ?>/auth/forgot-password">Mot de passe oublié?</a>
        </div>
        <div class="text-center mt-2">
            <a href="<?php echo APP_URL; ?>/auth/register">Créer un compte</a>
        </div>
    </form>
</div>

<?php require_once VIEW_PATH . '/layouts/auth_footer.php'; ?>
