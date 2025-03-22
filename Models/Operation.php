<?php
/**
 * Modèle Opération
 */
namespace App\Models;
use App\Utils\Database;

class Operation {
    protected $db;
    protected static $table = 'operations_stock';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Récupérer toutes les opérations
     */
    public static function getAll($limit = null) {
        $db = Database::getInstance();
        if($limit)
          return $db->fetchAll("SELECT * FROM operations_stock ORDER BY date_operation DESC LIMIT $limit");
        else
          return $db->fetchAll("SELECT * FROM operations_stock ORDER BY date_operation DESC");
  }

    /**
     * Récupérer une opération par son ID
     */
    public static function getById($id) {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM operations_stock WHERE id = ?", [$id]);
    }

    /**
     * Créer une nouvelle opération
     */
    public static function create($data) {
        $db = Database::getInstance();
        if (!isset($data['date_operation'])) {
            $data['date_operation'] = date('Y-m-d H:i:s');
        }
        return $db->insert('operations_stock', $data);
    }

    /**
     * Mettre à jour une opération
     */
    public static function update($id, $data) {
        $db = Database::getInstance();
        return $db->update('operations_stock', $data, 'id = ?', [$id]);
    }

    /**
     * Supprimer une opération
     */
    public static function delete($id) {
        $db = Database::getInstance();
        return $db->delete('operations_stock', 'id = ?', [$id]);
    }

    /**
     * Ajouter un produit à une opération
     */
    public static function addProduct($operationId, $productId, $quantity, $price) {
        $db = Database::getInstance();
        return $db->insert('details_commande', [ // ou details_entree_stock ou details_sortie_stock
            'id_commande' => $operationId, // ou id_entree ou id_sortie
            'id_produit' => $productId,
            'quantite' => $quantity,
            'prix_unitaire' => $price,
            'montant_total' => $quantity * $price,
        ]);
    }

    /**
     * Récupérer l'utilisateur associé à l'opération
     */
    public static function getUser($operationId) {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT u.* FROM utilisateurs u
             JOIN operations_stock o ON u.id = o.id_utilisateur
             WHERE o.id = ?",
            [$operationId]
        );
    }

    /**
     * Récupérer les produits d'une opération
     */
    public static function getProducts($operationId) {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT dc.*, p.designation, p.prix_vente, p.prix_achat 
             FROM details_commande dc
             JOIN produits p ON dc.id_produit = p.id
             WHERE dc.id_commande = ?", // ou id_entree ou id_sortie
            [$operationId]
        );
    }

    /**
     * Filtrer les opérations selon des critères
     */
    public static function filter($criteria = [], $startDate = null, $endDate = null, $type = null) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM operations_stock";
        $params = [];
        $conditions = [];

        if (!empty($criteria)) {
            foreach ($criteria as $key => $value) {
                if ($value !== null) {
                    $conditions[] = "$key = ?";
                    $params[] = $value;
                }
            }
        }

        if ($startDate && $endDate) {
            $conditions[] = "date_operation BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        if ($type !== null) {
            $conditions[] = "type_operation = ?";
            $params[] = $type;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY date_operation DESC";

        return $db->fetchAll($sql, $params);
    }

    /**
     * Calculer le total d'une opération
     */
    public static function calculateTotal($operationId) {
        $db = Database::getInstance();
        $result = $db->fetch(
            "SELECT SUM(quantite * prix_unitaire) as total 
             FROM details_commande 
             WHERE id_commande = ?", // ou id_entree ou id_sortie
            [$operationId]
        );

        return $result['total'] ?? 0;
    }

    /**
     * Mettre à jour le total d'une opération
     */
    public static function updateTotal($operationId) {
        $db = Database::getInstance();
        $total = self::calculateTotal($operationId);

        return $db->update(
            'commandes', // ou entrees_stock ou sorties_stock
            ['montant_total' => $total],
            'id = ?', // ou id
            [$operationId]
        );
    }

    /**
     * Récupérer les opérations par type
     */
    public static function getByType($type) {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM operations_stock WHERE type_operation = ? ORDER BY date_operation DESC",
            [$type]
        );
    }

    /**
     * Récupérer les opérations par période
     */
    public static function getByPeriod($startDate, $endDate, $type = null) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM operations_stock WHERE date_operation BETWEEN ? AND ?";
        $params = [$startDate, $endDate];

        if ($type !== null) {
            $sql .= " AND type_operation = ?";
            $params[] = $type;
        }

        $sql .= " ORDER BY date_operation DESC";

        return $db->fetchAll($sql, $params);
    }

    /**
     * Récupérer les statistiques des opérations
     */
    public static function getStats($period = 'month') {
        $db = Database::getInstance();
        $sql = "";

        switch ($period) {
            case 'day':
                $sql = "SELECT DATE(date_operation) as period, COUNT(*) as count, type_operation 
                       FROM operations_stock 
                       WHERE date_operation >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)  GROUP BY DATE(date_operation), type_operation 
                       ORDER BY DATE(date_operation)";
                break;
            case 'month':
                $sql = "SELECT DATE_FORMAT(date_operation, '%Y-%m') as period, COUNT(*) as count, type_operation 
                       FROM operations_stock 
                       WHERE date_operation >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) 
                       GROUP BY DATE_FORMAT(date_operation, '%Y-%m'), type_operation 
                       ORDER BY period";
                break;
            case 'year':
                $sql = "SELECT YEAR(date_operation) as period, COUNT(*) as count, type_operation 
                       FROM operations_stock 
                       GROUP BY YEAR(date_operation), type_operation 
                       ORDER BY period";
                break;
        }

        return $db->fetchAll($sql);
    }

}

