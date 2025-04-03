<?php
namespace App\Controllers;

use App\Models\Client;
use App\Utils\Auth;
use App\Core\Controller;
use App\Models\Product;
use App\Models\Entry;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use App\Models\ExitOp;
use App\Models\Operation;

class DashboardController extends Controller
{
    public function index()
    {
        if (!Auth::isLoggedIn()) {
          // die('bonjour');
            $this->redirect('/auth/login'); // Redirect if not logged in
        }
        $userRole = $_SESSION['user_role'];
        switch ($userRole) {
            case 'admin':
                $this->adminDashboard();
                break;
            case 'secretaire':
                $this->secretaryDashboard();
                break;
            case 'magasinier':
                $this->storekeeperDashboard();
                break;
            default:
                // Handle unknown roles
                echo "Unknown role.";
                break;
        }
    }

    private function adminDashboard()
    {
        // Fetch data for the director's dashboard
        $products = Product::getAll(); // Example: Fetch all products
        $orders = Order::where('statut', '=','pending')->get(); // Example: Fetch pending orders
        $users = User::getAll();
        $stats = [
          'users' => count($users),
          'orders' => count(Order::getAll()),
          'products' => count($products),
          'pending_orders' => count($orders)

        ];
        // Load the admin dashboard view
        $this->view('dashboard/admin', [
            'products' => $products,
            'orders' => $orders,
            'users' => $users,
            'stats' => $stats
        ]);
    }

    private function secretaryDashboard()
    {
        // Fetch data for the secretary's dashboard
        $orders = Order::where('statut', '=','pending');// Example: Fetch pending orders
        $orders = Order::getAll();
        $deliveries = Delivery::getAll(); // Example: Fetch recent deliveries
        $clients = Client::getAll(); 
        $recentOrders = Order::getAll(10);

        // Load the secretary dashboard view
        $this->view('dashboard/secretary', [
            'orders' => $orders,
            'deliveries' => $deliveries,
            'clients' => $clients,
            'recentOrders' => $recentOrders
            // ... pass other data
        ],'admin');
    }

    private function storekeeperDashboard()
    {
        // Fetch data for the storekeeper's dashboard
        $products = Product::getAll(); // Example: Fetch all products
        $entries = Entry::getAll(); // Example: Fetch recent entries
        $exits = ExitOp::getAll(); // example fetch recent exits.
        $orders = Order::where('statut', '','en_attente');

        // Load the storekeeper dashboard view
        // var_dump($products);
        $this->view('dashboard/storekeeper', [
            'products' => $products,
            'outOfStock' => Product::getOutOfStock(),
            'lowStockProducts' => Product::getLowStock(),
            'operations' => Operation::getAll(5),
            'entries' => $entries,
            'exits' => $exits,
            'orders' => $orders,
        ],'admin');
    }
}
