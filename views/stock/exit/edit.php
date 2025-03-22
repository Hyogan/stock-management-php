<div class="container mt-4">
    <h2>Modifier une Sortie de Stock</h2>

    <form action="/exits/update/<?= $exit['id']; ?>" method="POST">
        <div class="mb-3">
            <label for="reference" class="form-label">Référence</label>
            <input type="text" class="form-control" id="reference" name="reference" value="<?= htmlspecialchars($exit['reference']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="type_sortie" class="form-label">Type de Sortie</label>
            <select class="form-select" id="type_sortie" name="type_sortie" required>
                <option value="vente" <?= $exit['type_sortie'] == 'vente' ? 'selected' : ''; ?>>Vente</option>
                <option value="perte" <?= $exit['type_sortie'] == 'perte' ? 'selected' : ''; ?>>Perte</option>
                <option value="transfert" <?= $exit['type_sortie'] == 'transfert' ? 'selected' : ''; ?>>Transfert</option>
                <option value="autre" <?= $exit['type_sortie'] == 'autre' ? 'selected' : ''; ?>>Autre</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="date_sortie" class="form-label">Date de Sortie</label>
            <input type="datetime-local" class="form-control" id="date_sortie" name="date_sortie" value="<?= str_replace(' ', 'T', htmlspecialchars($exit['date_sortie'])); ?>" required>
        </div>

        <div class="mb-3">
            <label for="id_commande" class="form-label">ID Commande (Optionnel)</label>
            <input type="number" class="form-control" id="id_commande" name="id_commande" value="<?= htmlspecialchars($exit['id_commande']); ?>">
        </div>

        <div class="mb-3">
            <label for="destination" class="form-label">Destination (Optionnel)</label>
            <input type="text" class="form-control" id="destination" name="destination" value="<?= htmlspecialchars($exit['destination']); ?>">
        </div>

        <div class="mb-3">
            <label for="montant_total" class="form-label">Montant Total</label>
            <input type="number" step="0.01" class="form-control" id="montant_total" name="montant_total" value="<?= htmlspecialchars($exit['montant_total']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes"><?= htmlspecialchars($exit['notes']); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="id_livraison" class="form-label">ID Livraison (Optionnel)</label>
            <input type="number" class="form-control" id="id_livraison" name="id_livraison" value="<?= htmlspecialchars($exit['id_livraison']); ?>">
        </div>

        <div class="mb-3">
            <label for="products" class="form-label">Produits</label>
            <div id="product-container">
                <?php foreach ($exitProducts as $index => $product): ?>
                    <div class="row product-row">
                        <div class="col-md-4">
                            <select class="form-select product-select" name="details[<?= $index; ?>][id_produit]" required>
                                <option value="">Sélectionner un produit</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['id']; ?>" <?= $p['id'] == $product['id_produit'] ? 'selected' : ''; ?>><?= $p['designation']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="number" class="form-control quantity-input" name="details[<?= $index; ?>][quantite]" placeholder="Quantité" value="<?= $product['quantite']; ?>" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" step="0.01" class="form-control price-input" name="details[<?= $index; ?>][prix_unitaire]" placeholder="Prix Unitaire" value="<?= $product['prix_unitaire']; ?>" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger remove-product">Supprimer</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-primary mt-2" id="add-product">Ajouter un produit</button>
        </div>

        <button type="submit" class="btn btn-success">Modifier la Sortie</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let productCount = <?= count($exitProducts); ?>;
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
