
<div class="container mt-4">
    <h2>Modifier la Catégorie</h2>

    <?php if (!empty($_SESSION['error'])) : ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST" action="/categories/update/<?= $category['id']; ?>">
        <div class="mb-3">
            <label for="name" class="form-label">Nom de la catégorie :</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($category['nom']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Description de la catégorie :</label>
            <input type="text" class="form-control" id="description" name="description" value="<?= htmlspecialchars($category['description']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="statut" class="form-label">Statut:</label>
            <select  class="form-control" id="statut" name="statut" required>
              <option value="actif">Actif</option>
              <option value="inactif">Inactif</option>
            </select>
        </div>
        
        
        <button type="submit" class="btn btn-success">Mettre à jour</button>
        <a href="/categories" class="btn btn-secondary">Annuler</a>
    </form>
</div>
