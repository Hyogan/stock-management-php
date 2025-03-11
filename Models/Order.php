<?php
namespace App\Models;
use App\Core\Model;
use App\Models\Client;
use App\Utils\Database;
use Exception;
/**
 * Modèle Commande
 */
class Order extends Model{
  protected static $table = 'commandes';
    /**
     * Récupérer toutes les commandes
     */
    public static function getAll()
    {
      $db = Database::getInstance();  
      return $db->fetchAll(
            "SELECT c.*, cl.nom, cl.prenom
             FROM commande c
             JOIN client cl ON c.id_client = cl.id_client
             ORDER BY c.date DESC"
        );
    }
    
    /**
     * Récupérer une commande par son numéro
     */
    public function getById($id) {
        return $this->db->fetch(
            "SELECT c.*, cl.nom, cl.prenom, cl.telephone, cl.ville, cl.quartier
             FROM commande c
             JOIN client cl ON c.id_client = cl.id_client
             WHERE c.numero_commande = ?",
            [$id]
        );
    }

        /**
     * Récupérer les détails d'une commande (produits)
     */
    public function getItems($orderId) {
      return $this->db->fetchAll(
          "SELECT d.*, p.designation, p.reference
           FROM detail_commande d
           JOIN produit p ON d.id_produit = p.id_produit
           WHERE d.id_commande = ?",
          [$orderId]
      );
  }
    
    /**
     * Créer une nouvelle commande
     */
    public function add($data, $products) {
        // Commencer une transaction
        $this->db->getConnection()->beginTransaction();
        
        try {
            // Vérifier la solvabilité du client si nécessaire
            if (isset($data['check_solvency']) && $data['check_solvency']) {
                if (!Client::checkSolvency($data['id_client'])) {
                    throw new Exception("Le client n'est pas solvable pour cette commande.");
                }
            }
            // Calculer le montant total et le nombre de produits
            $totalAmount = 0;
            foreach ($products as $product) {
                $totalAmount += $product['quantite'] * $product['prix_unitaire'];
            }
            
            // Créer la commande
            $orderData = [
                'numero_commande' => $data['numero_commande'] ?? generateReference('CMD'),
                'date' => $data['date'] ?? date('Y-m-d H:i:s'),
                'montant' => $totalAmount,
                'nbr_produit' => count($products),
                'id_client' => $data['id_client'],
                'statut' => $data['statut'] ?? ORDER_STATUS_PENDING,
                'statut_paiement' => $data['statut_paiement'] ?? PAYMENT_STATUS_PENDING
            ];
            
            $orderId = $this->db->insert('commande', $orderData);
            
            // Ajouter les produits à la commande
            foreach ($products as $product) {
                $this->db->insert('commande_produit', [
                    'numero_commande' => $orderId,
                    'id_produit' => $product['id_produit'],
                    'quantite' => $product['quantite'],
                    'prix_unitaire' => $product['prix_unitaire']
                ]);
            }
            
            // Valider la transaction
            $this->db->getConnection()->commit();
            
            return $orderId;
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->getConnection()->rollBack();
            throw $e;
        }
    }

     /**
     * Mettre à jour le statut d'une commande
     */
    public function updateStatus($orderId, $status) {
      return $this->db->execute(
          "UPDATE commande SET statut = ? WHERE id_commande = ?",
          [$status, $orderId]
      );
  }
  
