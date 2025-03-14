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
// Routes pour les clients 
$routes['/clients'] = ['ClientController', 'index'];
$routes['/clients/create'] = ['ClientController', 'create'];
$routes['/clients/store'] = ['ClientController', 'store'];
$routes['/clients/edit'] = ['ClientController', 'edit'];
$routes['/clients/update'] = ['ClientController', 'update'];
$routes['/clients/delete'] = ['ClientController', 'delete'];
$routes['/clients/show'] = ['ClientController', 'show'];
// Routes pour les commandes 
$routes['/orders'] = ['OrderController', 'index'];
$routes['/orders/create'] = ['OrderController', 'create'];
$routes['/orders/store'] = ['OrderController', 'store'];
$routes['/orders/edit'] = ['OrderController', 'edit'];
$routes['/orders/update'] = ['OrderController', 'update'];
$routes['/orders/delete'] = ['OrderController', 'delete'];
$routes['/orders/show'] = ['OrderController', 'show'];
