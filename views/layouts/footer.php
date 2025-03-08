<footer class="footer mt-auto py-3 bg-light">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <span class="text-muted">&copy; <?= date('Y') ?> Admin Elect. Tous droits réservés.</span>
            </div>
            <div class="col-md-6 text-end">
                <span class="text-muted">Version 1.0.0</span>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Custom JS -->
<script src="<?= APP_URL ?>/assets/js/main.js"></script>

<?php if (isset($additionalJs)): ?>
    <?php foreach ($additionalJs as $js): ?>
        <script src="<?= APP_URL ?>/assets/js/<?= $js ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
