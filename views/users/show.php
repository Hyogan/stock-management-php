<div class="mt-5">
    <h1>Détails de l'utilisateur</h1>

    <?php if (isset($user)): ?>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($user['nom']) . ' ' . htmlspecialchars($user['prenom']) ?></h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>ID:</strong> <?= htmlspecialchars($user['id']) ?></li>
                    <li class="list-group-item"><strong>Nom:</strong> <?= htmlspecialchars($user['nom']) ?></li>
                    <li class="list-group-item"><strong>Prénom:</strong> <?= htmlspecialchars($user['prenom']) ?></li>
                    <li class="list-group-item"><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></li>
                    <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></li>
                    <li class="list-group-item"><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></li>
                    <li class="list-group-item"><strong>Statut:</strong> <?= htmlspecialchars($user['statut']) ?></li>
                    <li class="list-group-item"><strong>Date de création:</strong> <?= htmlspecialchars($user['date_creation']) ?></li>
                    <li class="list-group-item"><strong>Dernière connexion:</strong> <?= htmlspecialchars($user['derniere_connexion']) ?></li>
                </ul>
                <div class="mt-3">
                    <a href="/users/edit/<?= htmlspecialchars($user['id']) ?>" class="btn btn-primary">Modifier</a>
                    <a href="/users" class="btn btn-secondary">Retour à la liste</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            Utilisateur non trouvé.
        </div>
    <?php endif; ?>
</div>
