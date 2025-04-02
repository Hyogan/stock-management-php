<div class="container mt-5">
    <h2>Détails de l'Entrée de Stock</h2>
    <div class="card">
        <div class="card-header">
            <h3>Entrée #<?= htmlspecialchars($entry['reference']) ?></h3>
        </div>
        <div class="card-body">
            <p><strong>Fournisseur:</strong> <?= htmlspecialchars($entry['fournisseur_nom']) ?></p>
            <p><strong>Date d'entrée:</strong> <?= htmlspecialchars($entry['date_entree']) ?></p>
            <p><strong>Montant Total:</strong> <?= htmlspecialchars($entry['montant_total']) ?> fcfa</p>
            <p><strong>Statut:</strong> <?= htmlspecialchars($entry['statut']) ?></p>
            <p><strong>Notes:</strong> <?= htmlspecialchars($entry['notes']) ?></p>
            <h4>Détails des Produits</h4>
            <ul>
                <?php foreach ($entry['details'] as $detail): ?>
                    <li><?= htmlspecialchars($detail['produit_nom']) ?> - Quantité: <?= htmlspecialchars($detail['quantite']) ?> - Prix Unitaire: <?= htmlspecialchars($detail['prix_unitaire']) ?> fcfa</li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="card-footer">
            <a href="/stock/edit/entry/<?= htmlspecialchars($entry['id']) ?>" class="btn btn-warning">Modifier</a>
            <a href="/stock" class="btn btn-secondary">Retour à la liste</a>
        </div>
    </div>
</div>
