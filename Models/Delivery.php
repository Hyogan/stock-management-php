<?php
/**
 * Modèle Livraison
 */
namespace App\Models;
use App\Utils\Database;
use App\Core\Model;


class Delivery extends Model{
  protected static $table = 'livraisons';    
    
    /**
     * Récupérer toutes les livraisons
     */
    public static function getAll() 
    {
      $db = Database::getInstance();  
        return $db->fetchAll(
            "SELECT l.*, s.client
             FROM livraisons l
             JOIN sortie s ON l.id_sortie = s.id_sortie
             ORDER BY l.date DESC"
        );
    }
    
    /**
     * Récupérer une livraison par son numéro
     */
    public function getById($id) 
    {
      $db = Database::getInstance();  
        return $db->fetch(
            "SELECT l.*, s.client
             FROM" . self::$table . " l
             JOIN sortie s ON l.id_sortie = s.id_sortie
             WHERE l.numero_livraison = ?",
            [$id]
        );
    }
    
    /**
     * Créer une nouvelle livraison
     */
    public static function create($data) {
        // Récupérer les informations de la sortie
        $exitModel = new ExitOp();
        $exit = $exitModel->getById($data['id_sortie']);
        
        if (!$exit) {
            throw new \Exception("La sortie spécifiée n'existe pas.");
        }
        
        $deliveryData = [
            'numero_livraison' => $data['numero_livraison'] ?? generateReference('LIV'),
            'date' => $data['date'] ?? date('Y-m-d H:i:s'),
            'montant' => $exit['prix'],
            'nombre_produit' => $exit['nombre_produit'],
            'type' => $data['type'] ?? 'standard',
            'id_sortie' => $data['id_sortie']
        ];
        $db = Database::getInstance();  
        return $db->insert(self::$table, $deliveryData);
    }

    /**
 * Récupérer une livraison par l'ID de commande
 */
public function getByOrderId($orderId) {
  return $this->db->fetch(
      "SELECT l.*, c.numero_commande, cl.nom as nom_client, cl.prenom as prenom_client
       FROM". self::$table ."l
       JOIN commande c ON l.id_commande = c.id_commande
       JOIN client cl ON c.id_client = cl.id_client
       WHERE l.id_commande = ?",
      [$orderId]
  );
}
    
    /**
     * Mettre à jour une livraison
     */
    public static function update($id, $data) 
    {
        $deliveryData = [
            'date' => $data['date'] ?? null,
            'type' => $data['type'] ?? null
        ];
        
        // Filtrer les valeurs null
        $deliveryData = array_filter($deliveryData, function($value) {
            return $value !== null;
        });
        
        if (!empty($deliveryData)) {
            $db = Database::getInstance();
            return $db->update(self::$table, $deliveryData, 'numero_livraison = ?', [$id]);
        }
        
        return false;
    }
        /**
     * Mettre à jour le statut d'une livraison
     */
    public function updateStatus($id, $status) 
    {
      return $this->db->execute(
          "UPDATE". self::$table ."SET statut = ? WHERE id_livraison = ?",
          [$status, $id]
      );
  }
    
    /**
     * Supprimer une livraison
     */
    public static function delete($id) 
    {
        $db = Database::getInstance();
        return $db->delete(self::$table, 'numero_livraison = ?', [$id]);
    }
    
    /**
     * Récupérer les livraisons par client
     */
    public function getByClient($client) {
        return $this->db->fetchAll(
            "SELECT l.*, s.client
             FROM".self::$table ."l
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
             FROM ". self::$table ." l
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
             FROM ". self::$table . "l
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
                        FROM ". self::$table ."
                        GROUP BY DATE(date)
                        ORDER BY DATE(date) DESC
                        LIMIT 30";
                break;
            case 'week':
                $sql = "SELECT YEAR(date) as year, WEEK(date) as week, 
                               COUNT(*) as count, SUM(montant) as total
                        FROM " . self::$table . "
                        GROUP BY YEAR(date), WEEK(date)
                        ORDER BY YEAR(date) DESC, WEEK(date) DESC
                        LIMIT 12";
                break;
            case 'month':
            default:
                $sql = "SELECT YEAR(date) as year, MONTH(date) as month, 
                               COUNT(*) as count, SUM(montant) as total
                        FROM " . self::$table . "
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
             FROM " . self::$table . "
             GROUP BY type
             ORDER BY count DESC"
        );
    }
}
