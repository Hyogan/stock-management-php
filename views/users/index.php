<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Liste des Utilisateurs</h6>
            <div class="dropdown no-arrow">
                <a href="<?= APP_URL ?>/users/create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-circle"></i> Ajouter un utilisateur
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Dernière connexion</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Aucun utilisateur trouvé</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td><?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <?php
                                            $typeLabels = [
                                                'admin' => '<span class="badge bg-danger">Administrateur</span>',
                                                'magasinier' => '<span class="badge bg-primary">Magasinier</span>',
                                                'secretaire' => '<span class="badge bg-info">Secrétaire</span>'
                                            ];
                                            echo $typeLabels[$user['role']] ?? $user['role'];
                                        ?>
                                    </td>
                                    <td>
                                        <?= $user['derniere_connexion'] ? formatDate($user['derniere_connexion']) : 'Jamais' ?>
                                    </td>
                                    <td>
                                        <a href="<?= APP_URL ?>/users/edit/<?= $user['id'] ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-pencil"></i>
                                        </a>
                                        <a href="<?= APP_URL ?>/users/show/<?= $user['id'] ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $user['id'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <div class="modal fade" id="deleteModal<?= $user['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?= $user['id'] ?>" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteModalLabel<?= $user['id'] ?>">Confirmer la suppression</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Êtes-vous sûr de vouloir supprimer l'utilisateur "<?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?>" ?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                            <a href="<?= APP_URL ?>/users/delete/<?= $user['id'] ?>" class="btn btn-danger">Supprimer</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($users as $user): ?>
        const myModal<?= $user['id'] ?> = new bootstrap.Modal(document.getElementById('deleteModal<?= $user['id'] ?>'));
        console.log('modal initialized for deleteModal<?= $user['id'] ?>');

        const button<?= $user['id'] ?> = document.querySelector('[data-bs-target="#deleteModal<?= $user['id'] ?>"]');

        if (button<?= $user['id'] ?>) {
            button<?= $user['id'] ?>.addEventListener('click', function() {
                console.log('modal being shown for deleteModal<?= $user['id'] ?>');
                myModal<?= $user['id'] ?>.show();
                console.log('modal shown for deleteModal<?= $user['id'] ?>');
            });
        } else {
            console.error('Button not found for modal deleteModal<?= $user['id'] ?>');
        }
    <?php endforeach; ?>
});
</script>
