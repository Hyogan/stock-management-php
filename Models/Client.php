<?php
/**
 * Modèle Client
 */
namespace App\Models;

 use App\Core\Model;
 use App\Utils\Database; 
class Client extends Model{
    protected static $table = 'clients';
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Récupérer tous les clients
     */
    public static function getAll() {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM client ORDER BY nom, prenom");
    }
    
    /**
     * Récupérer un client par son ID
     */
    public static function getById($id) {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM client WHERE id_client = ?", [$id]);
    }
    
    /**
     * Créer un nouveau client
     */
    public static function create($data) {
      $db = Database::getInstance();
      $query = "INSERT INTO clients (nom, prenom, email, telephone, adresse, ville, code_postal, pays, date_creation) 
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
      
      $params = [
          $data['nom'],
          $data['prenom'],
          $data['email'],
          $data['telephone'],
          $data['adresse'],
          $data['ville'],
          $data['code_postal'],
          $data['pays']
      ];
      
      $db->query($query, $params);
      return $db->getConnection()->lastInsertId();
  }
 /**
     * Met à jour un client
     */
    public static function update($id, $data) {
      $db = Database::getInstance();
      $query = "UPDATE clients 
               SET nom = ?, 
                   prenom = ?, 
                   email = ?, 
                   telephone = ?, 
                   adresse = ?, 
                   ville = ?, 
                   code_postal = ?, 
                   pays = ?, 
                   date_modification = NOW() 
               WHERE id = ?";
      
      $params = [
          $data['nom'],
          $data['prenom'],
          $data['email'],
          $data['telephone'],
          $data['adresse'],
          $data['ville'],
          $data['code_postal'],
          $data['pays'],
          $id
      ];
      
      return $db->query($query, $params);
  }
    
    /**
     * Supprimer un client
     */
    public static function delete($id) {
      $db = Database::getInstance();
      return $db->query("DELETE FROM clients WHERE id = ?", [$id]);
  }
    
    /**
     * Rechercher des clients
     */
    public function search($keyword) {
        $keyword = "%{$keyword}%";
        return $this->db->fetchAll(
            "SELECT * FROM client 
             WHERE nom LIKE ? OR prenom LIKE ? OR telephone LIKE ?
             ORDER BY nom, prenom",
            [$keyword, $keyword, $keyword]
        );
    }
    
    /**
     * Vérifier la solvabilité d'un client
     */
    public static function checkSolvency($id) {
        // Récupérer le total des commandes impayées
        $db  = Database::getInstance();
        $unpaidOrders = $db->fetch(
            "SELECT SUM(montant) as total_unpaid
             FROM commande
             WHERE id_client = ? AND statut_paiement != 'paid'",
            [$id]
        );
        
        $totalUnpaid = $unpaidOrders['total_unpaid'] ?? 0;
        
        // Définir un seuil de solvabilité (à ajuster selon vos besoins)
        $threshold = 5000; // Par exemple, 5000 DH
        
        return $totalUnpaid < $threshold;
    }
    
    /**
     * Récupérer l'historique des commandes d'un client
     */
    public function getOrderHistory($id) {
        return $this->db->fetchAll(
            "SELECT c.*, COUNT(cs.id_sortie) as nb_sorties
             FROM commande c
             LEFT JOIN commande_sortie cs ON c.numero_commande = cs.numero_commande
             WHERE c.id_client = ?
             GROUP BY c.numero_commande
             ORDER BY c.date DESC",
            [$id]
        );
    }
    
    /**
     * Récupérer les statistiques d'achat d'un client
     */
    public function getPurchaseStats($id) {
        return $this->db->fetch(
            "SELECT COUNT(c.numero_commande) as total_orders,
                    SUM(c.montant) as total_amount,
                    AVG(c.montant) as avg_order_value,
                    MAX(c.date) as last_order_date
             FROM commande c
             WHERE c.id_client = ?",
            [$id]
        );
    }
    
