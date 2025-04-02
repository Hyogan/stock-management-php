<div class="container mt-5">
    <h2>Détails de la Sortie de Stock</h2>
    <div class="card">
        <div class="card-header">
            <h3>Sortie #<?= htmlspecialchars($exit['reference']) ?></h3>
        </div>
        <div class="card-body">
            <p><strong>Type de Sortie:</strong> <?= htmlspecialchars($exit['type_sortie']) ?></p>
            <p><strong>Date de sortie:</strong> <?= htmlspecialchars($exit['date_sortie']) ?></p>
            <p><strong>Montant Total:</strong> <?= htmlspecialchars($exit['montant_total']) ?> fcfa</p>
            <p><strong>Statut:</strong> <?= htmlspecialchars($exit['statut']) ?></p>
            <p><strong>Notes:</strong> <?= htmlspecialchars($exit['notes']) ?></p>
            <h4>Détails des Produits</h4>
            <ul>
                <?php foreach ($exit['details'] as $detail): ?>
                    <li><?= htmlspecialchars($detail['produit_nom']) ?> - Quantité: <?= htmlspecialchars($detail['quantite']) ?> - Prix Unitaire: <?= htmlspecialchars($detail['prix_unitaire']) ?> fcfa</li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="card-footer">
            <a href="/stock/edit/exit/<?= htmlspecialchars($exit['id']) ?>" class="btn btn-warning">Modifier</a>
            <a href="/stock" class="btn btn-secondary">Retour à la liste</a>
        </div>
    </div>
</div>
