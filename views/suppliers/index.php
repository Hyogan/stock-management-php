<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Liste des fournisseurs</h3>
                    <div class="card-tools">
                        <a href="/suppliers/create" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Ajouter un fournisseur
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table id="suppliers-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Téléphone</th>
                                <th>Email</th>
                                <th>Statut</th>
                                <th>Date de création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suppliers as $supplier): ?>
                            <tr>
                                <td><?= $supplier['id'] ?></td>
                                <td><?= htmlspecialchars($supplier['nom']) ?></td>
                                <td><?= htmlspecialchars($supplier['telephone'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($supplier['email'] ?? '-') ?></td>
                                <td>
                                    <span class="badge badge-<?= $supplier['statut'] === 'actif' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($supplier['statut']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($supplier['date_creation'])) ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/suppliers/show/<?= $supplier['id'] ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/suppliers/edit/<?= $supplier['id'] ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="/suppliers/change-status/<?= $supplier['id'] ?>" class="btn btn-<?= $supplier['statut'] === 'actif' ? 'danger' : 'success' ?> btn-sm">
                                            <i class="fas fa-<?= $supplier['statut'] === 'actif' ? 'times' : 'check' ?>"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm delete-btn" data-toggle="modal" data-target="#deleteModal" data-id="<?= $supplier['id'] ?>" data-name="<?= htmlspecialchars($supplier['nom']) ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmation de suppression</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir supprimer le fournisseur <span id="supplier-name"></span> ?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <a href="#" id="confirm-delete" class="btn btn-danger">Supprimer</a>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#suppliers-table').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "language": {
                "url": "/assets/plugins/datatables/fr-FR.json"
            }
        });
        
        $('.delete-btn').click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            $('#supplier-name').text(name);
            $('#confirm-delete').attr('href', '/suppliers/delete/' + id);
        });
    });
</script>
