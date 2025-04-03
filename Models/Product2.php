<?php
namespace App\Models;
use App\Core\Model;
use App\Utils\Database;

/**
 * Modèle Produit
 */
class Product extends Model{
    // private $db;
    protected static $table = 'produits';
      
    /**
     * Récupère tous les produits
     */
    public static function getAll() {
      $db = Database::getInstance();
      return $db->fetchAll("SELECT p.*, c.nom as categorie_nom 
                           FROM produits p 
                           LEFT JOIN categories c ON p.id_categorie = c.id 
                           ORDER BY p.designation ASC");
  }

  /**
   * 
   * Recupere les produits par categorie
   */

   public static function getAllByCategory($category) {
    $db = Database::getInstance();
    return $db->fetchAll("SELECT p.*, c.nom as categorie_nom 
                         FROM produits p 
                         LEFT JOIN categories c ON p.id_categorie = c.id 
                         WHERE c.name = $category
                         ORDER BY p.designation ASC");
}

   /**
     * Récupère tous les produits avec tri
     */
    public static function getAllSorted($sort = 'designation', $order = 'asc') {
      $db = Database::getInstance();
      
      // Liste des colonnes autorisées pour le tri
      $allowedColumns = ['designation', 'reference', 'prix_vente', 'quantite_stock', 'date_creation'];
      
      // Vérifier si la colonne est autorisée
      if (!in_array($sort, $allowedColumns)) {
          $sort = 'designation';
      }
      
      // Vérifier si l'ordre est valide
      $order = strtolower($order) === 'desc' ? 'DESC' : 'ASC';
      return $db->fetchAll("SELECT * FROM produits ORDER BY $sort $order");
  }
  
  /**
   * Récupère un produit par son ID
   */
  public static function getById($id) {
      $db = Database::getInstance();
      return $db->fetch("SELECT p.*, c.nom as categorie_nom 
                        FROM produits p 
                        LEFT JOIN categories c ON p.id_categorie = c.id 
                        WHERE p.id = ?", [$id]);
  }
     /**
     * Ajoute un nouveau produit
     */
    public static function add($data) {
      $db = Database::getInstance();
      $query = "INSERT INTO produits (reference, designation, description, prix_achat, prix_unitaire, 
                                    quantite_stock, id_categorie, date_creation) 
               VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
      $params = [
          $data['reference'],
          $data['designation'],
          $data['description'],
          $data['prix_achat'],
          $data['prix_unitaire'],
          $data['quantite_stock'],
          $data['id_categorie']
      ];
      
      $db->query($query, $params);
      return $db->getConnection()->lastInsertId();
  }
    
    /**
     * Mettre à jour un produit
     */
    /**
     * Met à jour un produit
     */
    public static function update($id, $data) {
      $db = Database::getInstance();
      $query = "UPDATE produits 
               SET reference = ?, 
                   designation = ?, 
                   description = ?, 
                   prix_achat = ?, 
                   prix_unitaire = ?, 
                   quantite_stock = ?, 
                   id_categorie = ?, 
                   date_modification = NOW() 
               WHERE id = ?";
      
      $params = [
          $data['reference'],
          $data['designation'],
          $data['description'],
          $data['prix_achat'],
          $data['prix_unitaire'],
          $data['quantite_stock'],
          $data['id_categorie'],
          $id
      ];
      
      return $db->query($query, $params);
  }
    
     /**
     * Supprime un produit
     */
    public static function delete($id) {
      $db = Database::getInstance();
      return $db->query("DELETE FROM produits WHERE id = ?", [$id]);
  }
  
  /**
   * Récupère les produits en rupture de stock
   */
  public static function getOutOfStock() {
      $db = Database::getInstance();
      return $db->fetchAll("SELECT p.*, c.nom as categorie_nom 
                           FROM produits p 
                           LEFT JOIN categories c ON p.id_categorie = c.id 
                           WHERE p.quantite_stock <= 0 
                           ORDER BY p.designation ASC");
  }

    /**
     * Récupère les produits les moins vendus
     */
    public static function getLeastSoldProducts($limit = 10, $period = 'month') {
      $db = Database::getInstance();
      $dateCondition = self::getDateConditionForPeriod($period, 'cp.date_ajout');
      
      $sql = "SELECT p.id, p.designation, p.reference, p.prix_unitaire, 
                     COALESCE(SUM(cp.quantite), 0) as total_vendu
              FROM produits p
              LEFT JOIN commande_produit cp ON p.id = cp.id_produit
              LEFT JOIN commandes c ON cp.id_commande = c.id
              WHERE c.statut != 'annulee' OR c.statut IS NULL
              " . ($dateCondition ? "AND $dateCondition" : "") . "
              GROUP BY p.id
              ORDER BY total_vendu ASC
              LIMIT ?";
      
      return $db->fetchAll($sql, [$limit]);
  }


     /**
     * Récupère les produits les plus rentables
     */
    public static function getMostProfitableProducts($limit = 10, $period = 'month') {
      $db = Database::getInstance();
      $dateCondition = self::getDateConditionForPeriod($period, 'cp.date_ajout');
      
      $sql = "SELECT p.id, p.designation, p.reference, p.prix_unitaire, 
                     COALESCE(SUM(cp.quantite), 0) as total_vendu,
                     COALESCE(SUM(cp.quantite * (p.prix_unitaire - p.prix_achat)), 0) as profit_total
              FROM produits p
              LEFT JOIN commande_produit cp ON p.id = cp.id_produit
              LEFT JOIN commandes c ON cp.id_commande = c.id
              WHERE (c.statut != 'annulee' OR c.statut IS NULL)
              " . ($dateCondition ? "AND $dateCondition" : "") . "
              GROUP BY p.id
              ORDER BY profit_total DESC
              LIMIT ?";
      
      return $db->fetchAll($sql, [$limit]);
  }
  
/**
* Génère la condition SQL pour filtrer par période
* 
* @param string $period Période ('month', 'quarter', 'year')
* @param string $dateField Nom du champ de date dans la requête
* @return string Condition SQL pour la période
*/
    private static function getDateConditionForPeriod($period, $dateField) {
      switch ($period) {
          case 'month':
              return "$dateField >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
          case 'quarter':
              return "$dateField >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
          case 'year':
              return "$dateField >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
          default:
              return "";
      }
  }
      /**
     * Recherche des produits
     */
    public static function search($keyword) {
      $db = Database::getInstance();
      $keyword = "%$keyword%";
      return $db->fetchAll("SELECT p.*, c.nom as categorie_nom 
                           FROM produits p 
                           LEFT JOIN categories c ON p.id_categorie = c.id 
                                OR p.description LIKE ? 
                             ORDER BY p.designation ASC", 
                             [$keyword, $keyword, $keyword]);
    }
    public static function searchAdvanced($keyword, $category, $minPrice, $maxPrice, $inStock) {
      return self::search($keyword);
    }
    /**
     * Récupère les mouvements récents de stock
     */
    public static function getRecentMovements($limit = 10) {
      $db = Database::getInstance();
      return $db->fetchAll("SELECT m.*, p.designation as produit_nom, 
                           CASE 
                               WHEN m.type = 'entree' THEN 'Entrée'
                               WHEN m.type = 'sortie' THEN 'Sortie'
                               ELSE m.type
                           END as type_libelle,
                           DATE_FORMAT(m.date_mouvement, '%d/%m/%Y %H:%i') as date_formatee
                           FROM mouvements_stock m
                           JOIN produits p ON m.id_produit = p.id
                           ORDER BY m.date_mouvement DESC
                           LIMIT ?", [$limit]);
  }

  /**
     * Ajoute un mouvement de stock
     */
    public static function addStockMovement($productId, $quantity, $type, $reason = null, $userId = null) {
      $db = Database::getInstance();
      
      // Commencer une transaction
      $db->beginTransaction();
      
      try {
          // Ajouter le mouvement
          $query = "INSERT INTO mouvements_stock (id_produit, quantite, type, motif, id_utilisateur, date_mouvement) 
                   VALUES (?, ?, ?, ?, ?, NOW())";
          
          $params = [
              $productId,
              $quantity,
              $type,
              $reason,
              $userId ?? $_SESSION['user_id'] ?? null
          ];
          
          $db->query($query, $params);
          
          // Mettre à jour le stock du produit
          if ($type === 'entree') {
              $updateQuery = "UPDATE produits SET quantite_stock = quantite_stock + ? WHERE id = ?";
          } else {
              $updateQuery = "UPDATE produits SET quantite_stock = quantite_stock - ? WHERE id = ?";
          }
          
          $db->query($updateQuery, [$quantity, $productId]);
          
          // Valider la transaction
          $db->commit();
          
          return true;
      } catch (\Exception $e) {
          // Annuler la transaction en cas d'erreur
          $db->rollback();
          throw $e;
      }
  }
    
    /**
     * Mettre à jour le stock d'un produit
     */
    // public function updateStock($id, $quantity, $type) {
    //     $product = $this->getById($id);
    //     if (!$product) {
    //         return false;
    //     }
    //     // $newQuantity = $product['quantite'];
    //     $newQuantity = ($type == '-') ? $product['quantite'] - $quantity : $product['quantite'] + $quantity;
        
    //     // Empêcher les quantités négatives
    //     if ($newQuantity < 0) {
    //         return false;
    //     }
    //     $product['quantite'] = $newQuantity;
    //     $db = Database::getInstance();
    //     return $db->update($id,$product);
    // }
    
    /**
     * Vérifier si un produit est en stock
     */
    public function isInStock($id, $quantity = 1) {
        $product = $this->getById($id);
        return $product && $product['quantite'] >= $quantity;
    }
    
    /**
     * Récupérer les produits avec un stock faible
     */
    public static function getLowStock($threshold = 10)
     {
      $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM produit WHERE quantite <= ? ORDER BY quantite",
            [$threshold]
        );
    }

      /**
     * Compte les produits avec un stock faible
     */
    public static function getLowStockCount() {
      $db = Database::getInstance();
      $result = $db->fetch("
          SELECT COUNT(*) as count FROM produits 
          WHERE quantite_stock <= quantite_alerte 
            AND quantite_stock > 0 
            AND statut = 'actif'
      ");
      return $result['count'];
  }
    
    /**
     * Calculer la valeur totale du stock
     */
    public static function getTotalStockValue() 
    {
      $db = Database::getInstance();
        return $db->fetch(
            "SELECT SUM(quantite * prix_achat) as total_value FROM produit"
        )['total_value'] ?? 0;
    }
    
    /**
     * Récupérer les produits les plus vendus
     */
    public static function getMostSold($limit = 10) {
      $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT p.*, SUM(o.nombre_produit) as total_sold 
             FROM produit p
             JOIN operation_produit op ON p.id_produit = op.id_produit
             JOIN operation o ON op.numero_operation = o.numero_operation
             WHERE o.type = 'sortie'
             GROUP BY p.id_produit
             ORDER BY total_sold DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function recordStockMovement($data) {
      return $this->db->insert('mouvement_stock', $data);
  }
  
  /**
   * Récupérer l'historique des mouvements d'un produit
   */
  public function getMovementHistory($productId) {
      return $this->db->fetchAll(
          "SELECT m.*, u.nom, u.prenom 
          FROM mouvement_stock m 
          LEFT JOIN utilisateur u ON m.id_utilisateur = u.id_utilisateur 
          WHERE m.id_produit = ? 
          ORDER BY m.date_mouvement DESC",
          [$productId]
      );
  }
  
  /**
   * Compter le nombre total de produits
   */
  public static function countProducts() {
    $db = Database::getInstance();
      $result = $db->fetch("SELECT COUNT(*) AS total FROM produit");
      return $result ? $result['total'] : 0;
  }
    /**
     * Récupère un produit par sa référence
     */
    public static function getByReference($reference) {
      $db = Database::getInstance();
      return $db->fetch("SELECT * FROM produits WHERE reference = ?", [$reference]);
  }

    /**
     * Vérifie si une référence existe déjà
     */
    public static function referenceExists($reference, $excludeId = null) {
      $db = Database::getInstance();
      $query = "SELECT COUNT(*) as count FROM produits WHERE reference = ?";
      $params = [$reference];
      
      if ($excludeId) {
          $query .= " AND id != ?";
          $params[] = $excludeId;
      }
      
      $result = $db->fetch($query, $params);
      return $result['count'] > 0;
  }
    /**
     * Récupère tous les produits d'un fournisseur
     */
    public static function getAllBySupplier($supplierId) {
      $db = Database::getInstance();
      return $db->fetchAll("
          SELECT * FROM produits 
          WHERE id_fournisseur = ?
          ORDER BY designation ASC
      ", [$supplierId]);
  }
  
  /**
   * Récupérer le produit le plus cher
   */
  public static function getMostExpensiveProduct() {
    $db = Database::getInstance();
      return $db->fetch("SELECT * FROM produit ORDER BY prix_vente DESC LIMIT 1");
  }
  
  /**
   * Récupérer le produit avec le plus grand stock
   */
  public static function getMostInStockProduct() {
    $db = Database::getInstance();
      return $db->fetch("SELECT * FROM produit ORDER BY quantite DESC LIMIT 1");
  }

    /**
     * Récupère les mouvements de stock d'un produit
     */
    public static function getStockMovements($productId) {
      $db = Database::getInstance();
      return $db->fetchAll("
          SELECT ms.*, u.nom as nom_utilisateur 
          FROM mouvements_stock ms
          LEFT JOIN utilisateurs u ON ms.id_utilisateur = u.id
          WHERE ms.id_produit = ?
          ORDER BY ms.date_mouvement DESC
      ", [$productId]);
  }

  /**
 * Récupère les statistiques des produits
 */
public static function getStats() {
  $db = Database::getInstance();
  $stats = [];
  
  // Nombre total de produits
  $result = $db->fetch("SELECT COUNT(*) as count FROM produits");
  $stats['total'] = $result['count'];
  
  // Nombre de produits par catégorie
  $categories = $db->fetchAll("
      SELECT c.nom, COUNT(p.id) as count 
      FROM categories c
      LEFT JOIN produits p ON c.id = p.id_categorie
      GROUP BY c.id
      ORDER BY count DESC
  ");
  $stats['categories'] = $categories;
  
  // Nombre de produits par statut
  $statuses = $db->fetchAll("
      SELECT statut, COUNT(*) as count 
      FROM produits 
      GROUP BY statut
  ");
  $stats['statuses'] = [];
  
  foreach ($statuses as $status) {
      $stats['statuses'][$status['statut']] = $status['count'];
  }
  
  // Produits en rupture de stock
  $result = $db->fetch("
      SELECT COUNT(*) as count 
      FROM produits 
      WHERE quantite_stock = 0 AND statut = 'actif'
  ");
  $stats['out_of_stock'] = $result['count'];
  
  // Produits avec stock faible
  $result = $db->fetch("
      SELECT COUNT(*) as count 
      FROM produits 
      WHERE quantite_stock <= quantite_alerte 
        AND quantite_stock > 0 
        AND statut = 'actif'
  ");
  $stats['low_stock'] = $result['count'];
  
  // Valeur totale du stock
  $result = $db->fetch("
      SELECT SUM(quantite_stock * prix_achat) as total_value 
      FROM produits
  ");
  $stats['stock_value'] = $result['total_value'] ?? 0;
  
  // Produits les plus chers
  $stats['most_expensive'] = $db->fetchAll("
      SELECT id, reference, designation, prix_vente 
      FROM produits 
      ORDER BY prix_vente DESC 
      LIMIT 5
  ");
  
  // Produits avec le plus de stock
  $stats['most_stock'] = $db->fetchAll("
      SELECT id, reference, designation, quantite_stock 
      FROM produits 
      ORDER BY quantite_stock DESC 
      LIMIT 5
  ");
  
  return $stats;
}

  

    
    /**
     * Récupérer les produits les plus vendus
     */
  //   public function getTopSelling($limit = 5) {
  //     $query = "SELECT p.*, SUM(od.quantite) as total_vendu 
  //               FROM produits p
  //               JOIN commande_details od ON p.id = od.produit_id
  //               JOIN commandes o ON od.commande_id = o.id
  //               WHERE o.statut = 'completed'
  //               GROUP BY p.id
  //               ORDER BY total_vendu DESC
  //               LIMIT :limit";
      
  //     $stmt = $this->db->prepare($query);
  //     $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
  //     $stmt->execute();
      
  //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
  // }

    /**
     * Récupérer les produits récemment ajoutés
     */
    public static function getRecent($limit = 5) {
      $db = Database::getInstance();
      return $db->fetchAll("
          SELECT * FROM produits 
          ORDER BY date_creation DESC 
          LIMIT ?
      ", [$limit]);
  }

  /**
 * Récupère les produits par plage de quantité
 */
public static function getByStockRange($min, $max) {
    $db = Database::getInstance();
    return $db->fetchAll("
        SELECT * FROM produits 
        WHERE quantite_stock BETWEEN ? AND ?
        ORDER BY quantite_stock ASC
    ", [$min, $max]);
}

/**
 * Récupère les produits par statut
 */
public static function getByStatus($status) {
    $db = Database::getInstance();
    return $db->fetchAll("
        SELECT * FROM produits 
        WHERE statut = ?
        ORDER BY designation ASC
    ", [$status]);
}

/**
 * Récupère les produits actifs
 */
public static function getActive() {
    $db = Database::getInstance();
    return $db->fetchAll("
        SELECT * FROM produits 
        WHERE statut = 'actif'
        ORDER BY designation ASC
    ");
}

}
