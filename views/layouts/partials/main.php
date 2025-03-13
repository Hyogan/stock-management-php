<?php
// Inclure l'en-tête
include_once BASE_PATH . '/views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include_once BASE_PATH . '/views/layouts/sidebar.php'; ?>
        
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?= $pageTitle ?? 'Tableau de bord' ?></h1>
                <?php if (isset($actionButtons)): ?>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <?= $actionButtons ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Messages flash -->
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
                    <?= $_SESSION['flash_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php 
                    unset($_SESSION['flash_message']);
                    unset($_SESSION['flash_type']);
                ?>
            <?php endif; ?>

            <!-- Le contenu de la page sera inséré ici -->
            <?php if (isset($content)): ?>
                <?= $content ?>
            <?php endif; ?>

        </main>
    </div>
</div>

<!-- Footer -->
<?php include_once BASE_PATH . '/views/layouts/footer.php'; ?>
