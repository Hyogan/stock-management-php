<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Arial', sans-serif; /* Modern font */
    }

    .container {
        max-width: 1200px; /* Wider container for larger screens */
    }

    .card {
        border: 1px solid #dee2e6; /* Light border */
        border-radius: 8px; /* Rounded corners */
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); /* Slightly stronger shadow */
        transition: transform 0.3s ease, box-shadow 0.3s ease; /* Smooth transitions */
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12); /* More pronounced shadow on hover */
    }

    .card-header {
        background-color: #007bff;
        color: white;
        font-weight: 600; /* Semi-bold font */
        padding: 1rem 1.5rem; /* Increased padding */
        border-bottom: 1px solid rgba(0, 0, 0, 0.1); /* Subtle border */
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    .card-body {
        padding: 1.5rem; /* Increased padding */
    }

    .list-group-item {
        border: none;
        padding: 0.75rem 1.25rem; /* Adjusted padding */
        transition: background-color 0.3s ease;
    }

    .list-group-item:hover {
        background-color: #f0f0f0;
    }

    .table {
        background-color: white;
        border-collapse: collapse; /* Collapse borders for cleaner look */
    }

    .table th, .table td {
        vertical-align: middle;
        padding: 0.75rem 1.25rem; /* Adjusted padding */
        text-align: left; /* Align text left for better readability */
        border-bottom: 1px solid #dee2e6; /* Light border between rows */
    }

    .table th {
        font-weight: 600; /* Semi-bold headers */
        background-color: #f8f9fa; /* Light background for headers */
    }

    .text-center {
        text-align: center;
    }

    .mt-2 {
        margin-top: 1rem;
    }

    .mb-4 {
        margin-bottom: 2rem;
    }

    .fs-1 {
        font-size: 2.5rem; /* Larger icons */
    }
</style>

<div class="container mt-5">
    <h1 class="mb-4"><?php echo $data['pageTitle']; ?></h1>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">Produits Totaux</div>
                <div class="card-body text-center">
                    <i class="bi bi-box-seam fs-1 text-primary"></i>
                    <h2 class="mt-2"><?php echo $data['totalProducts']; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">Valeur Totale du Stock</div>
                <div class="card-body text-center">
                    <i class="bi bi-cash-coin fs-1 text-success"></i>
                    <h2 class="mt-2"><?php echo number_format($data['totalValue'], 2); ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">Rupture de Stock</div>
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle fs-1 text-danger"></i>
                    <h2 class="mt-2"><?php echo $data['outOfStocksCount']; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">Stock Faible</div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($data['lowStock'] as $product): ?>
                        <li class="list-group-item">
                            <?php echo $product['designation']; ?> 
                            ( stock : <?php echo $product['quantite_stock']; ?>)
                            ( stock alerte : <?php echo $product['quantite_alerte']; ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">Produits en Rupture de Stock</div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($data['outOfStocks'] as $product): ?>
                        <li class="list-group-item">
                            <?php echo $product['designation']; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">Produits les Plus Vendus</div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Quantité Vendue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['mostSoldProducts'] as $product): ?>
                                <tr>
                                    <td><?php echo $product['designation']; ?></td>
                                    <td><?php echo $product['total_sold']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">Produits les Moins Vendus</div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Quantité Vendue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['leastSoldProducts'] as $product): ?>
                                <tr>
                                    <td><?php echo $product['designation']; ?></td>
                                    <td><?php echo $product['total_vendu']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">Produits les Plus Rentables</div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Profit Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['mostProfitableProducts'] as $product): ?>
                                <tr>
                                    <td><?php echo $product['designation']; ?></td>
                                    <td><?php echo number_format($product['profit_total'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