  /**
   * Récupérer le client d'une commande
   */
  public function getClient($clientId) {
      return $this->db->fetch(
          "SELECT * FROM client WHERE id_client = ?",
          [$clientId]
      );
  }
  
    
    /**
     * Mettre à jour une commande
     */
    public static function update($id, $data) {
        // Commencer une transaction
        $db = Database::getInstance();
        $db->getConnection()->beginTransaction();
        
        try {
            $orderData = [
                'date' => $data['date'] ?? null,
                'id_client' => $data['id_client'] ?? null,
                'statut' => $data['statut'] ?? null,
                'statut_paiement' => $data['statut_paiement'] ?? null
            ];
            
            // Filtrer les valeurs null
            $orderData = array_filter($orderData, function($value) {
                return $value !== null;
            });
            
            // Si des produits sont fournis, mettre à jour les produits et recalculer le montant
            if ($data !== null) {
                // Calculer le montant total et le nombre de produits
                $totalAmount = 0;
                foreach ($data as $product) {
                    $totalAmount += $product['quantite'] * $product['prix_unitaire'];
                }
                
                $orderData['montant'] = $totalAmount;
                $orderData['nbr_produit'] = count($data);
                
                // Supprimer les produits actuels
                $db->delete('commande_produit', 'numero_commande = ?', [$id]);
                
                // Ajouter les nouveaux produits
                foreach ($data as $product) {
                    $db->insert('commande_produit', [
                        'numero_commande' => $id,
                        'id_produit' => $product['id_produit'],
                        'quantite' => $product['quantite'],
                        'prix_unitaire' => $product['prix_unitaire']
                    ]);
                }
            }
            // Mettre à jour la commande
            if (!empty($orderData)) {
                $db->update('commande', $orderData, 'numero_commande = ?', [$id]);
            }
            
            // Valider la transaction
            $db->getConnection()->commit();
            
            return true;
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $db->getConnection()->rollBack();
            throw $e;
        }
    }
    
    /**
     * Supprimer une commande
     */
    public static function delete($id) {
        // Commencer une transaction
        $db = Database::getInstance();
        $db->getConnection()->beginTransaction();
        
        try {
            // Vérifier si la commande a des sorties associées
            $exits = $db->fetchAll(
                "SELECT id_sortie FROM commande_sortie WHERE numero_commande = ?",
                [$id]
            );
            
            if (!empty($exits)) {
                throw new Exception("Impossible de supprimer la commande car elle a des sorties associées.");
            }
            
            // Supprimer les produits de la commande
            $db->delete('commande_produit', 'numero_commande = ?', [$id]);
            // Supprimer la commande
            $db->delete('commande', 'numero_commande = ?', [$id]);
            // Valider la transaction
            $db->getConnection()->commit();
            
            return true;
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $db->getConnection()->rollBack();
            throw $e;
        }
    }
    
    /**
     * Récupérer les produits d'une commande
     */
    public function getProducts($id) {
        return $this->db->fetchAll(
            "SELECT cp.*, p.designation
             FROM commande_produit cp
             JOIN produit p ON cp.id_produit = p.id_produit
             WHERE cp.numero_commande = ?",
            [$id]
        );
    }
    
    /**
     * Approuver une commande
     */
    public function approve($id) {
        return $this->db->update(
            'commande',
            ['statut' => ORDER_STATUS_APPROVED],
            'numero_commande = ?',
            [$id]
        );
    }
    
    /**
     * Rejeter une commande
     */
    public function reject($id) {
        return $this->db->update(
            'commande',
            ['statut' => ORDER_STATUS_REJECTED],
            'numero_commande = ?',
            [$id]
        );
    }
    
    /**
     * Mettre à jour le statut de paiement d'une commande
     */
    public function updatePaymentStatus($id, $status) {
        return $this->db->update(
            'commande',
            ['statut_paiement' => $status],
            'numero_commande = ?',
            [$id]
        );
    }
    
    /**
     * Récupérer les commandes par client
     */
    public function getByClient($clientId) {
        return $this->db->fetchAll(
            "SELECT c.*, cl.nom, cl.prenom
             FROM commande c
             JOIN client cl ON c.id_client = cl.id_client
             WHERE c.id_client = ?
             ORDER BY c.date DESC",
            [$clientId]
        );
    }
    
