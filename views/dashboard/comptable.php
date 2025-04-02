<?php
$pageTitle = 'Tableau de bord Comptable';
ob_start();
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tableau de bord Comptable</h1>
        <div>
            <a href="/invoices/create" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-2">
                <i class="fas fa-file-invoice fa-sm text-white-50"></i> Nouvelle facture
            </a>
            <a href="/payments/create" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
                <i class="fas fa-money-bill-wave fa-sm text-white-50"></i> Nouveau paiement
            </a>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Chiffre d'affaires Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Chiffre d'affaires (mois)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['monthly_revenue'] ?? 0, 2, ',', ' ') ?> fcfa</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Factures impayées Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Factures impayées</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['unpaid_amount'] ?? 0, 2, ',', ' ') ?> fcfa</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paiements reçus Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="        <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Paiements reçus (mois)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['monthly_payments'] ?? 0, 2, ',', ' ') ?> fcfa</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-euro-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nombre de factures Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Factures (mois)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['monthly_invoices'] ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Factures en attente de paiement -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Factures en attente de paiement</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Actions:</div>
                            <a class="dropdown-item" href="/invoices?status=unpaid">Voir toutes</a>
                            <a class="dropdown-item" href="/reports/unpaid-invoices">Exporter</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($unpaidInvoices)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>N° Facture</th>
                                        <th>Client</th>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($unpaidInvoices as $invoice): ?>
                                    <tr>
                                        <td><?= $invoice['numero'] ?></td>
                                        <td><?= htmlspecialchars($invoice['client_nom']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($invoice['date_emission'])) ?></td>
                                        <td><?= number_format($invoice['montant_total'], 2, ',', ' ') ?> fcfa</td>
                                        <td>
                                            <a href="/invoices/view/<?= $invoice['id'] ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/payments/create?invoice_id=<?= $invoice['id'] ?>" class="btn btn-success btn-sm">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center">Aucune facture en attente de paiement.</p>
                    <?php endif; ?>
                    <div class="mt-3 text-center">
                        <a href="/invoices" class="btn btn-primary">Voir toutes les factures</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Derniers paiements -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Derniers paiements reçus</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Actions:</div>
                            <a class="dropdown-item" href="/payments">Voir tous</a>
                            <a class="dropdown-item" href="/reports/payments">Exporter</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentPayments)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>N° Facture</th>
                                        <th>Client</th>
                                        <th>Montant</th>
                                        <th>Mode</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentPayments as $payment): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($payment['date_paiement'])) ?></td>
                                        <td><?= $payment['facture_numero'] ?></td>
                                        <td><?= htmlspecialchars($payment['client_nom']) ?></td>
                                        <td><?= number_format($payment['montant'], 2, ',', ' ') ?> fcfa</td>
                                        <td>
                                            <?php 
                                            $badgeClass = 'badge-secondary';
                                            switch($payment['mode_paiement']) {
                                                case 'virement': $badgeClass = 'badge-primary'; break;
                                                case 'carte': $badgeClass = 'badge-info'; break;
                                                case 'especes': $badgeClass = 'badge-success'; break;
                                                case 'cheque': $badgeClass = 'badge-warning'; break;
                                            }
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= ucfirst($payment['mode_paiement']) ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center">Aucun paiement récent.</p>
                    <?php endif; ?>
                    <div class="mt-3 text-center">
                        <a href="/payments" class="btn btn-primary">Voir tous les paiements</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Statistiques par statut -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Répartition des factures par statut</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($stats['par_statut'])): ?>
                        <div class="row">
                            <?php foreach ($stats['par_statut'] as $statut => $data): ?>
                            <div class="col-md-6 mb-4">
                                <?php 
                                $cardClass = 'border-left-secondary';
                                switch($statut) {
                                    case 'payee': $cardClass = 'border-left-success'; break;
                                    case 'impayee': $cardClass = 'border-left-danger'; break;
                                    case 'partielle': $cardClass = 'border-left-warning'; break;
                                    case 'annulee': $cardClass = 'border-left-dark'; break;
                                }
                                ?>
                                <div class="card <?= $cardClass ?> shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                    <?= ucfirst($statut) ?></div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['total'] ?> factures</div>
                                                <div class="small text-gray-600"><?= number_format($data['montant'], 2, ',', ' ') ?> fcfa</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center">Aucune donnée disponible.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Évolution du CA -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Évolution du chiffre d'affaires</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="revenueChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-primary"></i> CA Mensuel
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Paiements reçus
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page level custom scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique d'évolution du CA
    var ctx = document.getElementById("revenueChart");
    var myLineChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chartData['labels'] ?? []) ?>,
            datasets: [{
                label: "CA",
                lineTension: 0.3,
                backgroundColor: "rgba(78, 115, 223, 0.05)",
                borderColor: "rgba(78, 115, 223, 1)",
                pointRadius: 3,
                pointBackgroundColor: "rgba(78, 115, 223, 1)",
                pointBorderColor: "rgba(78, 115, 223, 1)",
                pointHoverRadius: 3,
                pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                pointHitRadius: 10,
                pointBorderWidth: 2,
                data: <?= json_encode($chartData['revenue'] ?? []) ?>,
            },
            {
                label: "Paiements",
                lineTension: 0.3,
                backgroundColor: "rgba(28, 200, 138, 0.05)",
                borderColor: "rgba(28, 200, 138, 1)",
                pointRadius: 3,
                pointBackgroundColor: "rgba(28, 200, 138, 1)",
                pointBorderColor: "rgba(28, 200, 138, 1)",
                pointHoverRadius: 3,
                pointHoverBackgroundColor: "rgba(28, 200, 138, 1)",
                pointHoverBorderColor: "rgba(28, 200, 138, 1)",
                pointHitRadius: 10,
                pointBorderWidth: 2,
                data: <?= json_encode($chartData['payments'] ?? []) ?>,
            }],
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            scales: {
                xAxes: [{
                    time: {
                        unit: 'date'
                    },
                    gridLines: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        maxTicksLimit: 7
                    }
                }],
                yAxes: [{
                    ticks: {
                        maxTicksLimit: 5,
                        padding: 10,
                        callback: function(value, index, values) {
                            return value + ' fcfa';
                        }
                    },
                    gridLines: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }],
            },
            legend: {
                display: false
            },
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                titleMarginBottom: 10,
                titleFontColor: '#6e707e',
                titleFontSize: 14,
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                intersect: false,
                mode: 'index',
                caretPadding: 10,
                callbacks: {
                    label: function(tooltipItem, chart) {
                        var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                        return datasetLabel + ': ' + tooltipItem.yLabel + ' fcfa';
                    }
                }
            }
        }
    });
});
</script>

<!-- Footer -->
<footer class="sticky-footer bg-white">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>Copyright &copy; Admin-Elect <?= date('Y') ?></span>
        </div>
    </div>
</footer>
<!-- End of Footer -->

</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Prêt à partir?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Sélectionnez "Déconnexion" ci-dessous si vous êtes prêt à mettre fin à votre session actuelle.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Annuler</button>
                <a class="btn btn-primary" href="/logout">Déconnexion</a>
            </div>
        </div>
    </div>
</div>
