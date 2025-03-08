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
$routes['/auth/logout'] = ['`AuthController`', 'logout'];
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
