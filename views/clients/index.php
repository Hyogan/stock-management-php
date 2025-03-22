<div class="container mt-5">
    <h2>Liste des Clients</h2>
    <a href="/clients/create" class="btn btn-primary mb-3">Ajouter un Client</a>
    <form method="GET" action="/clients/search" class="mb-3">
        <input type="text" name="q" class="form-control" placeholder="Rechercher un client...">
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Ville</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?= htmlspecialchars($client['id']) ?></td>
                    <td><?= htmlspecialchars($client['nom']) ?></td>
                    <td><?= htmlspecialchars($client['prenom']) ?></td>
                    <td><?= htmlspecialchars($client['email']) ?></td>
                    <td><?= htmlspecialchars($client['telephone']) ?></td>
                    <td><?= htmlspecialchars($client['ville']) ?></td>
                    <td>
                        <a href="/clients/show/<?= $client['id'] ?>" class="btn btn-info btn-sm">Voir</a>
                        <a href="/clients/edit/<?= $client['id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                        <a href="/clients/delete/<?= $client['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?');">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
