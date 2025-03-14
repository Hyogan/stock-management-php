<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Modifier la Catégorie</h2>

    <?php if (!empty($_SESSION['error'])) : ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST" action="/categories/<?= $category->getId(); ?>/update">
        <div class="mb-3">
            <label for="name" class="form-label">Nom de la catégorie :</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($category->getName()); ?>" required>
        </div>
        
        <button type="submit" class="btn btn-success">Mettre à jour</button>
        <a href="/categories" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
