<?php
$pageTitle = 'Tableau de bord';
ob_start();
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="mb-3"><i class="fas fa-tachometer-alt"></i> Tableau de bord</h1>
        <p class="text-muted">Bienvenue sur votre tableau de bord. Voici un aperçu de vos statistiques.</p>
    </div>
</div>

<div class="row mb-4">
    <!-- Carte de statistiques - Commandes -->
    <div class="col-md-3 mb-4">
        <div class="card border-left-primary shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Commandes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_orders'] ?? 0 ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Carte de statistiques - Revenus -->
    <div class="col-md-3 mb-4">
        <div class="card border-left-success shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Revenus</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total_revenue'] ?? 0, 2, ',', ' ') ?> fcfa</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-euro-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Carte de statistiques - Clients -->
    <div class="col-md-3 mb-4">
        <div class="card border-left-info shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Clients</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_customers'] ?? 0 ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Carte de statistiques - Produits -->
    <div class="col-md-3 mb-4">
        <div class="card border-left-warning shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Produits</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_products'] ?? 0 ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Graphique des commandes par statut -->
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h6 class="m-0 font-weight-bold">Commandes par statut</h6>
            </div>
            <div class="card-body">
                <canvas id="orderStatusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Graphique des revenus mensuels -->
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h6 class="m-0 font-weight-bold">Revenus mensuels</h6>
            </div>
            <div class="card-body">
                <canvas id="monthlyRevenueChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Dernières commandes -->
    <div class="col-md-12 mb-4">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h6 class="m-0 font-weight-bold">Dernières commandes</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Client</th>
                                <th>Date</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($latestOrders)): ?>
                                <?php foreach ($latestOrders as $order): ?>
                                    <tr>
                                        <td><?= $order['id'] ?></td>
                                        <td><?= htmlspecialchars($order['client_name']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                        <td><?= number_format($order['montant'], 2, ',', ' ') ?> fcfa</td>
                                        <td>
                                            <span class="badge bg-<?= $order['statut'] === 'completed' ? 'success' : ($order['statut'] === 'pending' ? 'warning' : 'secondary') ?>">
                                                <?= ucfirst($order['statut']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="/orders/view/<?= $order['id'] ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Aucune commande trouvée</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="/orders" class="btn btn-primary">Voir toutes les commandes</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Données pour le graphique des commandes par statut
    const orderStatusData = {
        labels: <?= json_encode(array_keys($stats['par_statut'] ?? [])) ?>,
        datasets: [{
            label: 'Nombre de commandes',
            data: <?= json_encode(array_map(function($item) { return $item['total']; }, $stats['par_statut'] ?? [])) ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba -->
