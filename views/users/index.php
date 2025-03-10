<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Liste des Utilisateurs</h6>
            <div class="dropdown no-arrow">
                <a href="<?= APP_URL ?>/users/add" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Ajouter un utilisateur
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
                            <th>Nom d'utilisateur</th>
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
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td>
                                        <?php
                                            $typeLabels = [
                                                'admin' => '<span class="badge badge-danger">Administrateur</span>',
                                                'storekeeper' => '<span class="badge badge-primary">Magasinier</span>',
                                                'secretary' => '<span class="badge badge-info">Secrétaire</span>'
                                            ];
                                            echo $typeLabels[$user['type']] ?? $user['type'];
                                        ?>
                                    </td>
                                    <td>
                                        <?= $user['derniere_connexion'] ? formatDate($user['derniere_connexion']) : 'Jamais' ?>
                                    </td>
                                    <td>
                                        <a href="<?= APP_URL ?>/users/edit/<?= $user['id'] ?>" class="btn btn-primary btn-sm">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal<?= $user['id'] ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <!-- Modal de confirmation de suppression -->
                                            <div class="modal fade" id="deleteModal<?= $user['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?= $user['id'] ?>" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteModalLabel<?= $user['id'] ?>">Confirmer la suppression</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Êtes-vous sûr de vouloir supprimer l'utilisateur "<?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?>" ?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
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
