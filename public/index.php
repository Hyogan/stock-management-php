<?php
/**
 * Définition des routes de l'application
 */

// Tableau des routes
$routes2 = [
    '/' => ['HomeController', 'index'],
    'auth/login' => ['AuthController', 'loginForm'],
    'auth/l/login/process' => ['AuthController', 'login'],
    '/logout' => ['AuthController', 'logout'],
    '/dashboard' => ['DashboardController', 'index'],
    
    // Routes pour les clients
    '/clients' => ['ClientController', 'index'],
    '/clients/create' => ['ClientController', 'create'],
    '/clients/store' => ['ClientController', 'store'],
    '/clients/show' => ['ClientController', 'show'],
    '/clients/edit' => ['ClientController', 'edit'],
    '/clients/update' => ['ClientController', 'update'],
    '/clients/delete' => ['ClientController', 'delete'],
    
    // Routes pour les produits
    '/products' => ['ProductController', 'index'],
    '/products/create' => ['ProductController', 'create'],
    '/products/store' => ['ProductController', 'store'],
    '/products/show' => ['ProductController', 'show'],
    '/products/edit' => ['ProductController', 'edit'],
    '/products/update' => ['ProductController', 'update'],
    '/products/delete' => ['ProductController', 'delete'],
    '/products/stock' => ['ProductController', 'stockManagement'],
    '/products/add-stock' => ['ProductController', 'addStock'],
    '/products/remove-stock' => ['ProductController', 'removeStock'],
    
    // Routes pour les commandes
    '/orders' => ['OrderController', 'index'],
    '/orders/create' => ['OrderController', 'create'],
    '/orders/store' => ['OrderController', 'store'],
    '/orders/show' => ['OrderController', 'show'],
    '/orders/edit' => ['OrderController', 'edit'],
    '/orders/update' => ['OrderController', 'update'],
    '/orders/delete' => ['OrderController', 'delete'],
    '/orders/change-status' => ['OrderController', 'changeStatus'],
    
    // Routes pour les utilisateurs
    '/users' => ['UserController', 'index'],
    '/users/create' => ['UserController', 'create'],
    '/users/store' => ['UserController', 'store'],
    '/users/show' => ['UserController', 'show'],
    '/users/edit' => ['UserController', 'edit'],
    '/users/update' => ['UserController', 'update'],
    '/users/delete' => ['UserController', 'delete'],
    
    // Routes pour le profil
    '/profile' => ['ProfileController', 'index'],
    '/profile/update' => ['ProfileController', 'update'],
    '/profile/change-password' => ['ProfileController', 'changePassword'],
    
    // Routes pour les rapports
    '/reports' => ['ReportController', 'index'],
    '/reports/sales' => ['ReportController', 'sales'],
    '/reports/stock' => ['ReportController', 'stock'],
    '/reports/export' => ['ReportController', 'export'],
];

// Tableau des routes
$routes = [];

// Routes d'authentification
$routes['/'] = ['HomeController', 'index'];
$routes['/auth/login'] = ['AuthController', 'login'];
$routes['/auth/logout'] = ['AuthController', 'logout'];
$routes['/auth/register'] = ['AuthController', 'register'];
$routes['/auth/forgot-password'] = ['AuthController', 'forgotPassword'];
$routes['/auth/reset-password'] = ['AuthController', 'resetPassword'];

// Routes du tableau de bord
$routes['/dashboard'] = ['DashboardController', 'index'];

// Routes des produits
$routes['/products'] = ['ProductController', 'index'];
$routes['/products/create'] = ['ProductController', 'create'];
$routes['/products/store'] = ['ProductController', 'store'];
$routes['/products/edit'] = ['ProductController', 'edit'];
$routes['/products/update'] = ['ProductController', 'update'];
$routes['/products/delete'] = ['ProductController', 'delete'];
$routes['/products/show'] = ['ProductController', 'show'];

