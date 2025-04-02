<?php

namespace App\Controllers;

use App\Core\Controller;
class HomeController extends Controller
{
    public function index()
    {
        // Logique pour la page d'accueil
        return $this->view('/auth/login',[],'default');
    }
    
    // Ajoutez d'autres méthodes selon vos besoins
}
