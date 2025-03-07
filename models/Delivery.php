<?php
/**
 * Modèle Livraison
 */
class Delivery {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Récupérer toutes les livraisons
     */
    public function getAll() {
        return $this->db->fetchAll(
            "SELECT l.*, s.client
             FROM livraison l
             JOIN sortie s ON l.id_sortie = s.id_sortie
             ORDER BY l.date DESC"
        );
    }
    
    /**
     * Récupérer une livraison par son numéro
     */
    public function getById($id) {
        return $this->db->fetch(
            "SELECT l.*, s.client
             FROM livraison l
             JOIN sortie s ON l.id_sortie = s.id_sortie
             WHERE l.numero_livraison = ?",
            [$id]
        );
    }
    
    /**
     * Créer une nouvelle livraison
     */
    public function create($data) {
        // Récupérer les informations de la sortie
        $exitModel = new ExitOp();
        $exit = $exitModel->getById($data['id_sortie']);
        
        if (!$exit) {
            throw new Exception("La sortie spécifiée n'existe pas.");
        }
        
        $deliveryData = [
            'numero_livraison' => $data['numero_livraison'] ?? generateReference('LIV'),
            'date' => $data['date'] ?? date('Y-m-d H:i:s'),
            'montant' => $exit['prix'],
            'nombre_produit' => $exit['nombre_produit'],
            'type' => $data['type'] ?? 'standard',
            'id_sortie' => $data['id_sortie']
        ];
        
        return $this->db->insert('livraison', $deliveryData);
    }

    /**
 * Récupérer une livraison par l'ID de commande
 */
public function getByOrderId($orderId) {
  return $this->db->fetch(
      "SELECT l.*, c.numero_commande, cl.nom as nom_client, cl.prenom as prenom_client
       FROM livraison l
       JOIN commande c ON l.id_commande = c.id_commande
       JOIN client cl ON c.id_client = cl.id_client
       WHERE l.id_commande = ?",
      [$orderId]
  );
}
    
    /**
     * Mettre à jour une livraison
     */
    public function update($id, $data) {
        $deliveryData = [
            'date' => $data['date'] ?? null,
            'type' => $data['type'] ?? null
        ];
        
        // Filtrer les valeurs null
        $deliveryData = array_filter($deliveryData, function($value) {
            return $value !== null;
        });
        
        if (!empty($deliveryData)) {
            return $this->db->update('livraison', $deliveryData, 'numero_livraison = ?', [$id]);
        }
        
        return false;
    }
        /**
     * Mettre à jour le statut d'une livraison
     */
    public function updateStatus($id, $status) {
      return $this->db->execute(
          "UPDATE livraison SET statut = ? WHERE id_livraison = ?",
          [$status, $id]
      );
  }
    
    /**
     * Supprimer une livraison
     */
    public function delete($id) {
        return $this->db->delete('livraison', 'numero_livraison = ?', [$id]);
    }
    
    /**
     * Récupérer les livraisons par client
     */
    public function getByClient($client) {
        return $this->db->fetchAll(
            "SELECT l.*, s.client
             FROM livraison l
             JOIN sortie s ON l.id_sortie = s.id_sortie
             WHERE s.client LIKE ?
             ORDER BY l.date DESC",
            ["%{$client}%"]
        );
    }
    
    /**
     * Récupérer les livraisons par période
     */
    public function getByPeriod($startDate, $endDate) {
        return $this->db->fetchAll(
            "SELECT l.*, s.client
             FROM livraison l
             JOIN sortie s ON l.id_sortie = s.id_sortie
             WHERE l.date BETWEEN ? AND ?
             ORDER BY l.date DESC",
            [$startDate, $endDate]
        );
    }
    
    /**
     * Récupérer les livraisons par type
     */
    public function getByType($type) {
        return $this->db->fetchAll(
            "SELECT l.*, s.client
             FROM livraison l
             JOIN sortie s ON l.id_sortie = s.id_sortie
             WHERE l.type = ?
             ORDER BY l.date DESC",
            [$type]
        );
    }
    
    /**
     * Récupérer les produits d'une livraison
     */
    public function getProducts($id) {
        $delivery = $this->getById($id);
        
        if (!$delivery) {
            return [];
        }
        
        $exitModel = new ExitOp();
        return $exitModel->getProducts($delivery['id_sortie']);
    }
    
    /**
     * Générer un bon de livraison
     */
    public function generateDeliveryNote($id) {
        $delivery = $this->getById($id);
        $products = $this->getProducts($id);
        
        // Récupérer les informations du client
        $exitModel = new ExitOp();
        $exit = $exitModel->getById($delivery['id_sortie']);
        
        // Ici, vous pourriez générer un PDF ou simplement retourner les données
        return [
            'delivery' => $delivery,
            'exit' => $exit,
            'products' => $products,
            'delivery_note_number' => $delivery['numero_livraison'],
            'delivery_date' => $delivery['date'],
            'total' => $delivery['montant']
        ];
    }
    
    /**
     * Récupérer les statistiques des livraisons par période
     */
    public function getStatsByPeriod($period = 'month') {
        $sql = "";
        
        switch ($period) {
            case 'day':
                $sql = "SELECT DATE(date) as period, COUNT(*) as count, SUM(montant) as total
                        FROM livraison
                        GROUP BY DATE(date)
                        ORDER BY DATE(date) DESC
                        LIMIT 30";
                break;
            case 'week':
                $sql = "SELECT YEAR(date) as year, WEEK(date) as week, 
                               COUNT(*) as count, SUM(montant) as total
                        FROM livraison
                        GROUP BY YEAR(date), WEEK(date)
                        ORDER BY YEAR(date) DESC, WEEK(date) DESC
                        LIMIT 12";
                break;
            case 'month':
            default:
                $sql = "SELECT YEAR(date) as year, MONTH(date) as month, 
                               COUNT(*) as count, SUM(montant) as total
                        FROM livraison
                        GROUP BY YEAR(date), MONTH(date)
                        ORDER BY YEAR(date) DESC, MONTH(date) DESC
                        LIMIT 12";
                break;
        }
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Récupérer les statistiques des livraisons par type
     */
    public function getStatsByType() {
        return $this->db->fetchAll(
            "SELECT type, COUNT(*) as count, SUM(montant) as total
             FROM livraison
             GROUP BY type
             ORDER BY count DESC"
        );
    }
}
