<?php
$stylesheet = "/assets/css/auth.css";
$header = BASE_PATH . "/views/layouts/partials/auth_header.php";
$footer = BASE_PATH . "/views/layouts/partials/auth_footer.php";

ob_start(); ?>
<div class="auth-container container row justify-content-center">
    <?= $content ?>
</div>
<?php $content = ob_get_clean();

include BASE_PATH . "/views/layouts/base.php";
