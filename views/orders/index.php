
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800"><?= $pageTitle ?></h1>
    
    <!-- Filtres et recherche -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Filtres</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="/orders" class="row">
                <div class="col-md-4 mb-3">
                    <label for="search">Recherche</label>
                    <input type="text" class="form-control" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Référence, client...">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="status">Statut</label>
                    <select class="form-control" id="status" name="status">
                        <option value="" <?= $status === '' ? 'selected' : '' ?>>Tous</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>En attente</option>
                        <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approuvée</option>
                        <option value="delivered" <?= $status === 'delivered' ? 'selected' : '' ?>>Livrée</option>
                        <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Annulée</option>
                        <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejetée</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="sort">Trier par</label>
                    <select class="form-control" id="sort" name="sort">
                        <option value="date_creation" <?= $sort === 'date_creation' ? 'selected' : '' ?>>Date de création</option>
                        <option value="reference" <?= $sort === 'reference' ? 'selected' : '' ?>>Référence</option>
                        <option value="montant_total" <?= $sort === 'montant_total' ? 'selected' : '' ?>>Montant</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="order">Ordre</label>
                    <select class="form-control" id="order" name="order">
                        <option value="desc" <?= $order === 'desc' ? 'selected' : '' ?>>Décroissant</option>
                        <option value="asc" <?= $order === 'asc' ? 'selected' : '' ?>>Croissant</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Filtres</button>
                    <a href="/orders" class="btn btn-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des commandes -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Liste des commandes</h6>
            <div class="dropdown no-arrow">
             <?php if($_SESSION['user_role'] != 'magasinier') : ?>
                  <a href="/orders/create" class="btn btn-primary btn-sm">
                      <i class="fas fa-plus fa-sm"></i> Nouvelle commande
                  </a> 
             <?php endif ?>
                <!-- <a href="/orders/stats" class="btn btn-info btn-sm">
                    <i class="fas fa-chart-bar fa-sm"></i> Statistiques
                </a>
                <a href="/orders/reports" class="btn btn-secondary btn-sm">
                    <i class="fas fa-file-alt fa-sm"></i> Rapports
                </a> -->
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($orders)): ?>
                <div class="alert alert-info">
                    Aucune commande trouvée.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Référence</th>
                                <th>Client</th>
                                <th>Date</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Paiement</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['reference']) ?></td>
                                    <td><?= htmlspecialchars($order['client_nom'] . ' ' . $order['client_prenom']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($order['date_creation'])) ?></td>
                                    <td><?= number_format($order['montant_total'], 2, ',', ' ') ?> fcfa</td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        $statusText = '';
                                        
                                        switch ($order['statut']) {
                                            case 'pending':
                                                $statusClass = 'warning';
                                                $statusText = 'En attente';
                                                break;
                                            case 'approved':
                                                $statusClass = 'primary';
                                                $statusText = 'Approuvée';
                                                break;
                                            case 'delivered':
                                                $statusClass = 'success';
                                                $statusText = 'Livrée';
                                                break;
                                            case 'cancelled':
                                                $statusClass = 'danger';
                                                $statusText = 'Annulée';
                                                break;
                                            case 'rejected':
                                                $statusClass = 'danger';
                                                $statusText = 'Rejetée';
                                                break;
                                            default:
                                                $statusClass = 'secondary';
                                                $statusText = $order['statut'];
                                        }
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $paymentClass = '';
                                        $paymentText = '';
                                        
                                        switch ($order['statut_paiement']) {
                                            case 'pending':
                                                $paymentClass = 'warning';
                                                $paymentText = 'En attente';
                                                break;
                                            case 'partial':
                                                $paymentClass = 'info';
                                                $paymentText = 'Partiel';
                                                break;
                                            case 'paid':
                                                $paymentClass = 'success';
                                                $paymentText = 'Payé';
                                                break;
                                            default:
                                                $paymentClass = 'secondary';
                                                $paymentText = $order['statut_paiement'];
                                        }
                                        ?>
                                        <span class="badge bg-<?= $paymentClass ?>"><?= $paymentText ?></span>
                                    </td>
                                    <td>
                                        <a href="/orders/show/<?= $order['id'] ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($order['statut'] === 'pending'): ?>
                                            <a href="/orders/edit/<?= $order['id'] ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="/orders/delete/<?= $order['id'] ?>" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal<?= $order['id'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php if($_SESSION['user_role'] != 'magasinier') : ?>
                                                <a href="/orders/approve/<?= $order['id'] ?>" class="btn btn-primary btn-sm">
                                                   <i class="fas fa-check-circle"></i>
                                                </a>  
                                           <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                
                                <!-- Modal de suppression -->
                                <div class="modal fade" id="deleteModal<?= $order['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                Êtes-vous sûr de vouloir supprimer la commande <strong><?= htmlspecialchars($order['reference']) ?></strong> ?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                <form action="/orders/<?= $order['id'] ?>/delete" method="POST">
                                                    <button type="submit" class="btn btn-danger">Supprimer</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