    /**
     * Récupérer les commandes par statut
     */
    public function getByStatus($status) {
        return $this->db->fetchAll(
            "SELECT c.*, cl.nom, cl.prenom
             FROM commande c
             JOIN client cl ON c.id_client = cl.id_client
             WHERE c.statut = ?
             ORDER BY c.date DESC",
            [$status]
        );
    }
    
    /**
     * Récupérer les commandes par statut de paiement
     */
    public function getByPaymentStatus($status) {
        return $this->db->fetchAll(
            "SELECT c.*, cl.nom, cl.prenom
             FROM commande c
             JOIN client cl ON c.id_client = cl.id_client
             WHERE c.statut_paiement = ?
             ORDER BY c.date DESC",
            [$status]
        );
    }
    
    /**
     * Récupérer les commandes par période
     */
    public function getByPeriod($startDate, $endDate) {
        return $this->db->fetchAll(
            "SELECT c.*, cl.nom, cl.prenom
             FROM commande c
             JOIN client cl ON c.id_client = cl.id_client
             WHERE c.date BETWEEN ? AND ?
             ORDER BY c.date DESC",
            [$startDate, $endDate]
        );
    }
    
    /**
     * Récupérer les statistiques des commandes par période
     */
    public function getStatsByPeriod($period = 'month') {
        $sql = "";
        
        switch ($period) {
            case 'day':
                $sql = "SELECT DATE(date) as period, COUNT(*) as count, SUM(montant) as total
                        FROM commande
                        GROUP BY DATE(date)
                        ORDER BY DATE(date) DESC
                        LIMIT 30";
                break;
            case 'week':
                $sql = "SELECT YEAR(date) as year, WEEK(date) as week, 
                               COUNT(*) as count, SUM(montant) as total
                        FROM commande
                        GROUP BY YEAR(date), WEEK(date)
                        ORDER BY YEAR(date) DESC, WEEK(date) DESC
                        LIMIT 12";
                break;
            case 'month':
            default:
                $sql = "SELECT YEAR(date) as year, MONTH(date) as month, 
                               COUNT(*) as count, SUM(montant) as total
                        FROM commande
                        GROUP BY YEAR(date), MONTH(date)
                        ORDER BY YEAR(date) DESC, MONTH(date) DESC
                        LIMIT 12";
                break;
        }
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Récupérer les statistiques des commandes par client
     */
    public function getStatsByClient() {
        return $this->db->fetchAll(
            "SELECT c.id_client, c.nom, c.prenom, COUNT(*) as count, SUM(co.montant) as total
             FROM commande co
             JOIN client c ON co.id_client = c.id_client
             GROUP BY c.id_client, c.nom, c.prenom
             ORDER BY total DESC
             LIMIT 10"
        );
    }
    
    /**
     * Récupérer les statistiques des commandes par produit
     */
    public function getStatsByProduct() {
        return $this->db->fetchAll(
            "SELECT p.id_produit, p.designation, SUM(cp.quantite) as total_quantity, 
                    SUM(cp.quantite * cp.prix_unitaire) as total_value
             FROM commande_produit cp
             JOIN produit p ON cp.id_produit = p.id_produit
             GROUP BY p.id_produit, p.designation
             ORDER BY total_value DESC
             LIMIT 10"
        );
    }
    
    /**
     * Générer une facture pour une commande
     */
    public function generateInvoice($id) {
        $order = $this->getById($id);
        $products = $this->getProducts($id);
        
        // Ici, vous pourriez générer un PDF ou simplement retourner les données
        return [
            'order' => $order,
            'products' => $products,
            'invoice_number' => generateReference('FAC'),
            'invoice_date' => date('Y-m-d H:i:s'),
            'total' => $order['montant'],
            'tax' => $order['montant'] * 0.2, // TVA à 20%
            'total_with_tax' => $order['montant'] * 1.2
        ];
    }
      /**
     * Récupérer les commandes par utilisateur
     */
    public function getByUserId($userId) {
      $query = "SELECT * FROM commandes WHERE user_id = :user_id ORDER BY date_commande DESC";
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
      $stmt->execute();
      
      return $stmt->fetchAll(\PDO::FETCH_ASSOC);
  }
    /**
     * Récupérer les détails d'une commande
     */
    public function getOrderDetails($orderId) {
      $query = "SELECT * FROM commande_details WHERE commande_id = :commande_id";
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':commande_id', $orderId, \PDO::PARAM_INT);
      $stmt->execute();
      
      return $stmt->fetchAll(\PDO::FETCH_ASSOC);
  }

