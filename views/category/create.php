
<div class="container mt-4">
    <h2>Ajouter une Catégorie</h2>

    <?php if (!empty($_SESSION['error'])) : ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST" action="/categories/store">
        <div class="mb-3">
            <label for="name" class="form-label">Nom de la catégorie :</label>
            <input type="text" class="form-control" id="nom" name="nom" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description de la catégorie :</label>
            <input type="text" class="form-control" id="description" name="description" required>
        </div>

        <div class="mb-3">
            <label for="statut" class="form-label">Statut:</label>
            <select  class="form-control" id="statut" name="statut" required>
              <option value="actif">Actif</option>
              <option value="inactif">Inactif</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="/categories" class="btn btn-secondary">Annuler</a>
    </form>
</div>

