<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>" href="<?= APP_URL ?>/dashboard">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Tableau de bord
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'products' ? 'active' : '' ?>" href="<?= APP_URL ?>/products">
                    <i class="fas fa-box me-2"></i>
                    Produits
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'categories' ? 'active' : '' ?>" href="<?= APP_URL ?>/categories">
                    <i class="fas fa-tags me-2"></i>
                    Catégories
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'suppliers' ? 'active' : '' ?>" href="<?= APP_URL ?>/suppliers">
                    <i class="fas fa-truck me-2"></i>
                    Fournisseurs
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'clients' ? 'active' : '' ?>" href="<?= APP_URL ?>/clients">
                    <i class="fas fa-users me-2"></i>
                    Clients
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'orders' ? 'active' : '' ?>" href="<?= APP_URL ?>/orders">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Commandes
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'stock-entries' ? 'active' : '' ?>" href="<?= APP_URL ?>/stock-entries">
                    <i class="fas fa-arrow-circle-down me-2"></i>
                    Entrées de stock
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'stock-exits' ? 'active' : '' ?>" href="<?= APP_URL ?>/stock-exits">
                    <i class="fas fa-arrow-circle-up me-2"></i>
                    Sorties de stock
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'payments' ? 'active' : '' ?>" href="<?= APP_URL ?>/payments">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    Paiements
                </a>
            </li>
            
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'users' ? 'active' : '' ?>" href="<?= APP_URL ?>/users">
                    <i class="fas fa-user-cog me-2"></i>
                    Utilisateurs
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'reports' ? 'active' : '' ?>" href="<?= APP_URL ?>/reports">
                    <i class="fas fa-chart-bar me-2"></i>
                    Rapports
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'settings' ? 'active' : '' ?>" href="<?= APP_URL ?>/settings">
                    <i class="fas fa-cog me-2"></i>
                    Paramètres
                </a>
            </li>
            <?php endif; ?>
        </ul>
        
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Administration</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'activity-logs' ? 'active' : '' ?>" href="<?= APP_URL ?>/activity-logs">
                    <i class="fas fa-history me-2"></i>
                    Logs d'activité
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'backups' ? 'active' : '' ?>" href="<?= APP_URL ?>/backups">
                    <i class="fas fa-database me-2"></i>
                    Sauvegardes
                </a>
            </li>
        </ul>
        <?php endif; ?>
    </div>
</nav>
