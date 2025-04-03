<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Nouvelle Commande</h6>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $error ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <form action="<?= APP_URL ?>/orders/store" method="POST" id="orderForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Informations Client</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="client_id">Client existant</label>
                                    <select class="form-control" id="client_id" name="client_id">
                                        <option value="">Sélectionner un client existant</option>
                                        <?php foreach ($clients as $client): ?>
                                            <option value="<?= $client['id'] ?>" <?= (isset($_POST['client_id']) && $_POST['client_id'] == $client['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($client['nom'] . ' ' . $client['prenom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">Ou remplissez les informations ci-dessous pour un nouveau client</small>
                                </div>

                                <div class="form-group">
                                    <label for="client_nom">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="client_nom" name="client_nom" value="<?= isset($_POST['client_nom']) ? htmlspecialchars($_POST['client_nom']) : '' ?>">
                                </div>

                                <div class="form-group">
                                    <label for="client_prenom">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="client_prenom" name="client_prenom" value="<?= isset($_POST['client_prenom']) ? htmlspecialchars($_POST['client_prenom']) : '' ?>">
                                </div>

                                <div class="form-group">
                                    <label for="client_telephone">Téléphone <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="client_telephone" name="client_telephone" value="<?= isset($_POST['client_telephone']) ? htmlspecialchars($_POST['client_telephone']) : '' ?>">
                                </div>

                                <div class="form-group">
                                    <label for="client_email">Email</label>
                                    <input type="email" class="form-control" id="client_email" name="client_email" value="<?= isset($_POST['client_email']) ? htmlspecialchars($_POST['client_email']) : '' ?>">
                                </div>

                                <div class="form-group">
                                    <label for="client_adresse">Adresse</label>
                                    <textarea class="form-control" id="client_adresse" name="client_adresse" rows="3"><?= isset($_POST['client_adresse']) ? htmlspecialchars($_POST['client_adresse']) : '' ?></textarea>
                                </div>
                            </div>-
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Détails de la Commande</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="date_livraison">Date de livraison souhaitée</label>
                                    <input type="date" class="form-control" id="date_livraison" name="date_livraison" value="<?= isset($_POST['date_livraison']) ? htmlspecialchars($_POST['date_livraison']) : '' ?>">
                                </div>

                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"><?= isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : '' ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                    <tr class="no-products-row">
                                        <td colspan="5" class="text-center">Aucun produit ajouté</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Total</strong></td>
                                        <td colspan="2">
                                            <span id="orderTotal">0.00</span> fcfa
                                            <input type="hidden" name="montant_total" id="montantTotal" value="0">
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" id="submitOrder">Enregistrer la commande</button>
                    <a href="<?= APP_URL ?>/orders" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour sélectionner un produit -->
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
    document.addEventListener('DOMContentLoaded', function() {
        // Variables
        let productRowCount = 0;
        const productsList = document.getElementById('productsList');
        const noProductsRow = document.querySelector('.no-products-row');
        const orderTotal = document.getElementById('orderTotal');
        const montantTotal = document.getElementById('montantTotal');
        
        // Ajouter un produit
        document.getElementById('addProductBtn').addEventListener('click', function() {
            $('#productModal').modal('show');
        });
        
        // Sélectionner un produit depuis le modal
        document.querySelectorAll('.select-product').forEach(button => {
            button.addEventListener('click', function() {
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
            // Cacher la ligne "aucun produit"
            if (noProductsRow) {
                noProductsRow.style.display = 'none';
            }
            
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
            
            quantityInput.addEventListener('change', function() {
                updateProductTotal(row);
                updateOrderTotal();
            });
            
            priceInput.addEventListener('change', function() {
                updateProductTotal(row);
                updateOrderTotal();
            });
            
            removeButton.addEventListener('click', function() {
                row.remove();
                updateOrderTotal();
                
                // Afficher la ligne "aucun produit" si plus aucun produit
                if (productsList.querySelectorAll('tr:not(.no-products-row)').length === 0) {
                    noProductsRow.style.display = '';
                }
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
            document.querySelectorAll('#productsList tr:not(.no-products-row)').forEach(row => {
                const quantity = parseInt(row.querySelector('.product-quantity').value) || 0;
                const price = parseFloat(row.querySelector('.product-price').value) || 0;
                total += quantity * price;
            });
            
            orderTotal.textContent = total.toFixed(2);
            montantTotal.value = total.toFixed(2);
        }
        
        // Recherche de produits dans le modal
        document.getElementById('searchProductBtn').addEventListener('click', searchProducts);
        document.getElementById('searchProduct').addEventListener('keyup', function(e) {
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
        
        // Gestion du client existant vs nouveau client
        document.getElementById('client_id').addEventListener('change', function() {
            const clientId = this.value;
            const clientFields = ['client_nom', 'client_prenom', 'client_telephone', 'client_email', 'client_adresse'];
            
            if (clientId) {
                // Client existant sélectionné, désactiver les champs
                clientFields.forEach(field => {
                    document.getElementById(field).disabled = true;
                    document.getElementById(field).value = '';
                });
            } else {
                // Nouveau client, activer les champs
                clientFields.forEach(field => {
                    document.getElementById(field).disabled = false;
                });
            }
        });
        
        // Validation du formulaire avant soumission
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            const productRows = document.querySelectorAll('#productsList tr:not(.no-products-row)');
            
            if (productRows.length === 0) {
                e.preventDefault();
                alert('Veuillez ajouter au moins un produit à la commande.');
                return false;
            }
            
            const clientId = document.getElementById('client_id').value;
            if (!clientId) {
                // Vérifier les champs obligatoires pour un nouveau client
                const requiredFields = ['client_nom', 'client_prenom', 'client_telephone'];
                let missingFields = false;
                
                requiredFields.forEach(field => {
                    if (!document.getElementById(field).value.trim()) {
                        document.getElementById(field).classList.add('is-invalid');
                        missingFields = true;
                    } else {
                        document.getElementById(field).classList.remove('is-invalid');
                    }
                });
                
                if (missingFields) {
                    e.preventDefault();
                    alert('Veuillez remplir tous les champs obligatoires pour le client.');
                    return false;
                }
            }
            
            return true;
        });
    });
</script>
