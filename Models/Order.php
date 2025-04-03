<?php

namespace App\Models;

use App\Core\Model;
use App\Utils\Database;

class Order extends Model {
    protected static $table = 'commandes';
    
    /**
     * Récupère toutes les commandes
     */
    public static function getAll($limit = null ,$sort = 'date_creation', $order = 'desc') {
        $db = Database::getInstance();
        $query = "SELECT c.*, cl.nom as client_nom, cl.prenom as client_prenom, u.nom as user_nom, u.prenom as user_prenom 
                 FROM commandes c
                 JOIN clients cl ON c.id_client = cl.id
                 JOIN utilisateurs u ON c.id_utilisateur = u.id
                 ORDER BY c.{$sort} {$order}";
        if ($limit !== null) {
          $query .= " LIMIT ?";
          return $db->fetchAll($query, [$limit]);
      }
      return $db->fetchAll($query);
    }


  public static function setStatus($orderId,$status = 'pending')
  {
    $db = Database::getInstance();
    $query = "UPDATE commandes SET statut = ? WHERE id = ?";
      return $db->query($query, [$status, $orderId]);
  }

    
    /**
     * Recherche des commandes selon critères
     */
    public static function search($search, $status = '', $sort = 'date_creation', $order = 'desc') {
        $db = Database::getInstance();
        $query = "SELECT c.*, cl.nom as client_nom, cl.prenom as client_prenom, u.nom as user_nom, u.prenom as user_prenom 
                 FROM commandes c
                 JOIN clients cl ON c.id_client = cl.id
                 JOIN utilisateurs u ON c.id_utilisateur = u.id
                 WHERE (c.reference LIKE ? OR cl.nom LIKE ? OR cl.prenom LIKE ?)";
        
        $params = ["%$search%", "%$search%", "%$search%"];
        
        if (!empty($status)) {
            $query .= " AND c.statut = ?";
            $params[] = $status;
        }
        
        $query .= " ORDER BY c.{$sort} {$order}";
        
        return $db->fetchAll($query, $params);
    }
    
    /**
     * Récupère une commande par son ID
     */
    public static function getById($id) {
        $db = Database::getInstance();
        $query = "SELECT c.*, cl.nom as client_nom, cl.prenom as client_prenom, u.nom as user_nom, u.prenom as user_prenom 
                 FROM commandes c
                 JOIN clients cl ON c.id_client = cl.id
                 JOIN utilisateurs u ON c.id_utilisateur = u.id
                 WHERE c.id = ?";
        return $db->fetch($query, [$id]);
    }

    /**
     * Récupère une commande par son statut
     */
    public static function getByStatus($status) {
      $db = Database::getInstance();
      $query = "SELECT c.*, cl.nom as client_nom, cl.prenom as client_prenom, u.nom as user_nom, u.prenom as user_prenom 
               FROM commandes c
               JOIN clients cl ON c.id_client = cl.id
               JOIN utilisateurs u ON c.id_utilisateur = u.id
               WHERE c.statut = ?";
      return $db->fetch($query, [$status]);
  }
    
    /**
     * Récupère les détails d'une commande
     */
    public static function getOrderDetails($id) {
        $db = Database::getInstance();
        $query = "SELECT d.*, p.reference, p.designation, p.image 
                 FROM details_commande d
                 JOIN produits p ON d.id_produit = p.id
                 WHERE d.id_commande = ?";
        return $db->fetchAll($query, [$id]);
    }
    
    /**
     * Récupère les paiements d'une commande
     */
    public static function getOrderPayments($id) {
        $db = Database::getInstance();
        $query = "SELECT p.*, u.nom as user_nom, u.prenom as user_prenom 
                 FROM paiements p
                 JOIN utilisateurs u ON p.id_utilisateur = u.id
                 WHERE p.id_commande = ?
                 ORDER BY p.date_paiement DESC";
        return $db->fetchAll($query, [$id]);
    }
    
