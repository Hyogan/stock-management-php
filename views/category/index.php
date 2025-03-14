
<div class="container mt-4">
    <h2><?= $pageTitle ?? 'Gestion des Catégories' ?></h2>

    <div class="d-flex justify-content-between mb-3">
        <form method="GET" action="/categories" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Rechercher..." value="<?= htmlspecialchars($search ?? '') ?>">
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>
        <a href="/categories/create" class="btn btn-success">+ Ajouter une catégorie</a>
    </div>

    <?php if (!empty($_SESSION['success'])) : ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])) : ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Description</th>
                <th>statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category) : ?>
                <tr>
                    <td><?= $category['id']; ?></td>
                    <td><?= htmlspecialchars($category['nom']); ?></td>
                    <td><?= htmlspecialchars($category['description']); ?></td>
                    <td><?= htmlspecialchars($category['statut']); ?></td>
                    <td>
                        <a href="/categories/<?= $category['id']; ?>/edit" class="btn btn-warning btn-sm">Modifier</a>
                        <a href="/categories/<?= $category['id']; ?>/delete" onclick="return confirm('Confirmer la suppression ?');" class="btn btn-danger btn-sm">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
