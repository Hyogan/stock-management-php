<div class="container mt-5">
    <h2>Modifier la Commande</h2>
    <form method="POST" action="/orders/update/<?= htmlspecialchars($order['id']) ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($order['id']) ?>">
        <div class="mb-3">
            <label for="client" class="form-label">Client</label>
            <select name="client_id" class="form-control" required>
                <?php foreach ($clients as $client): ?>
                    <option value="<?= $client['id'] ?>" <?= ($client['id'] == $order['client_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($client['nom']) ?> <?= htmlspecialchars($client['prenom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="montant_total" class="form-label">Montant Total</label>
            <input type="number" name="montant_total" class="form-control" value="<?= htmlspecialchars($order['montant_total']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="statut" class="form-label">Statut</label>
            <select name="statut" class="form-control" required>
                <option value="pending" <?= ($order['statut'] == 'pending') ? 'selected' : '' ?>>En attente</option>
                <option value="approved" <?= ($order['statut'] == 'approved') ? 'selected' : '' ?>>Approuvée</option>
                <option value="rejected" <?= ($order['statut'] == 'rejected') ? 'selected' : '' ?>>Rejetée</option>
                <option value="delivered" <?= ($order['statut'] == 'delivered') ? 'selected' : '' ?>>Livrée</option>
                <option value="cancelled" <?= ($order['statut'] == 'cancelled') ? 'selected' : '' ?>>Annulée</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="statut_paiement" class="form-label">Statut de Paiement</label>
            <select name="statut_paiement" class="form-control" required>
                <option value="pending" <?= ($order['statut_paiement'] == 'pending') ? 'selected' : '' ?>>En attente</option>
                <option value="partial" <?= ($order['statut_paiement'] == 'partial') ? 'selected' : '' ?>>Partiel</option>
                <option value="paid" <?= ($order['statut_paiement'] == 'paid') ? 'selected' : '' ?>>Payé</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($order['notes']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-warning">Mettre à jour</button>
        <a href="/orders" class="btn btn-secondary">Annuler</a>
    </form>
</div>