    /**
     * Ajoute une nouvelle commande
     */
    public static function add($data) {
        $db = Database::getInstance();
        $query = "INSERT INTO commandes (reference, id_client, id_utilisateur, montant_total, 
                 statut, statut_paiement, date_livraison_prevue, notes, date_creation) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['reference'],
            $data['id_client'],
            $data['id_utilisateur'],
            $data['montant_total'],
            $data['statut'] ?? 'pending',
            $data['statut_paiement'] ?? 'pending',
            $data['date_livraison_prevue'] ?? null,
            $data['notes'] ?? null,
            $data['date_creation'] ?? date('Y-m-d H:i:s')
        ];
        
        $db->query($query, $params);
        return $db->getConnection()->lastInsertId();
    }
    
    /**
     * Ajoute un détail de commande
     */
    public static function addOrderDetail($data) {
        $db = Database::getInstance();
        $query = "INSERT INTO details_commande (id_commande, id_produit, quantite, prix_unitaire, montant_total) 
                 VALUES (?, ?, ?, ?, ?)";
        $params = [
            $data['id_commande'],
            $data['id_produit'],
            $data['quantite'],
            $data['prix_unitaire'],
            $data['montant_total']
        ];
        
        $db->query($query, $params);
        return $db->getConnection()->lastInsertId();
    }
    
    /**
     * Ajoute un paiement
     */
    public static function addPayment($data) {
        $db = Database::getInstance();
        $query = "INSERT INTO paiements (id_commande, montant, mode_paiement, reference_transaction, 
                 date_paiement, notes, id_utilisateur) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['id_commande'],
            $data['montant'],
            $data['mode_paiement'],
            $data['reference_transaction'] ?? null,
            $data['date_paiement'],
            $data['notes'] ?? null,
            $data['id_utilisateur']
        ];
        
        $db->query($query, $params);
        return $db->getConnection()->lastInsertId();
    }
    
    /**
     * Met à jour une commande
     */
    public static function update($id, $data) {
        $db = Database::getInstance();
        $fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        $params[] = $id;
        
        $query = "UPDATE commandes SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $db->query($query, $params);
    }
    
    /**
     * Supprime les détails d'une commande
     */
    public static function deleteOrderDetails($id) {
        $db = Database::getInstance();
        return $db->query("DELETE FROM details_commande WHERE id_commande = ?", [$id]);
    }
    
    /**
     * Supprime les paiements d'une commande
     */
    public static function deleteOrderPayments($id) {
        $db = Database::getInstance();
        return $db->query("DELETE FROM paiements WHERE id_commande = ?", [$id]);
    }
    
    /**
     * Supprime une commande
     */
    public static function delete($id) {
        $db = Database::getInstance();
        return $db->query("DELETE FROM commandes WHERE id = ?", [$id]);
    }
    
    /**
     * Récupère le nombre total de commandes
     */
    public static function getTotalCount() {
        $db = Database::getInstance();
        $result = $db->fetch("SELECT COUNT(*) as count FROM commandes");
        return $result['count'];
    }
    
    /**
     * Récupère le nombre de commandes par statut
     */
    public static function getCountByStatus() {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT statut, COUNT(*) as count FROM commandes GROUP BY statut");
    }
    
    /**
     * Récupère le nombre de commandes par mois
     */
    public static function getCountByMonth() {
        $db = Database::getInstance();
        return $db->fetchAll("
            SELECT 
                YEAR(date_creation) as year,
                MONTH(date_creation) as month,
                COUNT(*) as count,
                SUM(montant_total) as total
            FROM commandes
            WHERE date_creation >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY YEAR(date_creation), MONTH(date_creation)
            ORDER BY year DESC, month DESC
        ");
    }
    
    /**
     * Récupère le montant total des ventes
     */
    public static function getTotalRevenue() {
        $db = Database::getInstance();
        $result = $db->fetch("SELECT SUM(montant_total) as total FROM commandes WHERE statut = 'delivered'");
        return $result['total'] ?? 0;
    }
    
    /**
     * Récupère les meilleurs clients
     */
    public static function getTopClients($limit = 5) {
        $db = Database::getInstance();
        return $db->fetchAll("
            SELECT 
                c.id_client,
                cl.nom,
                cl.prenom,
                COUNT(c.id) as order_count,
                SUM(c.montant_total) as total_spent
            FROM commandes c
            JOIN clients cl ON c.id_client = cl.id
            GROUP BY c.id_client
            ORDER BY total_spent DESC
            LIMIT ?
        ", [$limit]);
    }
    
    /**
     * Récupère les produits les plus vendus
     */
    public static function getTopProducts($startDate = null, $endDate = null, $limit = 5) {
        $db = Database::getInstance();
        $query = "
            SELECT 
                d.id_produit,
                p.reference,
                p.designation,
                SUM(d.quantite) as total_quantity,
                SUM(d.montant_total) as total_amount
            FROM details_commande d
            JOIN produits p ON d.id_produit = p.id
            JOIN commandes c ON d.id_commande = c.id
            WHERE c.statut = 'delivered'";
        
        $params = [];
        
        if ($startDate && $endDate) {
            $query .= " AND c.date_creation BETWEEN ? AND ?";
            $params[] = $startDate . ' 00:00:00';
            $params[] = $endDate . ' 23:59:59';
        }
        
        $query .= " GROUP BY d.id_produit
                   ORDER BY total_quantity DESC
                   LIMIT ?";
        
        $params[] = $limit;
        
        return $db->fetchAll($query, $params);
    }
    
    /**
     * Récupère les commandes pour export
     */
    public static function getForExport($startDate = null, $endDate = null, $status = null) {
        $db = Database::getInstance();
        $query = "SELECT c.*, cl.nom as client_nom, cl.prenom as client_prenom 
                 FROM commandes c
                 JOIN clients cl ON c.id_client = cl.id
                 WHERE 1=1";
        
        $params = [];
        
        if ($startDate) {
            $query .= " AND c.date_creation >= ?";
            $params[] = $startDate . ' 00:00:00';
        }
        
        if ($endDate) {
            $query .= " AND c.date_creation <= ?";
            $params[] = $endDate . ' 23:59:59';
        }
        
        if ($status) {
            $query .= " AND c.statut = ?";
            $params[] = $status;
        }
        
        $query .= " ORDER BY c.date_creation DESC";
        
        return $db->fetchAll($query, $params);
    }
    
    /**
     * Récupère les commandes d'un client
     */
    public static function getByClient($clientId) {
        $db = Database::getInstance();
        $query = "SELECT c.* 
                 FROM commandes c
                 WHERE c.id_client = ?
                 ORDER BY c.date_creation DESC";
        
        return $db->fetchAll($query, [$clientId]);
    }
    
    /**
     * Crée une sortie de stock
     */
    public static function createStockExit($data) {
        $db = Database::getInstance();
        $query = "INSERT INTO sorties_stock (reference, date_sortie, id_utilisateur, type_sortie, 
                 id_commande, montant_total, notes, statut, date_creation) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['reference'],
            $data['date_sortie'],
            $data['id_utilisateur'],
            $data['type_sortie'],
            $data['id_commande'],
            $data['montant_total'],
            $data['notes'] ?? null,
            $data['statut'],
            $data['date_creation']
        ];
        
        $db->query($query, $params);
        return $db->getConnection()->lastInsertId();
    }
    
    /**
     * Ajoute un détail de sortie de stock
     */
    public static function addStockExitDetail($data) {
        $db = Database::getInstance();
        $query = "INSERT INTO details_sortie_stock (id_sortie, id_produit, quantite, prix_unitaire, montant_total) 
                 VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $data['id_sortie'],
            $data['id_produit'],
            $data['quantite'],
            $data['prix_unitaire'],
            $data['montant_total']
        ];
        
        $db->query($query, $params);
        return $db->getConnection()->lastInsertId();
    }
    
    /**
     * Met à jour le stock d'un produit
     */
    public static function updateProductStock($productId, $quantity) {
        $db = Database::getInstance();
        $query = "UPDATE produits SET quantite_stock = quantite_stock + ?, date_modification = NOW() WHERE id = ?";
        
        return $db->query($query, [$quantity, $productId]);
    }
    
    /**
     * Ajoute une opération de stock
     */
    public static function addStockOperation($data) {
        $db = Database::getInstance();
        $query = "INSERT INTO operations_stock (id_produit, type_operation, quantite, motif, 
                 id_commande, id_utilisateur, date_operation) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['id_produit'],
            $data['type_operation'],
            $data['quantite'],
            $data['motif'] ?? null,
            $data['id_commande'] ?? null,
            $data['id_utilisateur'],
            $data['date_operation']
        ];
        
        $db->query($query, $params);
        return $db->getConnection()->lastInsertId();
    }
    
    /**
     * Récupère les commandes filtrées par date et statut
     */
    public static function getFiltered($startDate, $endDate, $status = '') {
        $db = Database::getInstance();
        $query = "SELECT c.*, cl.nom as client_nom, cl.prenom as client_prenom 
                 FROM commandes c
                 JOIN clients cl ON c.id_client = cl.id
                 WHERE c.date_creation BETWEEN ? AND ?";
        
        $params = [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59'
        ];
        
        if (!empty($status)) {
            $query .= " AND c.statut = ?";
            $params[] = $status;
        }
        
        $query .= " ORDER BY c.date_creation DESC";
        
        return $db->fetchAll($query, $params);
    }
    
    /**
     * Récupère les statistiques des commandes pour une période donnée
     */
    public static function getStats($startDate = null, $endDate = null) {
        $db = Database::getInstance();
        $stats = [
            'total_commandes' => 0,
            'total_montant' => 0,
            'par_statut' => [],
            'par_jour' => []
        ];
        
        $whereClause = "";
        $params = [];
        
        if ($startDate && $endDate) {
            $whereClause = " WHERE date_creation BETWEEN ? AND ?";
            $params = [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59'
            ];
        }
        
        // Total des commandes et montant
        $query = "SELECT COUNT(*) as total, SUM(montant_total) as montant 
                 FROM commandes" . $whereClause;
        
        $result = $db->fetch($query, $params);
        $stats['total_commandes'] = $result['total'] ?? 0;
        $stats['total_montant'] = $result['montant'] ?? 0;
        
        // Par statut
        $query = "SELECT statut, COUNT(*) as total, SUM(montant_total) as montant 
                 FROM commandes" . $whereClause . " 
                 GROUP BY statut";
        
        $results = $db->fetchAll($query, $params);
        foreach ($results as $row) {
            $stats['par_statut'][$row['statut']] = [
                'total' => $row['total'],
                'montant' => $row['montant']
            ];
        }
        
        // Par jour
        $query = "SELECT DATE(date_creation) as jour, COUNT(*) as total, SUM(montant_total) as montant 
                 FROM commandes" . $whereClause . " 
                 GROUP BY DATE(date_creation) 
                 ORDER BY jour";
        
        $results = $db->fetchAll($query, $params);
        foreach ($results as $row) {
            $stats['par_jour'][$row['jour']] = [
                'total' => $row['total'],
                'montant' => $row['montant']
            ];
        }
        
        return $stats;
    }

      /**
     * Fetches order items for a given order ID.
     *
     * @param int $orderId The ID of the order.
     * @return array|null An array of order items, or null if an error occurs.
     */
    public static function getOrderItems($orderId) 
    {
      $db = Database::getInstance();
      try {
          $sql = "SELECT 
                      dc.id, 
                      dc.id_commande,
                      dc.id_produit,
                      p.designation AS produit_designation,
                      dc.quantite, 
                      dc.prix_unitaire, 
                      dc.montant_total 
                  FROM 
                      details_commande dc
                  JOIN 
                      produits p ON dc.id_produit = p.id
                  WHERE 
                      dc.id_commande = :orderId";

          $stmt = $db->prepare($sql); // Assuming $this->db is your PDO connection
          $stmt->bindParam(':orderId', $orderId, \PDO::PARAM_INT);
          $stmt->execute();

          return $stmt->fetchAll(\PDO::FETCH_ASSOC);

      } catch (\PDOException $e) {
          // Log the error or handle it appropriately
          error_log("Error fetching order items: " . $e->getMessage());
          return null; // Or throw an exception, depending on your error handling strategy
      }
  }


  /**
     * Récupère les éléments d'une commande (details_commande) par id_commande.
     *
     * @param int $orderId L'ID de la commande.
     * @return array|null Un tableau d'éléments de commande, ou null en cas d'erreur.
     */
    public static function getItems($orderId) {
      $db = Database::getInstance();
      try {
          $sql = "SELECT 
                      dc.id, 
                      dc.id_commande,
                      dc.id_produit,
                      p.designation AS produit_designation,
                      dc.quantite, 
                      dc.prix_unitaire, 
                      dc.montant_total 
                  FROM 
                      details_commande dc
                  JOIN 
                      produits p ON dc.id_produit = p.id
                  WHERE 
                      dc.id_commande = :orderId";

          $stmt = $db->prepare($sql);
          $stmt->bindParam(':orderId', $orderId, \PDO::PARAM_INT);
          $stmt->execute();

          return $stmt->fetchAll(\PDO::FETCH_ASSOC);

      } catch (\PDOException $e) {
          error_log("Erreur lors de la récupération des éléments de la commande : " . $e->getMessage());
          return null;
      }
  }

   /**
     * Récupère les informations du client par id_client.
     *
     * @param int $clientId L'ID du client.
     * @return array|null Les informations du client, ou null en cas d'erreur.
     */
    public static function getClient($clientId) {
      $db = Database::getInstance();
      try {
          $sql = "SELECT * FROM clients WHERE id = :clientId";

          $stmt = $db->prepare($sql);
          $stmt->bindParam(':clientId', $clientId, \PDO::PARAM_INT);
          $stmt->execute();

          return $stmt->fetch(\PDO::FETCH_ASSOC);

      } catch (\PDOException $e) {
          error_log("Erreur lors de la récupération des informations du client : " . $e->getMessage());
          return null;
      }
  }

}
