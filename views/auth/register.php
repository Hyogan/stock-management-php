<?php
// register.php
// require_once VIEW_PATH . '/layouts/auth_header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Inscription</h2>

                    <?php if (isset($register_error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $register_error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?php echo APP_URL; ?>/auth/user/store" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control form-control-lg" id="nom" name="nom" required>
                            <div class="invalid-feedback">
                                Veuillez entrer votre nom.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control form-control-lg" id="prenom" name="prenom" required>
                            <div class="invalid-feedback">
                                Veuillez entrer votre prénom.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                            <div class="invalid-feedback">
                                Veuillez entrer une adresse email valide.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                            <div class="invalid-feedback">
                                Veuillez entrer votre mot de passe.
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">S'inscrire</button>
                        </div>
                        <div class="text-center mt-4">
                            <a href="<?php echo APP_URL; ?>/auth/login" class="text-decoration-none">Déjà inscrit? Se connecter.</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (function () {
        'use strict'

        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation')

        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    form.classList.add('was-validated')
                }, false)
            })
    })()
</script>

<?php
// require_once VIEW_PATH . '/layouts/auth_footer.php';
?>
