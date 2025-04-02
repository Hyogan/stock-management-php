<div class="container mt-5">
    <h2>Détails de la Commande</h2>
    <div class="card">
        <div class="card-header">
            <h3>Commande #<?= htmlspecialchars($order['reference']) ?></h3>
        </div>
        <div class="card-body">
            <p><strong>Client:</strong> <?= htmlspecialchars($order['client_nom']) ?> <?= htmlspecialchars($order['client_prenom']) ?></p>
            <p><strong>Date de création:</strong> <?= htmlspecialchars($order['date_creation']) ?></p>
            <p><strong>Montant Total:</strong> <?= htmlspecialchars($order['montant_total']) ?> fcfa</p>
            <p><strong>Statut:</strong> <?= htmlspecialchars($order['statut']) ?></p>
            <p><strong>Statut de Paiement:</strong> <?= htmlspecialchars($order['statut_paiement']) ?></p>
            <p><strong>Notes:</strong> <?= htmlspecialchars($order['notes']) ?></p>
            <h4>Détails des Produits</h4>
            <ul>
                <?php foreach ($order['details'] as $detail): ?>
                    <li><?= htmlspecialchars($detail['produit_nom']) ?> - Quantité: <?= htmlspecialchars($detail['quantite']) ?> - Prix Unitaire: <?= htmlspecialchars($detail['prix_unitaire']) ?> fcfa</li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="card-footer">
            <a href="/orders/edit/<?= htmlspecialchars($order['id']) ?>" class="btn btn-warning">Modifier</a>
            <a href="/orders" class="btn btn-secondary">Retour à la liste</a>
        </div>
    </div>
</div>
