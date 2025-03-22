<div class="container mt-4">
    <h2>Ajouter une Entrée de Stock</h2>

    <form action="/entries/store" method="POST">
        <div class="mb-3">
            <label for="reference" class="form-label">Référence</label>
            <input type="text" class="form-control" id="reference" name="reference" required>
        </div>

        <div class="mb-3">
            <label for="date_entree" class="form-label">Date d'Entrée</label>
            <input type="datetime-local" class="form-control" id="date_entree" name="date_entree" required>
        </div>

        <div class="mb-3">
            <label for="id_fournisseur" class="form-label">Fournisseur (Optionnel)</label>
            <select class="form-select" id="id_fournisseur" name="id_fournisseur">
                <option value="">Sélectionner un fournisseur</option>
                <?php foreach ($fournisseurs as $fournisseur): ?>
                    <option value="<?= $fournisseur['id'] ?>"><?= $fournisseur['nom'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="id_client" class="form-label">Client (Optionnel)</label>
            <select class="form-select" id="id_client" name="id_client">
                <option value="">Sélectionner un client</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?= $client['id'] ?>"><?= $client['nom'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="montant_total" class="form-label">Montant Total</label>
            <input type="number" step="0.01" class="form-control" id="montant_total" name="montant_total" required>
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes"></textarea>
        </div>

        <div class="mb-3">
            <label for="id_livraison" class="form-label">ID Livraison (Optionnel)</label>
            <input type="number" class="form-control" id="id_livraison" name="id_livraison">
        </div>

        <div class="mb-3">
            <label for="products" class="form-label">Produits</label>
            <div id="product-container">
                <div class="row product-row">
                    <div class="col-md-4">
                        <select class="form-select product-select" name="details[0][id_produit]" required>
                            <option value="">Sélectionner un produit</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id'] ?>"><?= $product['designation'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" class="form-control quantity-input" name="details[0][quantite]" placeholder="Quantité" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" class="form-control price-input" name="details[0][prix_unitaire]" placeholder="Prix Unitaire" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-product">Supprimer</button>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-primary mt-2" id="add-product">Ajouter un produit</button>
        </div>

        <button type="submit" class="btn btn-success">Ajouter l'Entrée</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let productCount = 1;
        document.getElementById('add-product').addEventListener('click', function() {
            const productContainer = document.getElementById('product-container');
            const newRow = document.createElement('div');
            newRow.classList.add('row', 'product-row');
            newRow.innerHTML = `
                <div class="col-md-4">
                    <select class="form-select product-select" name="details[${productCount}][id_produit]" required>
                        <option value="">Sélectionner un produit</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>"><?= $product['designation'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" class="form-control quantity-input" name="details[${productCount}][quantite]" placeholder="Quantité" required>
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.01" class="form-control price-input" name="details[${productCount}][prix_unitaire]" placeholder="Prix Unitaire" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-product">Supprimer</button>
                </div>
            `;
            productContainer.appendChild(newRow);
            productCount++;
        });

        document.getElementById('product-container').addEventListener('click', function(event) {
            if (event.target.classList.contains('remove-product')) {
                event.target.closest('.product-row').remove();
            }
        });
    });
</script>