// Routes des catégories
$routes['/categories'] = ['CategoryController', 'index'];
$routes['/categories/create'] = ['CategoryController', 'create'];
$routes['/categories/store'] = ['CategoryController', 'store'];
$routes['/categories/edit'] = ['CategoryController', 'edit'];
$routes['/categories/update'] = ['CategoryController', 'update'];
$routes['/categories/delete'] = ['CategoryController', 'delete'];

// Routes des fournisseurs
$routes['/suppliers'] = ['SupplierController', 'index'];
$routes['/suppliers/create'] = ['SupplierController', 'create'];
$routes['/suppliers/store'] = ['SupplierController', 'store'];
$routes['/suppliers/edit'] = ['SupplierController', 'edit'];
$routes['/suppliers/update'] = ['SupplierController', 'update'];
$routes['/suppliers/delete'] = ['SupplierController', 'delete'];
$routes['/suppliers/show'] = ['SupplierController', 'show'];

// Routes des clients
$routes['/clients'] = ['ClientController', 'index'];
$routes['/clients/create'] = ['ClientController', 'create'];
$routes['/clients/store'] = ['ClientController', 'store'];
$routes['/clients/edit'] = ['ClientController', 'edit'];
$routes['/clients/update'] = ['ClientController', 'update'];
$routes['/clients/delete'] = ['ClientController', 'delete'];
$routes['/clients/show'] = ['ClientController', 'show'];

// Routes des commandes
$routes['/orders'] = ['OrderController', 'index'];
$routes['/orders/create'] = ['OrderController', 'create'];
$routes['/orders/store'] = ['OrderController', 'store'];
$routes['/orders/edit'] = ['OrderController', 'edit'];
$routes['/orders/update'] = ['OrderController', 'update'];
$routes['/orders/delete'] = ['OrderController', 'delete'];
$routes['/orders/show'] = ['OrderController', 'show'];

// Routes des entrées de stock
$routes['/stock-entries'] = ['StockEntryController', 'index'];
$routes['/stock-entries/create'] = ['StockEntryController', 'create'];
$routes['/stock-entries/store'] = ['StockEntryController', 'store'];
$routes['/stock-entries/show'] = ['StockEntryController', 'show'];

// Routes des sorties de stock
$routes['/stock-exits'] = ['StockExitController', 'index'];
$routes['/stock-exits/create'] = ['StockExitController', 'create'];
$routes['/stock-exits/store'] = ['StockExitController', 'store'];
$routes['/stock-exits/show'] = ['StockExitController', 'show'];

// Routes des paiements
$routes['/payments'] = ['PaymentController', 'index'];
$routes['/payments/create'] = ['PaymentController', 'create'];
$routes['/payments/store'] = ['PaymentController', 'store'];
$routes['/payments/show'] = ['PaymentController', 'show'];

// Routes des utilisateurs (admin seulement)
$routes['/users'] = ['UserController', 'index'];
$routes['/users/create'] = ['UserController', 'create'];
$routes['/users/store'] = ['UserController', 'store'];
$routes['/users/edit'] = ['UserController', 'edit'];
$routes['/users/update'] = ['UserController', 'update'];
$routes['/users/delete'] = ['UserController', 'delete'];
$routes['/users/show'] = ['UserController', 'show'];

// Routes des rapports
$routes['/reports'] = ['ReportController', 'index'];
$routes['/reports/inventory'] = ['ReportController', 'inventory'];
$routes['/reports/sales'] = ['ReportController', 'sales'];
$routes['/reports/purchases'] = ['ReportController', 'purchases'];
$routes['/reports/payments'] = ['ReportController', 'payments'];

// Routes des paramètres
$routes['/settings'] = ['SettingController', 'index'];
$routes['/settings/update'] = ['SettingController', 'update'];

// Routes des logs d'activité
$routes['/activity-logs'] = ['ActivityLogController', 'index'];

// Routes des sauvegardes
$routes['/backups'] = ['BackupController', 'index'];
$routes['/backups/create'] = ['BackupController', 'create'];
$routes['/backups/download'] = ['BackupController', 'download'];
$routes['/backups/restore'] = ['BackupController', 'restore'];
