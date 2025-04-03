<?php
/**
 * Définition des routes de l'application
 * 
 * Format: $routes['/chemin'] = ['NomDuControleur', 'nomDeLaMethode'];
 */

// Tableau des routes
$routes = [];

// Routes d'authentification
$routes['/'] = ['HomeController', 'index'];
$routes['/auth/login'] = ['AuthController', 'login'];
$routes['/login'] = ['AuthController', 'login'];
$routes['/auth/authenticate'] = ['AuthController', 'authenticate'];
$routes['/auth/logout'] = ['AuthController', 'logout'];
$routes['/auth/register'] = ['AuthController', 'register'];
$routes['/auth/user/store'] = ['AuthController', 'store'];
$routes['/auth/forgot-password'] = ['AuthController', 'forgotPassword'];
$routes['/auth/reset-password'] = ['AuthController', 'resetPassword'];

// Routes du tableau de bord
$routes['/dashboard'] = ['DashboardController', 'index'];

// Routes des produits
$routes['/products'] = ['ProductController', 'index'];
$routes['/products/filter'] = ['ProductController', 'index'];
$routes['/products/create'] = ['ProductController', 'create'];
$routes['/products/store'] = ['ProductController', 'store'];
$routes['/products/edit/{productId}'] = ['ProductController', 'edit'];
$routes['/products/update/{productId}'] = ['ProductController', 'update'];
$routes['/products/delete/{productId}'] = ['ProductController', 'delete'];
$routes['/products/show/{productId}'] = ['ProductController', 'show'];
$routes['/products/exportCsv'] = ['ProductController', 'exportCsv'];
$routes['/products/stats'] = ['ProductController', 'statistics'];
$routes['/products/generateStockReport'] = ['ProductController', 'generateStockReport'];
// Routes des catégories
$routes['/categories'] = ['CategoryController', 'index'];
$routes['/categories/create'] = ['CategoryController', 'create'];
$routes['/categories/store'] = ['CategoryController', 'store'];
$routes['/categories/edit/{id}'] = ['CategoryController', 'edit'];
$routes['/categories/update/{id}'] = ['CategoryController', 'update'];
$routes['/categories/delete/{id}'] = ['CategoryController', 'delete'];
// Routes des fournisseurs
$routes['/suppliers'] = ['SupplierController', 'index'];
$routes['/suppliers/create'] = ['SupplierController', 'create'];
$routes['/suppliers/store'] = ['SupplierController', 'store'];
$routes['/suppliers/change-status/{id}'] = ['SupplierController', 'changeStatus'];
$routes['/suppliers/edit/{id}'] = ['SupplierController', 'edit'];
$routes['/suppliers/update/{id}'] = ['SupplierController', 'update'];
$routes['/suppliers/delete/{id}'] = ['SupplierController', 'delete'];
$routes['/suppliers/show/{id}'] = ['SupplierController', 'show'];
// Routes pour les clients 
$routes['/clients'] = ['ClientController', 'index'];
$routes['/clients/create'] = ['ClientController', 'create'];
$routes['/clients/store'] = ['ClientController', 'store'];
$routes['/clients/edit/{clientId}'] = ['ClientController', 'edit'];
$routes['/clients/update/{clientId}'] = ['ClientController', 'update'];
$routes['/clients/delete/{clientId}'] = ['ClientController', 'delete'];
$routes['/clients/show/{clientId}'] = ['ClientController', 'show'];
// Routes pour les commandes 
$routes['/orders'] = ['OrderController', 'index'];
$routes['/orders/create'] = ['OrderController', 'create'];
$routes['/orders/store'] = ['OrderController', 'store'];
$routes['/orders/edit/{orderId}'] = ['OrderController', 'edit'];
$routes['/orders/update'] = ['OrderController', 'update'];
$routes['/orders/delete/orderId}'] = ['OrderController', 'delete'];
$routes['/orders/show/{orderId}'] = ['OrderController', 'show'];
$routes['/orders/status/update/{id}'] = ['OrderController', 'updateStatus'];

// Routes pour les entrées de stock
$routes['/stock-entries'] = ['EntryController', 'index'];
$routes['/entries/create'] = ['EntryController', 'create'];
$routes['/entries/store'] = ['EntryController', 'store'];
$routes['/entries/edit/{id}'] = ['EntryController', 'edit'];
$routes['/entries/update/{id}'] = ['EntryController', 'update'];
$routes['/entries/delete/{entryId}'] = ['EntryController', 'delete'];
$routes['/entries/show/{entryId}'] = ['EntryController', 'show'];

// Routes pour les sorties de stock
$routes['/stock-exits'] = ['ExitController', 'index'];
$routes['/exits/create'] = ['ExitController', 'create'];
$routes['/exits/store'] = ['ExitController', 'store'];
$routes['/exits/edit/{exitId}'] = ['ExitController', 'edit'];
$routes['/exits/update/{exitId}'] = ['ExitController', 'update'];
$routes['/exits/delete/{exitId}'] = ['ExitController', 'delete'];
$routes['/exits/show/{exitId}'] = ['ExitController', 'show'];


// Routes des utilisateurs (admin seulement)
$routes['/users'] = ['UserController', 'index'];
$routes['/users/create'] = ['UserController', 'create'];
$routes['/users/store'] = ['UserController', 'store'];
$routes['/users/edit/{id}'] = ['UserController', 'edit'];
$routes['/users/update/{id}'] = ['UserController', 'update'];
$routes['/users/delete/{id}'] = ['UserController', 'delete'];
$routes['/users/show/{id}'] = ['UserController', 'show'];
$routes['/users/profile'] = ['UserController', 'profile'];
$routes['/users/profile/update'] = ['UserController', 'updateProfile'];

