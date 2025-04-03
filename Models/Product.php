<?php
namespace App\Models;
use App\Core\Model;
use App\Utils\Database;

/**
 * Modèle Produit
 */
class Product extends Model {
    protected static $table = 'produits';

    /**
     * Récupère tous les produits
     */
    public static function getAll() {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT p.*, c.nom as categorie_nom, f.nom as fournisseur_nom
                             FROM produits p 
                             LEFT JOIN categories c ON p.id_categorie = c.id 
                             LEFT JOIN fournisseurs f ON p.id_fournisseur = f.id
                             ORDER BY p.designation ASC");
    }

    /**
     * Récupère les produits par catégorie
     */
    public static function getAllByCategory($categoryId) {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT p.*, c.nom as categorie_nom 
                             FROM produits p 
                             LEFT JOIN categories c ON p.id_categorie = c.id 
                             WHERE p.id_categorie = ?
                             ORDER BY p.designation ASC", [$categoryId]);
    }

    /**
     * Récupère tous les produits avec tri
     */
    public static function getAllSorted($sort = 'designation', $order = 'asc') {
        $db = Database::getInstance();
        $allowedColumns = ['designation', 'reference', 'prix_vente', 'quantite_stock', 'date_creation'];
        if (!in_array($sort, $allowedColumns)) {
            $sort = 'designation';
        }
        $order = strtolower($order) === 'desc' ? 'DESC' : 'ASC';
        return $db->fetchAll("SELECT p.*, c.nom as categorie_nom, f.nom as fournisseur_nom
                             FROM produits p 
                             LEFT JOIN categories c ON p.id_categorie = c.id 
                             LEFT JOIN fournisseurs f ON p.id_fournisseur = f.id
                             ORDER BY p.$sort $order");
    }

    /**
     * Récupère un produit par son ID
     */
    public static function getById($id) {
        $db = Database::getInstance();
        return $db->fetch("SELECT p.*, c.nom as categorie_nom, f.nom as fournisseur_nom
                          FROM produits p 
                          LEFT JOIN categories c ON p.id_categorie = c.id 
                          LEFT JOIN fournisseurs f ON p.id_fournisseur = f.id
                          WHERE p.id = ?", [$id]);
    }

    /**
     * Ajoute un nouveau produit
     */
    public static function add($data) {
        $db = Database::getInstance();
        $query = "INSERT INTO produits (reference, designation, description, prix_achat, 
                                      prix_vente, quantite_stock, quantite_alerte, id_categorie, 
                                      id_fournisseur, unite, image, statut, date_creation) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $params = [
            $data['reference'],
            $data['designation'],
            $data['description'] ?? null,
            $data['prix_achat'] ?? null,
            $data['prix_vente'],
            $data['quantite_stock'] ?? 0,
            $data['quantite_alerte'] ?? 5,
            $data['id_categorie'] ?? null,
            $data['id_fournisseur'] ?? null,
            $data['unite'] ?? 'qt',
            $data['image'] ?? null,
            $data['statut'] ?? 'actif'
        ];
        $db->query($query, $params);
        return $db->lastInsertId();
    }

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
                      prix_vente = ?, 
                      quantite_alerte = ?, 
                      id_categorie = ?,
                      id_fournisseur = ?,
                      unite = ?,
                      image = COALESCE(?, image),
                      statut = ?,
                      date_modification = NOW() 
                  WHERE id = ?";
        $params = [
            $data['reference'],
            $data['designation'],
            $data['description'] ?? null,
            $data['prix_achat'] ?? null,
            $data['prix_vente'],
            $data['quantite_alerte'] ?? 5,
            $data['id_categorie'] ?? null,
            $data['id_fournisseur'] ?? null,
            $data['unite'] ?? null,
            $data['image'] ?? null,
            $data['statut'] ?? 'actif',
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
     * Recherche des produits
     */
    public static function search($keyword) {
        $db = Database::getInstance();
        $keyword = "%$keyword%";
        return $db->fetchAll("SELECT p.*, c.nom as categorie_nom 
                             FROM produits p 
                             LEFT JOIN categories c ON p.id_categorie = c.id 
                             WHERE p.designation LIKE ? 
                                OR p.reference LIKE ? 
                                OR p.description LIKE ? 
                             ORDER BY p.designation ASC", 
                             [$keyword, $keyword, $keyword]);
    }

    /**
     * Recherche avancée de produits
     */
    public static function searchAdvanced($keyword, $categoryId, $minPrice, $maxPrice, $inStock) {
        $db = Database::getInstance();
        $conditions = [];
        $params = [];

        if (!empty($keyword)) {
            $keyword = "%$keyword%";
            $conditions[] = "(p.designation LIKE ? OR p.reference LIKE ? OR p.description LIKE ?)";
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        if (!empty($categoryId)) {
            $conditions[] = "p.id_categorie = ?";
            $params[] = $categoryId;
        }

        if ($minPrice > 0) {
            $conditions[] = "p.prix_vente >= ?";
            $params[] = $minPrice;
        }

        if ($maxPrice > 0) {
            $conditions[] = "p.prix_vente <= ?";
            $params[] = $maxPrice;
        }

        if ($inStock == 1) {
            $conditions[] = "p.quantite_stock > 0";
        } elseif ($inStock == 0) {
            $conditions[] = "p.quantite_stock <= 0";
        }

        $whereClause = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";

        return $db->fetchAll("SELECT p.*, c.nom as categorie_nom 
                             FROM produits p 
                             LEFT JOIN categories c ON p.id_categorie = c.id 
                             $whereClause
                             ORDER BY p.designation ASC", $params);
    }

    /**
     * Récupère les mouvements récents de stock
     */
    public static function getRecentMovements($limit = 10) {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT os.*, p.designation as produit_nom, u.nom as nom_utilisateur, u.prenom as prenom_utilisateur,
                             CASE 
                                 WHEN os.type_operation = 'entry' THEN 'Entrée'
                                 WHEN os.type_operation = 'exit' THEN 'Sortie'
                                 ELSE os.type_operation
                             END as type_libelle,
                             DATE_FORMAT(os.date_operation, '%d/%m/%Y %H:%i') as date_formatee
                             FROM operations_stock os
                             JOIN produits p ON os.id_produit = p.id
                             LEFT JOIN utilisateurs u ON os.id_utilisateur = u.id
                             ORDER BY os.date_operation DESC
                             LIMIT ?", [$limit]);
    }

    /**
     * Ajoute un mouvement de stock
     */
    public static function addStockMovement($productId, $quantity, $type, $reason = null, $userId = null,$db = null) {
        // $db = Database::getInstance();
        if ($db === null) {
          $db = Database::getInstance();
        }
        try {
            // Insérer dans operations_stock
            $query = "INSERT INTO operations_stock (id_produit, quantite, type_operation, motif, id_utilisateur, date_operation) 
                      VALUES (?, ?, ?, ?, ?, NOW())";
            $params = [
                $productId,
                $quantity,
                $type === 'entree' ? 'entry' : 'exit',
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
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Vérifier si un produit est en stock
     */
    public static function isInStock($id, $quantity): bool
    {
        $product = self::getById($id);
        // dd([$product['quantite_stock'],$quantity,(bool)$product['quantite_stock'] < $quantity] ) ;
        return (bool)$product && $product['quantite_stock'] > $quantity;
    }

    /**
     * Récupérer les produits avec un stock faible
     */
    public static function getLowStock() {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT p.*, c.nom as categorie_nom 
             FROM produits p
             LEFT JOIN categories c ON p.id_categorie = c.id
             WHERE p.quantite_stock <= p.quantite_alerte 
             AND p.quantite_stock > 0 
             AND p.statut = 'actif'
             ORDER BY p.quantite_stock ASC"
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
    public static function getTotalStockValue() {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT SUM(quantite_stock * prix_achat) as total_value FROM produits"
        )['total_value'] ?? 0;
    }

    /**
     * Compter le nombre total de produits
     */
    public static function countProducts() {
        $db = Database::getInstance();
        $result = $db->fetch("SELECT COUNT(*) AS total FROM produits");
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
            SELECT p.*, c.nom as categorie_nom
            FROM produits p
            LEFT JOIN categories c ON p.id_categorie = c.id
            WHERE p.id_fournisseur = ?
            ORDER BY p.designation ASC
        ", [$supplierId]);
    }

     /**
     * Récupérer les produits les plus vendus
     */
    public static function getMostSold($limit = 10) {
      $db = Database::getInstance();
      return $db->fetchAll(
          "SELECT p.*, SUM(ds.quantite) as total_sold 
           FROM produits p
           JOIN details_sortie_stock ds ON p.id = ds.id_produit
           JOIN sorties_stock ss ON ds.id_sortie = ss.id
           WHERE ss.type_sortie = 'vente' 
           GROUP BY p.id
           ORDER BY total_sold DESC
           LIMIT ?",
          [$limit]
      );
  }

      /**
     * Récupère les produits les moins vendus
     */

public static function getLeastSoldProducts($limit = 10, $period = 'month') {
    $db = Database::getInstance();
    $dateCondition = self::getDateConditionForPeriod($period, 'ss.date_sortie');

    $sql = "SELECT p.id, p.designation, p.reference, p.prix_vente, 
                   COALESCE(SUM(ds.quantite), 0) as total_vendu
            FROM produits p
            LEFT JOIN details_sortie_stock ds ON p.id = ds.id_produit
            LEFT JOIN sorties_stock ss ON ds.id_sortie = ss.id
            WHERE ss.type_sortie = 'vente' 
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
    $dateCondition = self::getDateConditionForPeriod($period, 'ss.date_sortie');

    $sql = "SELECT p.id, p.designation, p.reference, p.prix_vente, 
                   COALESCE(SUM(ds.quantite), 0) as total_vendu,
                   COALESCE(SUM(ds.quantite * (p.prix_vente - p.prix_achat)), 0) as profit_total
            FROM produits p
            LEFT JOIN details_sortie_stock ds ON p.id = ds.id_produit
            LEFT JOIN sorties_stock ss ON ds.id_sortie = ss.id
            WHERE ss.type_sortie = 'vente'
            " . ($dateCondition ? "AND $dateCondition" : "") . "
            GROUP BY p.id
            ORDER BY profit_total DESC
            LIMIT ?";

    return $db->fetchAll($sql, [$limit]);
}

    /**
     * Récupérer le produit le plus cher
     */
    public static function getMostExpensiveProduct() {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM produits ORDER BY prix_vente DESC LIMIT 1");
    }

    /**
     * Récupérer le produit avec le plus grand stock
     */
    public static function getMostInStockProduct() {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM produits ORDER BY quantite_stock DESC LIMIT 1");
    }

    /**
     * Récupère les mouvements de stock d'un produit
     */
    public static function getStockMovements($productId) {
        $db = Database::getInstance();
        return $db->fetchAll("
            SELECT os.*, u.nom as nom_utilisateur, u.prenom as prenom_utilisateur,
            CASE 
                WHEN os.type_operation = 'entry' THEN 'Entrée'
                WHEN os.type_operation = 'exit' THEN 'Sortie'
                ELSE os.type_operation
            END as type_libelle
            FROM operations_stock os
            LEFT JOIN utilisateurs u ON os.id_utilisateur = u.id
            WHERE os.id_produit = ?
            ORDER BY os.date_operation DESC
        ", [$productId]);
    }

    /**
     * Récupère les statistiques des produits
     */
    public static function getStats() {
        $db = Database::getInstance();
        $stats = [];
        
        // Total products
        $result = $db->fetch("SELECT COUNT(*) as count FROM produits");
        $stats['total'] = $result['count'];
        
        // Products by category
        $stats['categories'] = $db->fetchAll("
            SELECT c.nom, COUNT(p.id) as count 
            FROM categories c
            LEFT JOIN produits p ON c.id = p.id_categorie
            GROUP BY c.id
            ORDER BY count DESC
        ");
        
        // Products by status
        $statuses = $db->fetchAll("
            SELECT statut, COUNT(*) as count 
            FROM produits 
            GROUP BY statut
        ");
        $stats['statuses'] = [];
        foreach ($statuses as $status) {
            $stats['statuses'][$status['statut']] = $status['count'];
        }
        
        // Out of stock products
        $result = $db->fetch("
            SELECT COUNT(*) as count 
            FROM produits 
            WHERE quantite_stock = 0 AND statut = 'actif'
        ");
        $stats['out_of_stock'] = $result['count'];
        
        // Low stock products
        $result = $db->fetch("
            SELECT COUNT(*) as count 
            FROM produits 
            WHERE quantite_stock <= quantite_alerte 
              AND quantite_stock > 0 
              AND statut = 'actif'
        ");
        $stats['low_stock'] = $result['count'];
        
        // Total stock value
        $result = $db->fetch("
            SELECT SUM(quantite_stock * prix_achat) as total_value 
            FROM produits
        ");
        $stats['stock_value'] = $result['total_value'] ?? 0;
        
        // Most expensive products
        $stats['most_expensive'] = $db->fetchAll("
            SELECT id, reference, designation, prix_vente 
            FROM produits 
            ORDER BY prix_vente DESC 
            LIMIT 5
        ");
        
        // Products with most stock
        $stats['most_stock'] = $db->fetchAll("
            SELECT id, reference, designation, quantite_stock 
            FROM produits 
            ORDER BY quantite_stock DESC 
            LIMIT 5
        ");
        
        return $stats;
    }

    /**
     * Récupérer les produits récemment ajoutés
     */
    public static function getRecent($limit = 5) {
        $db = Database::getInstance();
        return $db->fetchAll("
            SELECT p.*, c.nom as categorie_nom 
            FROM produits p
            LEFT JOIN categories c ON p.id_categorie = c.id
            ORDER BY p.date_creation DESC 
            LIMIT ?
        ", [$limit]);
    }

    /**
     * Récupère les produits par plage de quantité
     */
    public static function getByStockRange($min, $max) {
        $db = Database::getInstance();
        return $db->fetchAll("
            SELECT p.*, c.nom as categorie_nom 
            FROM produits p
            LEFT JOIN categories c ON p.id_categorie = c.id
            WHERE p.quantite_stock BETWEEN ? AND ?
            ORDER BY p.quantite_stock ASC
        ", [$min, $max]);
    }

    /**
     * Récupère les produits par statut
     */
    public static function getByStatus($status) {
        $db = Database::getInstance();
        return $db->fetchAll("
            SELECT p.*, c.nom as categorie_nom 
            FROM produits p
            LEFT JOIN categories c ON p.id_categorie = c.id
            WHERE p.statut = ?
            ORDER BY p.designation ASC
        ", [$status]);
    }

    /**
     * Récupère les produits actifs
     */
    public static function getActive() {
        $db = Database::getInstance();
        return $db->fetchAll("
            SELECT p.*, c.nom as categorie_nom 
            FROM produits p
            LEFT JOIN categories c ON p.id_categorie = c.id
            WHERE p.statut = 'actif'
            ORDER BY p.designation ASC
        ");
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
}
