<?php
// Définir le titre de la page
$pageTitle = 'Inscription';

// Inclure l'en-tête
require_once BASE_PATH . '/views/layouts/auth_header.php';
?>

<h4 class="text-center mb-4">Créer un compte</h4>

<?php if (isset($error)): ?>
    <div class="alert alert-danger" role="alert">
        <?= $error ?>
    </div>
<?php endif; ?>

<form action="<?= APP_URL ?>/auth/register" method="post">
    <div class="mb-3">
        <label for="username" class="form-label">Nom d'utilisateur</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-user"></i></span>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
    </div>
    
    <div class="mb-3">
        <label for="password" class="form-label">Mot de passe</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
    </div>

    <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
    </div>
    
    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary">S'inscrire</button>
    </div>
</form>

<div class="text-center mt-3">
    <a href="<?= APP_URL ?>/auth/login" class="text-decoration-none">Déjà un compte? Se connecter</a>
</div>

<?php
// Inclure le pied de page
require_once BASE_PATH . '/views/layouts/auth_footer.php';
?>