     /**
     * Mettre à jour la date de livraison d'une commande
     */
    public function updateDeliveryDate($id, $date) {
      $query = "UPDATE commandes SET date_livraison = :date_livraison WHERE id = :id";
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':date_livraison', $date, \PDO::PARAM_STR);
      $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
      
      return $stmt->execute();
  }
  
  /**
   * Récupérer les commandes filtrées par date et statut
   */
  public function getFiltered($startDate, $endDate, $status = '') {
      $query = "SELECT * FROM commandes WHERE date_commande BETWEEN :start_date AND :end_date";
      
      if (!empty($status)) {
          $query .= " AND statut = :statut";
      }
      
      $query .= " ORDER BY date_commande DESC";
      
      $stmt = $this->db->prepare($query);
      $startDateTime = "{$startDate} 00:00:00";
      $endDateTime = "{$endDate} 23:59:59";
      $stmt->bindParam(':start_date', $startDateTime, \PDO::PARAM_STR);
      $stmt->bindParam(':end_date', $endDateTime, \PDO::PARAM_STR);
      
      if (!empty($status)) {
          $stmt->bindParam(':statut', $status, \PDO::PARAM_STR);
      }
      
      $stmt->execute();
      
      return $stmt->fetchAll(\PDO::FETCH_ASSOC);
  }
  
  /**
   * Récupérer les produits les plus commandés
   */
  public function getTopProducts($startDate, $endDate, $limit = 10) {
      $query = "SELECT p.id, p.nom, SUM(cd.quantite) as total_quantite, SUM(cd.quantite * cd.prix_unitaire) as total_montant
                FROM produits p
                JOIN commande_details cd ON p.id = cd.produit_id
                JOIN commandes c ON cd.commande_id = c.id
                WHERE c.date_commande BETWEEN :start_date AND :end_date
                GROUP BY p.id
                ORDER BY total_quantite DESC
                LIMIT :limit";
      
      $stmt = $this->db->prepare($query);
      $startDateTime = "{$startDate} 00:00:00";
      $endDateTime = "{$endDate} 23:59:59";
      $stmt->bindParam(':start_date', $startDate, \PDO::PARAM_STR);
      $stmt->bindParam(':end_date', $endDate , \PDO::PARAM_STR);
      $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
      $stmt->execute();
      
      return $stmt->fetchAll(\PDO::FETCH_ASSOC);
  }
  
  /**
   * Rechercher des commandes
   */
  public function search($keyword, $status = '', $startDate = '', $endDate = '') {
      $query = "SELECT c.*, u.nom, u.prenom 
                FROM commandes c
                JOIN users u ON c.user_id = u.id
                WHERE 1=1";
      
      $params = [];
      
      if (!empty($keyword)) {
          $query .= " AND (c.id LIKE :keyword OR u.nom LIKE :keyword OR u.prenom LIKE :keyword)";
          $params[':keyword'] = "%$keyword%";
      }
      
      if (!empty($status)) {
          $query .= " AND c.statut = :statut";
          $params[':statut'] = $status;
      }
      
      if (!empty($startDate)) {
          $query .= " AND c.date_commande >= :start_date";
          $params[':start_date'] = $startDate . ' 00:00:00';
      }
      
      if (!empty($endDate)) {
          $query .= " AND c.date_commande <= :end_date";
          $params[':end_date'] = $endDate . ' 23:59:59';
      }
      
      $query .= " ORDER BY c.date_commande DESC";
      
      $stmt = $this->db->prepare($query);
      
      foreach ($params as $key => $value) {
          $stmt->bindValue($key, $value);
      }
      
      $stmt->execute();
      
      return $stmt->fetchAll(\PDO::FETCH_ASSOC);
  }

   /**
     * Calculer les statistiques des commandes (suite)
     */
    public function getStats($startDate, $endDate) {
      $stats = [
          'total_commandes' => 0,
          'total_montant' => 0,
          'par_statut' => [],
          'par_jour' => []
      ];
      
      // Nombre total de commandes et montant total
      $query = "SELECT COUNT(*) as total, SUM(montant_total) as montant 
                FROM commandes 
                WHERE date_commande BETWEEN :start_date AND :end_date";
      $stmt = $this->db->prepare($query);
      $startDateTime = "{$startDate} 00:00:00";
      $endDateTime = "{$endDate} 23:59:59";
      $stmt->bindParam(':start_date', $startDate, \PDO::PARAM_STR);
      $stmt->bindParam(':end_date', $endDate, \PDO::PARAM_STR);
      $stmt->execute();
      
      $result = $stmt->fetch(\PDO::FETCH_ASSOC);
      $stats['total_commandes'] = $result['total'];
      $stats['total_montant'] = $result['montant'];
      
      // Commandes par statut
      $query = "SELECT statut, COUNT(*) as total, SUM(montant_total) as montant 
                FROM commandes 
                WHERE date_commande BETWEEN :start_date AND :end_date 
                GROUP BY statut";
      
      $stmt = $this->db->prepare($query);
      $startDateTime = "{$startDate} 00:00:00";
      $endDateTime = "{$endDate} 23:59:59";
      $stmt->bindParam(':start_date', $startDate, \PDO::PARAM_STR);
      $stmt->bindParam(':end_date', $endDate, \PDO::PARAM_STR);
      $stmt->execute();
      
      while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
          $stats['par_statut'][$row['statut']] = [
              'total' => $row['total'],
              'montant' => $row['montant']
          ];
      }
      
      // Commandes par jour
      $query = "SELECT DATE(date_commande) as jour, COUNT(*) as total, SUM(montant_total) as montant 
                FROM commandes 
                WHERE date_commande BETWEEN :start_date AND :end_date 
                GROUP BY DATE(date_commande) 
                ORDER BY jour";
      
      $stmt = $this->db->prepare($query);
      $startDateTime = "{$startDate} 00:00:00";
      $endDateTime = "{$endDate} 23:59:59";
      $stmt->bindParam(':start_date', $startDate, \PDO::PARAM_STR);
      $stmt->bindParam(':end_date', $endDate, \PDO::PARAM_STR);
      $stmt->execute();
      
      while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
          $stats['par_jour'][$row['jour']] = [
              'total' => $row['total'],
              'montant' => $row['montant']
          ];
      }
      
      return $stats;
  }

    
    /**
     * Récupérer le nombre de commandes par statut
     */
    public function getCountByStatus() {
      $query = "SELECT statut, COUNT(*) as total FROM commandes GROUP BY statut";
      $stmt = $this->db->prepare($query);
      $stmt->execute();
      
      $result = [];
      while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
          $result[$row['statut']] = $row['total'];
      }
      
      return $result;
  }

  /**
     * Scope a query to filter orders by a field and value, with optional ordering.
     *
     * @param  string  $field
     * @param  mixed  $value
     * @param  string|null  $orderByField
     * @param  string  $orderByDirection
     */
    public static function where(string $field, $value, ?string $orderByField = null, string $orderByDirection = 'ASC'): array
    {
        $sql = "SELECT * FROM " . self::$table . " WHERE {$field} = ?";
        $params = [$value];

        if ($orderByField) {
            $sql .= " ORDER BY " . $orderByField . " " . strtoupper($orderByDirection); // Ensure uppercase for direction
        }
        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC); // Fetch as associative array
    }
}
