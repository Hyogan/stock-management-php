
<?php

class Delivery
{
    private $db;

    public function __construct()
    {
        $this->db = new Utils\Database;
    }

    // Get all deliveries
    public function getAllDeliveries()
    {
        $this->db->query('SELECT d.*, o.order_number, c.name as client_name 
                         FROM deliveries d 
                         INNER JOIN orders o ON d.order_id = o.id 
                         INNER JOIN clients c ON o.client_id = c.id 
                         ORDER BY d.created_at DESC');
        
        return $this->db->resultSet();
    }

    // Get delivery by ID
    public function getDeliveryById($id)
    {
        $this->db->query('SELECT * FROM deliveries WHERE id = :id');
        $this->db->bind(':id', $id);
        
        return $this->db->single();
    }

    // Create new delivery note
    public function createDelivery($data)
    {
        // Generate delivery number (BL-YYYYMMDD-XXX)
        $deliveryNumber = 'BL-' . date('Ymd') . '-' . $this->generateDeliverySequence();
        
        $this->db->query('INSERT INTO deliveries (order_id, delivery_number, delivery_date, status, notes, created_by) 
                         VALUES (:order_id, :delivery_number, :delivery_date, :status, :notes, :created_by)');
        
        // Bind values
        $this->db->bind(':order_id', $data['order_id']);
        $this->db->bind(':delivery_number', $deliveryNumber);
        $this->db->bind(':delivery_date', $data['delivery_date']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':notes', $data['notes']);
        $this->db->bind(':created_by', $data['created_by']);
        
        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // Update delivery
    public function updateDelivery($data)
    {
        $this->db->query('UPDATE deliveries SET delivery_date = :delivery_date, status = :status, notes = :notes, updated_at = NOW() 
                         WHERE id = :id');
        
        // Bind values
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':delivery_date', $data['delivery_date']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':notes', $data['notes']);
        
        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // Delete delivery
    public function deleteDelivery($id)
    {
        $this->db->query('DELETE FROM deliveries WHERE id = :id');
        $this->db->bind(':id', $id);
        
        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // Generate delivery sequence number
    private function generateDeliverySequence()
    {
        $this->db->query('SELECT COUNT(*) as count FROM deliveries WHERE DATE(created_at) = CURDATE()');
        $row = $this->db->single();
        
        return str_pad($row->count + 1, 3, '0', STR_PAD_LEFT);
    }

    // Get deliveries by order ID
    public function getDeliveriesByOrderId($orderId)
    {
        $this->db->query('SELECT * FROM deliveries WHERE order_id = :order_id ORDER BY created_at DESC');
        $this->db->bind(':order_id', $orderId);
        
        return $this->db->resultSet();
    }

    // Update delivery status
    public function updateDeliveryStatus($id, $status)
    {
        $this->db->query('UPDATE deliveries SET status = :status, updated_at = NOW() WHERE id = :id');
        
        // Bind values
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $status);
        
        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // Count deliveries by status
    public function countDeliveriesByStatus($status)
    {
        $this->db->query('SELECT COUNT(*) as count FROM deliveries WHERE status = :status');
        $this->db->bind(':status', $status);
        
        $row = $this->db->single();
        return $row->count;
    }
}
