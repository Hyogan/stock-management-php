<?php
namespace App\Models;
use App\Core\Model;
use App\Models\Client;
use App\Models\Operation;
use App\Utils\Database;
use Exception;

/**
 * Modèle Entrée
 */
class Entry extends Operation {
    protected static $table = 'entrees_stock';

    /**
     * Récupérer toutes les entrées
     */
    public static function getAll($limit = null) {
        $db = Database::getInstance();
        $sql = "SELECT e.*,f.nom as nom_fournisseur, u.nom AS nom_utilisateur 
                FROM entrees_stock e
                LEFT JOIN utilisateurs u ON e.id_utilisateur = u.id
                LEFT JOIN fournisseurs f ON e.id_fournisseur = f.id
                ORDER BY e.date_entree DESC";
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            return $db->fetchAll($sql, [$limit]);
        }
        return $db->fetchAll(sql: $sql);
    }

    /**
     * Récupérer une entrée par son ID
     */
    public static function getById($id) {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT e.*, u.nom as nom_utilisateur
             FROM entrees_stock e
             LEFT JOIN utilisateurs u ON e.id_utilisateur = u.id
             WHERE e.id = ?",
            [$id]
        );
    }

    /**
     * Créer une nouvelle entrée
     */
    public static function create($data, $products = []) {
        $db = Database::getInstance();
        try {
            $db->beginTransaction();

            $entryId = $db->insert('entrees_stock', [
                'reference' => $data['reference'] ?? generateReference('ENT'),
                'date_entree' => $data['date_entree'],
                'id_fournisseur' => $data['id_fournisseur'],
                'id_utilisateur' => $data['id_utilisateur'],
                'montant_total' => $data['montant_total'],
                'notes' => $data['notes'],
                'statut' => $data['statut'] ?? 'en_attente',
                'date_creation' => date('Y-m-d H:i:s'),
            ]);

            if (!empty($products)) {
                foreach ($products as $product) {
                    $db->insert('details_entree_stock', [
                        'id_entree' => $entryId,
                        'id_produit' => $product['id_produit'],
                        'quantite' => $product['quantite'],
                        'prix_unitaire' => $product['prix_unitaire'],
                        'montant_total' => $product['quantite'] * $product['prix_unitaire'],
                    ]);

                    self::updateProductStock($product['id_produit'], $product['quantite'], 'entree',$db);
                }
            }

            $db->commit();
            return $entryId;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Mettre à jour une entrée
     */
    public static function update($id, $data, $products = null) {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $entryData = [
                'date_entree' => $data['date_entree'] ?? null,
                'id_fournisseur' => $data['id_fournisseur'] ?? null,
                'montant_total' => $data['montant_total'] ?? null,
                'notes' => $data['notes'] ?? null,
                'statut' => $data['statut'] ?? null,
                'date_modification' => date('Y-m-d H:i:s'),
            ];

            $entryData = array_filter($entryData, function ($value) {
                return $value !== null;
            });

            if (!empty($entryData)) {
                $db->update('entrees_stock', $entryData, 'id = ?', [$id]);
            }

            if ($products !== null) {
                $currentProducts = self::getProducts($id);
                foreach ($currentProducts as $product) {
                    self::updateProductStock($product['id_produit'], $product['quantite'], 'sortie',$db);
                }

                $db->delete('details_entree_stock', 'id_entree = ?', [$id]);

                foreach ($products as $product) {
                    $db->insert('details_entree_stock', [
                        'id_entree' => $id,
                        'id_produit' => $product['id_produit'],
                        'quantite' => $product['quantite'],
                        'prix_unitaire' => $product['prix_unitaire'],
                        'montant_total' => $product['quantite'] * $product['prix_unitaire'],
                    ]);

                    self::updateProductStock($product['id_produit'], $product['quantite'], 'entree',$db);
                }
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    protected static function updateProductStock($productId, $quantity, $type,$db) {
      if ($db === null) {
        $db = Database::getInstance();
      }
        return Product::addStockMovement($productId, $quantity, $type,null,null,$db);
    }

    /**
     * Supprimer une entrée
     */
    public static function delete($id) {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $currentProducts = self::getProducts($id);
            foreach ($currentProducts as $product) {
                self::updateProductStock($product['id_produit'], $product['quantite'], 'sortie',$db);
            }

            $db->delete('details_entree_stock', 'id_entree = ?', [$id]);
            $db->delete('entrees_stock', 'id = ?', [$id]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Récupérer les entrées par fournisseur
     */
    public static function getBySupplier($supplier) {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT e.*, u.nom as nom_utilisateur
             FROM entrees_stock e
             LEFT JOIN utilisateurs u ON e.id_utilisateur = u.id
             LEFT JOIN fournisseurs f ON e.id_fournisseur = f.id
             WHERE f.nom LIKE ?
             ORDER BY e.date_entree DESC",
            ["%{$supplier}%"]
        );
    }

    /**
     * Récupérer les entrées par période
     */
    public static function getByPeriod($startDate, $endDate, $type = null) {
        $db = Database::getInstance();
        $sql = "SELECT e.*, u.nom as nom_utilisateur 
                FROM entrees_stock e
                JOIN utilisateurs u ON e.id_utilisateur = u.id
                WHERE e.date_entree BETWEEN ? AND ?";

        $params = [$startDate, $endDate];

        if ($type !== null) {
            $sql .= " AND e.type_operation = ?";
            $params[] = $type;
        }

        $sql .= " ORDER BY e.date_entree DESC";

        return $db->fetchAll($sql, $params);
    }

    /**
     * Récupérer les statistiques des entrées par fournisseur
     */
    public static function getStatsBySupplier() {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT f.nom as fournisseur, COUNT(e.id) as count, SUM(des.montant_total) as total
             FROM entrees_stock e
             LEFT JOIN fournisseurs f ON e.id_fournisseur = f.id
             LEFT JOIN details_entree_stock des ON e.id = des.id_entree
             GROUP BY e.id_fournisseur
             ORDER BY total DESC"
        );
    }

    /**
     * Récupérer les statistiques des entrées par produit
     */
    public static function getStatsByProduct() {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT p.id as id_produit, p.designation, SUM(des.quantite) as total_quantity, 
                    SUM(des.montant_total) as total_value
             FROM entrees_stock e
             LEFT JOIN details_entree_stock des ON e.id = des.id_entree
             LEFT JOIN produits p ON des.id_produit = p.id
             GROUP BY des.id_produit
             ORDER BY total_value DESC"
        );
    }

    /**
     * Récupérer les produits d'une entrée
     */
    public static function getProducts($entryId) {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT des.*, p.designation, p.prix_vente, p.prix_achat 
             FROM details_entree_stock des
             JOIN produits p ON des.id_produit = p.id
             WHERE des.id_entree = ?",
            [$entryId]
        );
    }
}
