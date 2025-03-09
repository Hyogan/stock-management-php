<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/dashboard">
        <div class="sidebar-brand-icon">
            <i class="fas fa-bolt"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Admin-Elect</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
        <a class="nav-link" href="/dashboard">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Tableau de bord</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <?php if (in_array($_SESSION['user_role'] ?? '', ['admin', 'secretary'])): ?>
    <!-- Heading -->
    <div class="sidebar-heading">
        Gestion commerciale
    </div>

    <!-- Nav Item - Clients -->
    <li class="nav-item <?= ($currentPage ?? '') === 'clients' ? 'active' : '' ?>">
        <a class="nav-link" href="/clients">
            <i class="fas fa-fw fa-users"></i>
            <span>Clients</span>
        </a>
    </li>

    <!-- Nav Item - Devis -->
    <li class="nav-item <?= ($currentPage ?? '') === 'quotes' ? 'active' : '' ?>">
        <a class="nav-link" href="/quotes">
            <i class="fas fa-fw fa-file-invoice"></i>
            <span>Devis</span>
        </a>
    </li>

    <!-- Nav Item - Commandes -->
    <li class="nav-item <?= ($currentPage ?? '') === 'orders' ? 'active' : '' ?>">
        <a class="nav-link" href="/orders">
            <i class="fas fa-fw fa-shopping-cart"></i>
            <span>Commandes</span>
        </a>
    </li>

    <!-- Nav Item - Factures -->
    <li class="nav-item <?= ($currentPage ?? '') === 'invoices' ? 'active' : '' ?>">
        <a class="nav-link" href="/invoices">
            <i class="fas fa-fw fa-file-invoice-dollar"></i>
            <span>Factures</span>
        </a>
    </li>

    <!-- Nav Item - Paiements -->
    <li class="nav-item <?= ($currentPage ?? '') === 'payments' ? 'active' : '' ?>">
        <a class="nav-link" href="/payments">
            <i class="fas fa-fw fa-money-bill-wave"></i>
            <span>Paiements</span>
        </a>
    </li>
    <?php endif; ?>

    <?php if (in_array($_SESSION['user_role'] ?? '', ['admin', 'storekeeper'])): ?>
    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Gestion des stocks
    </div>

    <!-- Nav Item - Produits -->
    <li class="nav-item <?= ($currentPage ?? '') === 'products' ? 'active' : '' ?>">
        <a class="nav-link" href="/products">
            <i class="fas fa-fw fa-box"></i>
            <span>Produits</span>
        </a>
    </li>

    <!-- Nav Item - Catégories -->
    <li class="nav-item <?= ($currentPage ?? '') === 'categories' ? 'active' : '' ?>">
        <a class="nav-link" href="/categories">
            <i class="fas fa-fw fa-tags"></i>
            <span>Catégories</span>
        </a>
    </li>

    <!-- Nav Item - Fournisseurs -->
    <li class="nav-item <?= ($currentPage ?? '') === 'suppliers' ? 'active' : '' ?>">
        <a class="nav-link" href="/suppliers">
            <i class="fas fa-fw fa-truck"></i>
            <span>Fournisseurs</span>
        </a>
    </li>

    <!-- Nav Item - Opérations de stock -->
    <li class="nav-item <?= ($currentPage ?? '') === 'operations' ? 'active' : '' ?>">
        <a class="nav-link" href="/operations">
            <i class="fas fa-fw fa-exchange-alt"></i>
            <span>Opérations de stock</span>
        </a>
    </li>
    <?php endif; ?>

    <?php if ($_SESSION['user_role'] ?? '' === 'admin'): ?>
    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Administration
    </div>

    <!-- Nav Item - Utilisateurs -->
    <li class="nav-item <?= ($currentPage ?? '') === 'users' ? 'active' : '' ?>">
        <a class="nav-link" href="/users">
            <i class="fas fa-fw fa-user-cog"></i>
            <span>Utilisateurs</span>
        </a>
    </li>

    <!-- Nav Item - Paramètres -->
    <li class="nav-item <?= ($currentPage ?? '') === 'settings' ? 'active' : '' ?>">
        <a class="nav-link" href="/settings">
            <i class="fas fa-fw fa-cogs"></i>
            <span>Paramètres</span>
        </a>
    </li>

    <!-- Nav Item - Statistiques -->
    <li class="nav-item <?= ($currentPage ?? '') === 'statistics' ? 'active' : '' ?>">
        <a class="nav-link" href="/dashboard/statistics">
            <i class="fas fa-fw fa-chart-line"></i>
            <span>Statistiques</span>
        </a>
    </li>
    <?php endif; ?>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->
