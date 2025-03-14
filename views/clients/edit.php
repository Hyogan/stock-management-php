
<div class="container mt-5">
    <h2>Modifier un Client</h2>
    <form method="POST" action="/clients/update">
        <input type="hidden" name="id" value="<?= htmlspecialchars($client['id']) ?>">
        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($client['nom']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($client['prenom']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($client['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="telephone" class="form-label">Téléphone</label>
            <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($client['telephone']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="ville" class="form-label">Ville</label>
            <input type="text" name="ville" class="form-control" value="<?= htmlspecialchars($client['ville']) ?>" required>
        </div>
        <button type="submit" class="btn btn-warning">Mettre à jour</button>
        <a href="/clients" class="btn btn-secondary">Annuler</a>
    </form>
</div>

