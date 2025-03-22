<div class="container mt-5">
    <h2>Ajouter une Sortie de Stock</h2>
    <form method="POST" action="/stock/store_exit">
        <div class="mb-3">
            <label for="reference" class="form-label">Référence</label>
            <input type="text" name="reference" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="type_sortie" class="form-label">Type de Sortie</label>
            <select name="type_sortie" class="form-control" required>
                <option value="vente">Vente</option>
                <option value="perte">Perte</option>
                <option value="transfert">Transfert</option>
                <option value="autre">Autre</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="date_sortie" class="form-label">Date de sortie</label>
            <input type="date" name="date_sortie" class="form-control" required>
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