    /**
     * Récupérer les produits les plus achetés par un client
     */
    public function getMostPurchasedProducts($id, $limit = 5) {
        return $this->db->fetchAll(
            "SELECT p.id_produit, p.designation, SUM(op.quantite) as total_quantity
             FROM commande c
             JOIN commande_sortie cs ON c.numero_commande = cs.numero_commande
             JOIN operation o ON cs.id_sortie = o.numero_operation
             JOIN operation_produit op ON o.numero_operation = op.numero_operation
             JOIN produit p ON op.id_produit = p.id_produit
             WHERE c.id_client = ?
             GROUP BY p.id_produit, p.designation
             ORDER BY total_quantity DESC
             LIMIT ?",
            [$id, $limit]
        );
    }
    
    /**
     * Récupérer les clients les plus actifs
     */
    public function getMostActiveClients($limit = 10) {
        return $this->db->fetchAll(
            "SELECT c.id_client, c.nom, c.prenom, COUNT(co.numero_commande) as order_count,
                    SUM(co.montant) as total_spent
             FROM client c
             JOIN commande co ON c.id_client = co.id_client
             GROUP BY c.id_client, c.nom, c.prenom
             ORDER BY order_count DESC, total_spent DESC
             LIMIT ?",
            [$limit]
        );
    }
    
    /**
     * Récupérer les clients par ville
     */
    public function getByCity($city) {
        return $this->db->fetchAll(
            "SELECT * FROM client WHERE ville LIKE ? ORDER BY nom, prenom",
            ["%{$city}%"]
        );
    }
    
    /**
     * Récupérer les statistiques des clients par ville
     */
    public function getStatsByCity() {
        return $this->db->fetchAll(
            "SELECT ville, COUNT(*) as client_count
             FROM client
             GROUP BY ville
             ORDER BY client_count DESC"
        );
    }
    
    /**
     * Récupérer les clients inactifs (sans commande depuis une période donnée)
     */
    public function getInactiveClients($months = 6) {
        $date = date('Y-m-d', strtotime("-{$months} months"));
        
        return $this->db->fetchAll(
            "SELECT c.*
             FROM client c
             LEFT JOIN (
                 SELECT id_client, MAX(date) as last_order_date
                 FROM commande
                 GROUP BY id_client
             ) co ON c.id_client = co.id_client
             WHERE co.last_order_date IS NULL OR co.last_order_date < ?
             ORDER BY co.last_order_date",
            [$date]
        );
    }

    /**
 * Récupère toutes les commandes d'un client spécifique
 * 
 * @param int $clientId L'identifiant du client
 * @param int $limit Limite optionnelle du nombre de résultats
 * @param int $offset Décalage optionnel pour la pagination
 * @return array Les commandes du client
 */
  public static function getOrders($clientId, $limit = null, $offset = null) {
    $sql = "SELECT c.*, 
            CASE 
                WHEN c.statut = 'en_attente' THEN 'En attente'
                WHEN c.statut = 'validee' THEN 'Validée'
                WHEN c.statut = 'en_cours' THEN 'En cours de livraison'
                WHEN c.statut = 'livree' THEN 'Livrée'
                WHEN c.statut = 'annulee' THEN 'Annulée'
                ELSE c.statut
            END AS statut_libelle,
            DATE_FORMAT(c.date_commande, '%d/%m/%Y') AS date_formatee
            FROM commande c
            WHERE c.id_client = ?
            ORDER BY c.date_commande DESC";
    
    $params = [$clientId];
    
    if ($limit !== null) {
        $sql .= " LIMIT ?";
        $params[] = (int)$limit;
        
        if ($offset !== null) {
            $sql .= " OFFSET ?";
            $params[] = (int)$offset;
        }
    }
    $db = Database::getInstance();
    return $db->fetchAll($sql, $params);
  }
}
