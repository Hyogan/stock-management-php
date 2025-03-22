<div class="container mt-5">
    <h2>Détails du Client</h2>
    <div class="card">
        <div class="card-header">
            <h3><?= htmlspecialchars($client['nom']) ?> <?= htmlspecialchars($client['prenom']) ?></h3>
        </div>
        <div class="card-body">
            <p><strong>Email:</strong> <?= htmlspecialchars($client['email']) ?></p>
            <p><strong>Téléphone:</strong> <?= htmlspecialchars($client['telephone']) ?></p>
            <p><strong>Adresse:</strong> <?= htmlspecialchars($client['adresse']) ?></p>
            <p><strong>Ville:</strong> <?= htmlspecialchars($client['ville']) ?></p>
            <p><strong>Code Postal:</strong> <?= htmlspecialchars($client['code_postal']) ?></p>
            <p><strong>Pays:</strong> <?= htmlspecialchars($client['pays']) ?></p>
        </div>
        <div class="card-footer">
            <a href="/clients/edit/<?= htmlspecialchars($client['id']) ?>" class="btn btn-warning">Modifier</a>
            <a href="/clients" class="btn btn-secondary">Retour à la liste</a>
        </div>
    </div>
</div>
