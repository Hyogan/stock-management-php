<div class="container mt-4">
    <h2>Liste des Sorties de Stock</h2>

    <a href="/exits/create" class="btn btn-primary mb-3">Ajouter une Sortie</a>

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
                <th>Date de Sortie</th>
                <th>Type de Sortie</th>
                <th>Montant Total</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($exits as $exit): ?>
                <tr>
                    <td><?= htmlspecialchars($exit['reference']); ?></td>
                    <td><?= htmlspecialchars($exit['date_sortie']); ?></td>
                    <td><?= htmlspecialchars($exit['type_sortie']); ?></td>
                    <td><?= htmlspecialchars($exit['montant_total']); ?></td>
                    <td><?= htmlspecialchars($exit['notes']); ?></td>
                    <td>
                        <a href="/exits/edit/<?= $exit['id']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="/exits/delete/<?= $exit['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette sortie ?');">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
