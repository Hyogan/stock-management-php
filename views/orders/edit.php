<div class="container mt-5">
    <h2>Modifier la Commande</h2>
    <form method="POST" action="/orders/update/<?= htmlspecialchars($order['id']) ?>" id="orderForm">
        <input type="hidden" name="id" value="<?= htmlspecialchars($order['id']) ?>">
        <div class="mb-3">
            <label for="client" class="form-label">Client</label>
            <select name="client_id" class="form-control" required>
                <?php foreach ($clients as $client): ?>
                    <option value="<?= $client['id'] ?>" <?= ($client['id'] == $order['client_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($client['nom']) ?> <?= htmlspecialchars($client['prenom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="date_livraison" class="form-label">Date de livraison souhaitée</label>
            <input type="date" class="form-control" id="date_livraison" name="date_livraison" value="<?= isset($order['date_livraison_prevue']) ? htmlspecialchars($order['date_livraison_prevue']) : '' ?>">
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($order['notes']) ?></textarea>
        </div>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Produits</h6>
                <button type="button" class="btn btn-primary btn-sm" id="addProductBtn">
                    <i class="fas fa-plus-circle"></i> Ajouter un produit
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="productsTable">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th width="120">Quantité</th>
                                <th width="150">Prix unitaire</th>
                                <th width="150">Total</th>
                                <th width="50">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="productsList">
                            <?php foreach ($orderDetails as $detail): ?>
                                <tr data-product-id="<?= $detail['id_produit'] ?>" data-price="<?= $detail['prix_unitaire'] ?>">
                                    <td>
                                        <?= htmlspecialchars($detail['reference']) ?>
                                        <input type="hidden" name="products[<?= $detail['id_produit'] ?>][id]" value="<?= $detail['id_produit'] ?>">
                                        <input type="hidden" name="products[<?= $detail['id_produit'] ?>][reference]" value="<?= htmlspecialchars($detail['reference']) ?>">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control product-quantity" name="products[<?= $detail['id_produit'] ?>][quantite]" min="1" value="<?= $detail['quantite'] ?>" required>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control product-price" name="products[<?= $detail['id_produit'] ?>][prix]" step="0.01" min="0" value="<?= $detail['prix_unitaire'] ?>" required>
                                    </td>
                                    <td class="product-total"><?= formatPrice($detail['montant_total']) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-product">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-right"><strong>Total</strong></td>
                                <td colspan="2">
                                    <span id="orderTotal"><?= formatPrice($order['montant_total']) ?></span>
                                    <input type="hidden" name="montant_total" id="montantTotal" value="<?= $order['montant_total'] ?>">
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-warning">Mettre à jour</button>
        <a href="/orders" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">Sélectionner un produit</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Rechercher un produit..." id="searchProduct">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="searchProductBtn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="productsModalTable">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Catégorie</th>
                                <th>Prix</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="productsModalList">
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['reference']) ?></td>
                                    <td>
                                        <?php
                                        $categoryName = "Non catégorisé";
                                        foreach ($categories as $category) {
                                            if ($category['id'] == $product['categorie_id']) {
                                                $categoryName = htmlspecialchars($category['nom']);
                                                break;
                                            }
                                        }
                                        echo $product['categorie_nom'];
                                        ?>
                                    </td>
                                    <td><?= formatPrice($product['prix_vente']) ?></td>
                                    <td>
                                        <span class="badge <?= $product['quantite_stock'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $product['quantite_stock'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm select-product"
                                                data-id="<?= $product['id'] ?>"
                                                data-name="<?= htmlspecialchars($product['reference']) ?>"
                                                data-price="<?= $product['prix_vente'] ?>"
                                                data-stock="<?= $product['quantite_stock'] ?>"
                                            <?= $product['quantite_stock'] <= 0 ? 'disabled' : '' ?>>
                                            Sélectionner
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Variables
        let productRowCount = <?= count($orderDetails) ?>;
        const productsList = document.getElementById('productsList');
        const orderTotal = document.getElementById('orderTotal');
        const montantTotal = document.getElementById('montantTotal');

        // Ajouter un produit
        document.getElementById('addProductBtn').addEventListener('click', function () {
            $('#productModal').modal('show');
        });

        // Sélectionner un produit depuis le modal
        document.querySelectorAll('.select-product').forEach(button => {
            button.addEventListener('click', function () {
                const productId = this.getAttribute('data-id');
                const productName = this.getAttribute('data-name');
                const productPrice = parseFloat(this.getAttribute('data-price'));
                const productStock = parseInt(this.getAttribute('data-stock'));

                addProductRow(productId, productName, productPrice, productStock);
                $('#productModal').modal('hide');
            });
        });

        // Fonction pour ajouter une ligne de produit
        function addProductRow(productId, productName, productPrice, productStock) {
            const row = document.createElement('tr');
            row.setAttribute('data-product-id', productId);
            row.setAttribute('data-price', productPrice);

            row.innerHTML = `
                <td>
                    ${productName}
                    <input type="hidden" name="products[${productRowCount}][id]" value="${productId}">
                    <input type="hidden" name="products[${productRowCount}][reference]" value="${productName}">
                </td>
                <td>
                    <input type="number" class="form-control product-quantity" name="products[${productRowCount}][quantite]" min="1" max="${productStock}" value="1" required>
                </td>
                <td>
                    <input type="number" class="form-control product-price" name="products[${productRowCount}][prix]" step="0.01" min="0" value="${productPrice.toFixed(2)}" required>
                </td>
                <td class="product-total">${productPrice.toFixed(2)} fcfa</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-product">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;

            productsList.appendChild(row);

            // Ajouter les écouteurs d'événements pour cette ligne
            const quantityInput = row.querySelector('.product-quantity');
            const priceInput = row.querySelector('.product-price');
            const removeButton = row.querySelector('.remove-product');

            quantityInput.addEventListener('change', function () {
                updateProductTotal(row);
                updateOrderTotal();
            });

            priceInput.addEventListener('change', function () {
                updateProductTotal(row);
                updateOrderTotal();
            });

            removeButton.addEventListener('click', function () {
                row.remove();
                updateOrderTotal();
            });

            productRowCount++;
            updateProductTotal(row);
            updateOrderTotal();
        }

        // Mettre à jour le total d'une ligne de produit
        function updateProductTotal(row) {
            const quantity = parseInt(row.querySelector('.product-quantity').value) || 0;
            const price = parseFloat(row.querySelector('.product-price').value) || 0;
            const totalCell = row.querySelector('.product-total');

            const total = quantity * price;
            totalCell.textContent = total.toFixed(2) + ' fcfa';
        }

        // Mettre à jour le total de la commande
        function updateOrderTotal() {
            let total = 0;
            document.querySelectorAll('#productsList tr').forEach(row => {
                const quantity = parseInt(row.querySelector('.product-quantity').value) || 0;
                const price = parseFloat(row.querySelector('.product-price').value) || 0;
                total += quantity * price;
            });

            orderTotal.textContent = total.toFixed(2);
            montantTotal.value = total.toFixed(2);
        }

        // Recherche de produits dans le modal
        document.getElementById('searchProductBtn').addEventListener('click', searchProducts);
        document.getElementById('searchProduct').addEventListener('keyup', function (e) {
            if (e.key === 'Enter') {
                searchProducts();
            }
        });

        function searchProducts() {
            const searchTerm = document.getElementById('searchProduct').value.toLowerCase();
            const rows = document.querySelectorAll('#productsModalList tr');

            rows.forEach(row => {
                const productName = row.querySelector('td:first-child').textContent.toLowerCase();
                const categoryName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();

                if (productName.includes(searchTerm) || categoryName.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Validation du formulaire avant soumission
        document.getElementById('orderForm').addEventListener('submit', function (e) {
            const productRows = document.querySelectorAll('#productsList tr');

            if (productRows.length === 0) {
                e.preventDefault();
                alert('Veuillez ajouter au moins un produit à la commande.');
                return false;
            }

            return true;
        });
    });
</script>
