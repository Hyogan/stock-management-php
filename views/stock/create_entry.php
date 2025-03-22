<div class="container mt-5">
    <h2>Ajouter une Entrée de Stock</h2>
    <form method="POST" action="/stock/store_entry">
        <div class="mb-3">
            <label for="reference" class="form-label">Référence</label>
            <input type="text" name="reference" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="fournisseur" class="form-label">Fournisseur</label>
            <select name="id_fournisseur" class="form-control" required>
                <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?= $supplier['id'] ?>"><?= htmlspecialchars($supplier['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="date_entree" class="form-label">Date d'entrée</label>
            <input type="date" name="date_entree" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="montant_total" class="form-label">Montant Total</label>
            <input type="number" name="montant_total" class="form-control" step="0.01" required>
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="/stock" class="btn btn-secondary">Annuler</a>
    </form>
</div>
