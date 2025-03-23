<?php

class DeliveryController extends Core\Controller
{
    private $deliveryModel;
    private $orderModel;
    private $clientModel;
    private $productModel;

    public function __construct()
    {
        $this->deliveryModel = $this->model('Delivery');
        $this->orderModel = $this->model('Order');
        $this->clientModel = $this->model('Client');
        $this->productModel = $this->model('Product');
    }

    public function index()
    {
        // Get all deliveries
        $deliveries = $this->deliveryModel->getAllDeliveries();
        
        $data = [
            'deliveries' => $deliveries
        ];
        
        $this->view('deliveries/index', $data);
    }

    public function create()
    {
        // Get pending orders for delivery note creation
        $pendingOrders = $this->orderModel->getPendingOrders();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process form
            
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Create data array
            $data = [
                'order_id' => trim($_POST['order_id']),
                'delivery_date' => trim($_POST['delivery_date']),
                'status' => 'pending',
                'notes' => trim($_POST['notes']),
                'created_by' => $_SESSION['user_id'],
                'order_id_err' => '',
                'delivery_date_err' => '',
            ];
            
            // Validate order_id
            if (empty($data['order_id'])) {
                $data['order_id_err'] = 'Please select an order';
            }
            
            // Validate delivery_date
            if (empty($data['delivery_date'])) {
                $data['delivery_date_err'] = 'Please enter delivery date';
            }
            
            // Make sure errors are empty
            if (empty($data['order_id_err']) && empty($data['delivery_date_err'])) {
                // Validated
                if ($this->deliveryModel->createDelivery($data)) {
                    // Update order status
                    $this->orderModel->updateOrderStatus($data['order_id'], 'processing');
                    
                    flash('delivery_message', 'Delivery note created successfully');
                    redirect('deliveries');
                } else {
                    die('Something went wrong');
                }
            } else {
                // Load view with errors
                $data['pendingOrders'] = $pendingOrders;
                $this->view('deliveries/create', $data);
            }
        } else {
            $data = [
                'pendingOrders' => $pendingOrders,
                'order_id' => '',
                'delivery_date' => date('Y-m-d'),
                'notes' => '',
                'order_id_err' => '',
                'delivery_date_err' => '',
            ];
            
            $this->view('deliveries/create', $data);
        }
    }

    public function edit($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process form
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Create data array
            $data = [
                'id' => $id,
                'delivery_date' => trim($_POST['delivery_date']),
                'status' => trim($_POST['status']),
                'notes' => trim($_POST['notes']),
                'delivery_date_err' => '',
            ];
            
            // Validate delivery_date
            if (empty($data['delivery_date'])) {
                $data['delivery_date_err'] = 'Please enter delivery date';
            }
            
            // Make sure errors are empty
            if (empty($data['delivery_date_err'])) {
                // Validated
                if ($this->deliveryModel->updateDelivery($data)) {
                    flash('delivery_message', 'Delivery note updated successfully');
                    redirect('deliveries');
                } else {
                    die('Something went wrong');
                }
            } else {
                // Load view with errors
                $this->view('deliveries/edit', $data);
            }
        } else {
            // Get delivery by ID
            $delivery = $this->deliveryModel->getDeliveryById($id);
            
            // Check if delivery exists
            if (!$delivery) {
                redirect('deliveries');
            }
            
            $data = [
                'id' => $delivery->id,
                'order_id' => $delivery->order_id,
                'delivery_date' => $delivery->delivery_date,
                'status' => $delivery->status,
                'notes' => $delivery->notes,
                'delivery_date_err' => '',
            ];
            
            $this->view('deliveries/edit', $data);
        }
    }

    public function show($id)
    {
        // Get delivery by ID
        $delivery = $this->deliveryModel->getDeliveryById($id);
        
        // Get related order details
        $order = $this->orderModel->getOrderById($delivery->order_id);
        
        // Get client information
        $client = $this->clientModel->getClientById($order->client_id);
        
        // Get order items
        $orderItems = $this->orderModel->getOrderItems($order->id);
        
        $data = [
            'delivery' => $delivery,
            'order' => $order,
            'client' => $client,
            'orderItems' => $orderItems
        ];
        
        $this->view('deliveries/show', $data);
    }

    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Get delivery by ID
            $delivery = $this->deliveryModel->getDeliveryById($id);
            
            // Check if delivery exists
            if (!$delivery) {
                redirect('deliveries');
            }
            
            if ($this->deliveryModel->deleteDelivery($id)) {
                flash('delivery_message', 'Delivery note removed');
                redirect('deliveries');
            } else {
                die('Something went wrong');
            }
        } else {
            redirect('deliveries');
        }
    }

    public function generatePDF($id)
    {
        // Get delivery by ID
        $delivery = $this->deliveryModel->getDeliveryById($id);
        
        // Get related order details
        $order = $this->orderModel->getOrderById($delivery->order_id);
        
        // Get client information
        $client = $this->clientModel->getClientById($order->client_id);
        
        // Get order items
        $orderItems = $this->orderModel->getOrderItems($order->id);
        
        $data = [
            'delivery' => $delivery,
            'order' => $order,
            'client' => $client,
            'orderItems' => $orderItems
        ];
        
        // Generate PDF using FPDF
        require_once APPROOT . '/lib/fpdf/fpdf.php';
        
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Add company logo and header
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(190, 10, 'BON DE LIVRAISON', 0, 1, 'C');
        $pdf->Cell(190, 10, 'No: ' . $delivery->id, 0, 1, 'C');
        
        // Add delivery information
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(190, 10, 'Informations de Livraison', 0, 1, 'L');
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(50, 7, 'Date de Livraison:', 0, 0);
        $pdf->Cell(140, 7, $delivery->delivery_date, 0, 1);
        
        $pdf->Cell(50, 7, 'Statut:', 0, 0);
        $pdf->Cell(140, 7, $delivery->status, 0, 1);
        
        // Add client information
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(190, 10, 'Client', 0, 1, 'L');
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(50, 7, 'Nom:', 0, 0);
        $pdf->Cell(140, 7, $client->name, 0, 1);
        
        $pdf->Cell(50, 7, 'Adresse:', 0, 0);
        $pdf->Cell(140, 7, $client->address, 0, 1);
        
        $pdf->Cell(50, 7, 'Téléphone:', 0, 0);
        $pdf->Cell(140, 7, $client->phone, 0, 1);
        
        // Add order items
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(190, 10, 'Produits', 0, 1, 'L');
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(10, 7, 'No', 1, 0, 'C');
        $pdf->Cell(80, 7, 'Produit', 1, 0, 'C');
        $pdf->Cell(30, 7, 'Quantité', 1, 0, 'C');
        $pdf->Cell(30, 7, 'Prix Unitaire', 1, 0, 'C');
        $pdf->Cell(40, 7, 'Total', 1, 1, 'C');
        
        $pdf->SetFont('Arial', '', 10);
        $count = 1;
        $grandTotal = 0;
        
        foreach ($orderItems as $item) {
            $product = $this->productModel->getProductById($item->product_id);
            $total = $item->quantity * $item->unit_price;
            $grandTotal += $total;
            
            $pdf->Cell(10, 7, $count, 1, 0, 'C');
            $pdf->Cell(80, 7, $product->name, 1, 0);
            $pdf->Cell(30, 7, $item->quantity, 1, 0, 'C');
            $pdf->Cell(30, 7, number_format($item->unit_price, 2) . ' XAF', 1, 0, 'R');
            $pdf->Cell(40, 7, number_format($total, 2) . ' XAF', 1, 1, 'R');
            
            $count++;
        }
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(150, 7, 'Total', 1, 0, 'R');
        $pdf->Cell(40, 7, number_format($grandTotal, 2) . ' XAF', 1, 1, 'R');
        
        // Add signatures
        $pdf->Ln(15);
        $pdf->Cell(95, 7, 'Signature du Livreur', 0, 0, 'C');
        $pdf->Cell(95, 7, 'Signature du Client', 0, 1, 'C');
        
        $pdf->Ln(20);
        $pdf->Cell(95, 7, '................................', 0, 0, 'C');
        $pdf->Cell(95, 7, '................................', 0, 1, 'C');
        
        // Add notes
        if (!empty($delivery->notes)) {
            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(190, 7, 'Notes:', 0, 1);
            $pdf->SetFont('Arial', '', 10);
            $pdf->MultiCell(190, 7, $delivery->notes, 0, 'L');
        }
        
        // Output PDF
        $pdf->Output('D', 'bon_de_livraison_' . $delivery->id . '.pdf');
    }
}
