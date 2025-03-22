<div class="container mt-4">
    <h2>Liste des Entrées de Stock</h2>

    <a href="/entries/create" class="btn btn-primary mb-3">Ajouter une Entrée</a>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Référence</th>
                <th>Date d'Entrée</th>
                <th>Fournisseur</th>
                <th>Client</th>
                <th>Montant Total</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($entries as $entry): ?>
                <tr>
                    <td><?= htmlspecialchars($entry['reference']); ?></td>
                    <td><?= htmlspecialchars($entry['date_entree']); ?></td>
                    <td><?= htmlspecialchars($entry['nom_fournisseur'] ?? 'N/A'); ?></td>
                    <td><?= htmlspecialchars($entry['id_client'] ? \App\Models\Client::getById($entry['id_client'])['nom'] : 'N/A') ; ?></td>
                    <td><?= htmlspecialchars($entry['montant_total']); ?></td>
                    <td><?= htmlspecialchars($entry['notes']); ?></td>
                    <td>
                        <a href="/entries/edit/<?= $entry['id']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="/entries/delete/<?= $entry['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette entrée ?');">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
