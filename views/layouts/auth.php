<?php
$stylesheet = "/assets/css/auth.css";

ob_start(); ?>
<div class="auth-container">
    <?= $content ?>
</div>
<?php $content = ob_get_clean();

include BASE_PATH . "/views/layouts/base.php";
