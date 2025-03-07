<?php
/**
 * Modèle Opération (classe parent pour Entrée et Sortie)
 */
class Operation {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Récupérer toutes les opérations
     */
    public function getAll() {
        return $this->db->fetchAll("SELECT * FROM operation ORDER BY date DESC");
    }
    
    /**
     * Récupérer une opération par son numéro
     */
    public function getById($id) {
        return $this->db->fetch("SELECT * FROM operation WHERE numero_operation = ?", [$id]);
    }
    
    /**
     * Créer une nouvelle opération
     */
    public function create($data,$products) {
        // Ajouter la date si elle n'est pas spécifiée
        if (!isset($data['date'])) {
            $data['date'] = date('Y-m-d H:i:s');
        }
        
        return $this->db->insert('operation', $data);
    }
    
    /**
     * Mettre à jour une opération
     */
    public function update($id, $data) {
        return $this->db->update('operation', $data, 'numero_operation = ?', [$id]);
    }
    
    /**
     * Supprimer une opération
     */
    public function delete($id) {
        // Supprimer d'abord les relations avec les produits
        $this->db->delete('operation_produit', 'numero_operation = ?', [$id]);
        
        // Puis supprimer l'opération
        return $this->db->delete('operation', 'numero_operation = ?', [$id]);
    }
    
    /**
     * Ajouter un produit à une opération
     */
    public function addProduct($operationId, $productId, $quantity, $price) {
        return $this->db->insert('operation_produit', [
            'numero_operation' => $operationId,
            'id_produit' => $productId,
            'quantite' => $quantity,
            'prix_unitaire' => $price
        ]);
    }
    
    /**
     * Récupérer les produits d'une opération
     */
    public function getProducts($operationId) {
        return $this->db->fetchAll(
            "SELECT op.*, p.designation, p.prix_vente, p.prix_achat 
             FROM operation_produit op
             JOIN produit p ON op.id_produit = p.id_produit
             WHERE op.numero_operation = ?",
            [$operationId]
        );
    }
    
    /**
     * Calculer le total d'une opération
     */
    public function calculateTotal($operationId) {
        $result = $this->db->fetch(
            "SELECT SUM(quantite * prix_unitaire) as total 
             FROM operation_produit 
             WHERE numero_operation = ?",
            [$operationId]
        );
        
        return $result['total'] ?? 0;
    }
    
    /**
     * Mettre à jour le total d'une opération
     */
    public function updateTotal($operationId) {
        $total = $this->calculateTotal($operationId);
        
        return $this->db->update(
            'operation',
            ['prix' => $total],
            'numero_operation = ?',
            [$operationId]
        );
    }
    
    /**
     * Récupérer les opérations par type
     */
    public function getByType($type) {
        return $this->db->fetchAll(
            "SELECT * FROM operation WHERE type = ? ORDER BY date DESC",
            [$type]
        );
    }
    
    /**
     * Récupérer les opérations par période
     */
    public function getByPeriod($startDate, $endDate, $type = null) {
        $sql = "SELECT * FROM operation WHERE date BETWEEN ? AND ?";
        $params = [$startDate, $endDate];
        
        if ($type !== null) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        
        $sql .= " ORDER BY date DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Récupérer les statistiques des opérations
     */
    public function getStats($period = 'month') {
        $sql = "";
        
        switch ($period) {
            case 'day':
                $sql = "SELECT DATE(date) as period, COUNT(*) as count, SUM(prix) as total, type 
                       FROM operation 
                       WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                       GROUP BY DATE(date), type 
                       ORDER BY DATE(date)";
                break;
            case 'month':
                $sql = "SELECT DATE_FORMAT(date, '%Y-%m') as period, COUNT(*) as count, SUM(prix) as total, type 
                       FROM operation 
                       WHERE date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) 
                       GROUP BY DATE_FORMAT(date, '%Y-%m'), type 
                       ORDER BY period";
                break;
            case 'year':
                $sql = "SELECT YEAR(date) as period, COUNT(*) as count, SUM(prix) as total, type 
                       FROM operation 
                       GROUP BY YEAR(date), type 
                       ORDER BY period";
                break;
        }
        
        return $this->db->fetchAll($sql);
    }
}
