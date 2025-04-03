<?php
$pageTitle = 'Tableau de bord Secrétaire';
?>

<div class="">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tableau de bord Secrétaire</h1>
        <a href="/orders/create" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Nouvelle commande
        </a>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Commandes en cours Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Commandes en cours</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($orders) ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bons de livraison Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Bons de livraison</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($deliveries) ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Factures Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Factures</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['invoices'] ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clients Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Clients</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($clients) ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Dernières commandes -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dernières commandes</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentOrders)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Client</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td><?= $order['id'] ?></td>
                                        <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($order['client_name']) ?></td>
                                        <td><?= number_format($order['total_amount'], 2, ',', ' ') ?> fcfa</td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            switch($order['statut']) {
                                                case 'en_attente': $statusClass = 'warning'; break;
                                                case 'approuve': $statusClass = 'success'; break;
                                                case 'rejete': $statusClass = 'danger'; break;
                                                default: $statusClass = 'secondary';
                                            }
                                            ?>
                                            <span class="badge bg-<?= $statusClass ?>">
                                                <?= ucfirst(str_replace('_', ' ', $order['statut'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="/orders/view/<?= $order['id'] ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/orders/edit/<?= $order['id'] ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <!-- <a href="/deliveries/create/<?= $order['id'] ?>" class="btn btn-success btn-sm">
                                                <i class="fas fa-truck"></i>
                                            </a> -->
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center">Aucune commande récente.</p>
                    <?php endif; ?>
                    <div class="mt-3 text-center">
                        <a href="/orders" class="btn btn-primary">Voir toutes les commandes</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Factures impayées -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Factures impayées</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($unpaidInvoices)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>N° Facture</th>
                                        <th>Date</th>
                                        <th>Client</th>
                                        <th>Montant</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($unpaidInvoices as $invoice): ?>
                                    <tr>
                                        <td><?= $invoice['invoice_number'] ?></td>
                                        <td><?= date('d/m/Y', strtotime($invoice['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($invoice['client_name']) ?></td>
                                        <td><?= number_format($invoice['total_amount'], 2, ',', ' ') ?> fcfa</td>
                                        <td>
                                            <a href="/invoices/view/<?= $invoice['id'] ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/payments/create/<?= $invoice['id'] ?>" class="btn btn-success btn-sm">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center">Aucune facture impayée.</p>
                    <?php endif; ?>
                    <div class="mt-3 text-center">
                        <a href="/invoices" class="btn btn-primary">Voir toutes les factures</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
