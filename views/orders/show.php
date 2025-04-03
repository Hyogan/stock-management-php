<div class="container mt-5">
    <h2>Détails de la Commande</h2>
    <div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Modifier le statut de la commande</h6>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $error ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

      <?php if($_SESSION['user_role'] == 'secretaire' || $_SESSION['user_role'] == 'admin') : ?>
        <form action="/orders/status/update/<?= $order['id'] ?>" method="POST">
                <div class="form-group">
                    <label for="status">Nouveau statut</label>
                    <select class="form-control" id="status" name="status">
                        <option value="pending" <?= ($order['statut'] == 'pending') ? 'selected' : '' ?>>En attente</option>
                        <option value="approved" <?= ($order['statut'] == 'approved') ? 'selected' : '' ?>>Approuvée</option>
                        <option value="rejected" <?= ($order['statut'] == 'rejected') ? 'selected' : '' ?>>Rejetée</option>
                        <option value="delivered" <?= ($order['statut'] == 'delivered') ? 'selected' : '' ?>>Livrée</option>
                        <option value="cancelled" <?= ($order['statut'] == 'cancelled') ? 'selected' : '' ?>>Annulée</option>
                    </select>
                </div>
                <button type="submit" class="btn my-2 btn-primary">Mettre à jour le statut</button>
            </form>
      <?php endif ?>
        </div>
    </div>
</div>
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
                <?php foreach ($orderDetails as $detail): ?>
                    <li class="underline border-2 border-primary">
                        - Nom: <strong><?= htmlspecialchars($detail['reference']) ?> </strong>
                        - Quantité: <strong><?= htmlspecialchars($detail['quantite']) ?> </strong>
                        - Prix Unitaire: <strong><?= htmlspecialchars($detail['prix_unitaire']) ?> fcfa, </strong>
                        - Total <strong><?= htmlspecialchars($detail['montant_total']) ?> fcfa</strong>
                      </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="card-footer">
            <a href="/orders/edit/<?= htmlspecialchars($order['id']) ?>" class="btn btn-warning">Modifier</a>
            <a href="/orders" class="btn btn-secondary">Retour à la liste</a>
        </div>
    </div>
</div>
