<?php
namespace App\Models;
use App\Core\Model;
use App\Models\Client;
use App\Models\Operation;
use App\Utils\Database;
use Exception;
/**
 * Modèle Entrée (hérite d'Opération)
 */
class Entry extends Operation {
    
    /**
     * Récupérer toutes les entrées
     */
    public static function getAll() {
      $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT o.*, e.id_entree, e.fournisseur 
             FROM operation o
             JOIN entree e ON o.numero_operation = e.id_entree
             ORDER BY o.date DESC"
        );
    }
    
    /**
     * Récupérer une entrée par son ID
     */
    public function getById($id) {
        return $this->db->fetch(
            "SELECT o.*, e.id_entree, e.fournisseur 
             FROM operation o
             JOIN entree e ON o.numero_operation = e.id_entree
             WHERE e.id_entree = ?",
            [$id]
        );
    }
    
    /**
     * Créer une nouvelle entrée
     */
      public function create($data, $products = []) 
      {
        try {
            // Démarrer une transaction
            $this->db->beginTransaction();
            
            // Insérer l'entrée principale
            $entryId = $this->db->insert('entree_stock', [
                'date_entree' => $data['date_entree'],
                'fournisseur' => $data['fournisseur'],
                'reference_document' => $data['reference_document'],
                'notes' => $data['notes'],
                'id_utilisateur' => $data['id_utilisateur']
            ]);
            
            // Insérer les détails des produits
            if (!empty($products)) {
                foreach ($products as $product) {
                    $this->db->insert('detail_entree', [
                        'id_entree' => $entryId,
                        'id_produit' => $product['id_produit'],
                        'quantite' => $product['quantite'],
                        'prix_unitaire' => $product['prix_unitaire']
                    ]);
                    
                    // Mettre à jour le stock du produit
                    $this->updateProductStock($product['id_produit'], $product['quantite'],'+');
                }
            }
            
            // Valider la transaction
            $this->db->commit();
            
            return $entryId;
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->rollback();
            throw $e;
        }
      }
    
    /**
     * Mettre à jour une entrée
     */
    public function update($id, $data, $products = null) {
        // Commencer une transaction
        $this->db->getConnection()->beginTransaction();
        
        try {
            // Mettre à jour l'opération
            $operationData = [
                'date' => $data['date'] ?? null
            ];
            
            if ($products !== null) {
                $operationData['nombre_produit'] = count($products);
            }
            
            if (!empty($operationData)) {
                parent::update($id, $operationData);
            }
            
            // Mettre à jour l'entrée
            $entryData = [
                'fournisseur' => $data['fournisseur'] ?? null
            ];
            
            if (!empty(array_filter($entryData, function($value) { return $value !== null; }))) {
                $this->db->update('entree', $entryData, 'id_entree = ?', [$id]);
            }
            
            // Si des produits sont fournis, mettre à jour les produits
            if ($products !== null) {
                $productModel = new Product();
                
                // Récupérer les produits actuels pour annuler leur effet sur le stock
                $currentProducts = parent::getProducts($id);
                foreach ($currentProducts as $product) {
                    $productModel->updateStock($product['id_produit'], $product['quantite'],'-');
                }
                
                // Supprimer les produits actuels
                $this->db->delete('operation_produit', 'numero_operation = ?', [$id]);
                
                // Ajouter les nouveaux produits
                foreach ($products as $product) {
                    parent::addProduct(
                        $id,
                        $product['id_produit'],
                        $product['quantite'],
                        $product['prix_unitaire']
                    );
                    
                    // Mettre à jour le stock
                    $productModel->updateStock($product['id_produit'], $product['quantite'],'+');
                }
                
                // Mettre à jour le total de l'opération
                parent::updateTotal($id);
            }
            
            // Valider la transaction
            $this->db->getConnection()->commit();
            
            return true;
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->getConnection()->rollBack();
            throw $e;
        }
    }

    protected function updateProductStock($productId, $quantity,$type) {
      $productModel = new Product();
      return $productModel->updateStock($productId, $quantity, $type);
  }
    
    /**
     * Supprimer une entrée
     */
    public function delete($id) {
        // Commencer une transaction
        $this->db->getConnection()->beginTransaction();
        
        try {
            $productModel = new Product();
            
            // Récupérer les produits pour annuler leur effet sur le stock
            $products = parent::getProducts($id);
            foreach ($products as $product) {
                $productModel->updateStock($product['id_produit'], $product['quantite'],'-');
            }
            
            // Supprimer l'entrée
            $this->db->delete('entree', 'id_entree = ?', [$id]);
            
            // Supprimer l'opération et ses produits associés
            parent::delete($id);
            
            // Valider la transaction
            $this->db->getConnection()->commit();
            
            return true;
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->getConnection()->rollBack();
            throw $e;
        }
    }
    
    /**
     * Récupérer les entrées par fournisseur
     */
    public function getBySupplier($supplier) {
        return $this->db->fetchAll(
            "SELECT o.*, e.id_entree, e.fournisseur 
             FROM operation o
             JOIN entree e ON o.numero_operation = e.id_entree
             WHERE e.fournisseur LIKE ?
             ORDER BY o.date DESC",
            ["%{$supplier}%"]
        );
    }

    /**
 * Récupérer les entrées par période
 */
  public function getByPeriod($startDate, $endDate, $type = null) {
    $sql = "SELECT e.*, u.nom as nom_utilisateur 
            FROM entree_stock e
            JOIN utilisateur u ON e.id_utilisateur = u.id_utilisateur
            WHERE e.date_entree BETWEEN ? AND ?";
    
    $params = [$startDate, $endDate];
    
    // Si un type spécifique est demandé
    if ($type !== null) {
        $sql .= " AND e.type = ?";
        $params[] = $type;
    }
    
    $sql .= " ORDER BY e.date_entree DESC";
    
    return $this->db->fetchAll($sql, $params);
  }
    
    /**
     * Récupérer les statistiques des entrées par fournisseur
     */
    public function getStatsBySupplier() {
        return $this->db->fetchAll(
            "SELECT e.fournisseur, COUNT(*) as count, SUM(o.prix) as total
             FROM operation o
             JOIN entree e ON o.numero_operation = e.id_entree
             GROUP BY e.fournisseur
             ORDER BY total DESC"
        );
    }
    
    /**
     * Récupérer les statistiques des entrées par produit
     */
    public function getStatsByProduct() {
        return $this->db->fetchAll(
            "SELECT p.id_produit, p.designation, SUM(op.quantite) as total_quantity, 
                    SUM(op.quantite * op.prix_unitaire) as total_value
             FROM operation o
             JOIN operation_produit op ON o.numero_operation = op.numero_operation
             JOIN produit p ON op.id_produit = p.id_produit
             JOIN entree e ON o.numero_operation = e.id_entree
             GROUP BY p.id_produit, p.designation
             ORDER BY total_value DESC"
        );
    }
}
