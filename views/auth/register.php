<?php
// register.php
require_once VIEW_PATH . '/layouts/auth_header.php';
?>

    <h2 class="text-center mb-4">Inscription</h2>

    <?php if (isset($register_error)): ?>
        <div class="alert alert-danger">
            <?php echo $register_error; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo APP_URL; ?>/auth/user/store" class="needs-validation" novalidate>
        <div class="form-group mb-3">
            <label for="nom">Nom</label>
            <input type="text" class="form-control" id="nom" name="nom" required>
            <div class="invalid-feedback">
                Veuillez entrer votre nom.
            </div>
        </div>
        <div class="form-group mb-3">
            <label for="prenom">Prenom</label>
            <input type="text" class="form-control" id="prenom" name="prenom" required>
            <div class="invalid-feedback">
                Veuillez entrer votre prenom.
            </div>
        </div>

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

        <div class="form-group">
            <button type="submit" class="btn btn-success btn-block">S'inscrire</button>
        </div>
        <div class="text-center mt-2">
            <a href="<?php echo APP_URL; ?>/auth/login">Déja inscrit? se connecter.</a>
        </div>
    </form>

<?php require_once VIEW_PATH . '/layouts/auth_footer.php'; ?>
