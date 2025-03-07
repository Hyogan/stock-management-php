<?php
// Définir le titre de la page
$pageTitle = 'Connexion';

// Inclure l'en-tête
require_once BASE_PATH . '/views/layouts/auth_header.php';
?>

<h4 class="text-center mb-4">Connexion à votre compte</h4>

<?php if (isset($error)): ?>
    <div class="alert alert-danger" role="alert">
        <?= $error ?>
    </div>
<?php endif; ?>

<form action="<?= APP_URL ?>/auth/login" method="post">
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
    
    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember">
        <label class="form-check-label" for="remember">Se souvenir de moi</label>
    </div>
    
    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary">Se connecter</button>
    </div>
</form>

<div class="text-center mt-3">
    <a href="<?= APP_URL ?>/auth/forgot-password" class="text-decoration-none">Mot de passe oublié?</a>
</div>

<?php
// Inclure le pied de page
require_once BASE_PATH . '/views/layouts/auth_footer.php';
?>
