<?php
namespace App\Models;
use App\Core\Model;
use App\Models\Client;
use App\Models\Operation;
use App\Utils\Database;
use Exception;
/**
 * Modèle Sortie (hérite d'Opération)
 */
class ExitOp extends Operation {
    
    /**
     * Récupérer toutes les sorties
     */
    public static function getAll() {
      $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT o.*, s.id_sortie, s.client 
             FROM operation o
             JOIN sortie s ON o.numero_operation = s.id_sortie
             ORDER BY o.date DESC"
        );
    }
    
    /**
     * Récupérer une sortie par son ID
     */
    public function getById($id) {
        return $this->db->fetch(
            "SELECT o.*, s.id_sortie, s.client 
             FROM operation o
             JOIN sortie s ON o.numero_operation = s.id_sortie
             WHERE s.id_sortie = ?",
            [$id]
        );
    }
    
    /**
     * Créer une nouvelle sortie
     */
    /**
 * Créer une nouvelle sortie de stock
 */
  public function create($data, $products = null) 
  {
    try {
        // Démarrer une transaction
        $this->db->beginTransaction();
        
        // Insérer la sortie
        $exitId = $this->db->insert('sortie_stock', [
            'id_produit' => $data['id_produit'],
            'quantite' => $data['quantite'],
            'motif' => $data['motif'],
            'date_sortie' => $data['date_sortie'],
            'id_utilisateur' => $data['id_utilisateur']
        ]);
        
        // Mettre à jour le stock du produit
        $this->updateProductStock($data['id_produit'], $data['quantite'],'-');
        
        // Valider la transaction
        $this->db->commit();
        
        return $exitId;
    } catch (Exception $e) {
        // Annuler la transaction en cas d'erreur
        $this->db->rollback();
        throw $e;
    }
  }
  /**
 * Mettre à jour le stock d'un produit
 */
  protected function updateProductStock($productId, $quantity,$type) {
    $productModel = new Product();
    return $productModel->updateStock($productId, $quantity,$type);
}
      
    /**
     * Mettre à jour une sortie
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
            
            // Mettre à jour la sortie
            $exitData = [
                'client' => $data['client'] ?? null
            ];
            
            if (!empty(array_filter($exitData, function($value) { return $value !== null; }))) {
                $this->db->update('sortie', $exitData, 'id_sortie = ?', [$id]);
            }
            
            // Si des produits sont fournis, mettre à jour les produits
            if ($products !== null) {
                $productModel = new Product();
                
                // Récupérer les produits actuels pour annuler leur effet sur le stock
                $currentProducts = parent::getProducts($id);
                foreach ($currentProducts as $product) {
                    $productModel->updateStock($product['id_produit'], $product['quantite'],'+');
                }
                
                // Vérifier la disponibilité des nouveaux produits
                foreach ($products as $product) {
                    if (!$productModel->isInStock($product['id_produit'], $product['quantite'])) {
                        throw new Exception("Le produit ID {$product['id_produit']} n'est pas disponible en quantité suffisante.");
                    }
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
                    
                    // Mettre à jour le stock (diminuer)
                    $productModel->updateStock($product['id_produit'], $product['quantite'],'-');
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
    
    /**
     * Supprimer une sortie
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
            
            // Supprimer les liens avec les commandes
            $this->db->delete('commande_sortie', 'id_sortie = ?', [$id]);
            
            // Supprimer les livraisons associées
            $this->db->delete('livraison', 'id_sortie = ?', [$id]);
            
            // Supprimer la sortie
            $this->db->delete('sortie', 'id_sortie = ?', [$id]);
            
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
     * Lier une sortie à une commande
     */
    public function linkToOrder($exitId, $orderId) {
        return $this->db->insert('commande_sortie', [
            'numero_commande' => $orderId,
            'id_sortie' => $exitId
        ]);
    }
    
    /**
     * Créer un bon de livraison pour une sortie
     */
    public function createDelivery($exitId, $data) {
        $exit = $this->getById($exitId);
        
        $deliveryData = [
            'numero_livraison' => generateReference('LIV'),
            'date' => $data['delivery_date'] ?? date('Y-m-d H:i:s'),
            'montant' => $exit['prix'],
            'nombre_produit' => $exit['nombre_produit'],
            'type' => $data['delivery_type'] ?? 'standard',
            'id_sortie' => $exitId
        ];
        
        return $this->db->insert('livraison', $deliveryData);
    }
    
    /**
     * Récupérer les sorties par client
     */
    public function getByClient($client) {
        return $this->db->fetchAll(
            "SELECT o.*, s.id_sortie, s.client 
             FROM operation o
             JOIN sortie s ON o.numero_operation = s.id_sortie
             WHERE s.client LIKE ?
             ORDER BY o.date DESC",
            ["%{$client}%"]
        );
    }
    
    /**
     * Récupérer les sorties par commande
     */
    public function getByOrder($orderId) {
        return $this->db->fetchAll(
            "SELECT o.*, s.id_sortie, s.client 
             FROM operation o
             JOIN sortie s ON o.numero_operation = s.id_sortie
             JOIN commande_sortie cs ON s.id_sortie = cs.id_sortie
             WHERE cs.numero_commande = ?
             ORDER BY o.date DESC",
            [$orderId]
        );
    }
    
    /**
     * Récupérer les sorties par période
     */
    public function getByPeriod($startDate, $endDate, $type = null) {
      $sql = "SELECT s.*, p.designation, p.reference, u.nom as nom_utilisateur
              FROM sortie_stock s
              JOIN produit p ON s.id_produit = p.id_produit
              JOIN utilisateur u ON s.id_utilisateur = u.id_utilisateur
              WHERE s.date_sortie BETWEEN ? AND ?";
      
      $params = [$startDate, $endDate];
      
      // Si un type spécifique est demandé
      if ($type !== null) {
          $sql .= " AND s.type = ?";
          $params[] = $type;
      }
      
      $sql .= " ORDER BY s.date_sortie DESC";
      
      return $this->db->fetchAll($sql, $params);
  }
    
    /**
     * Récupérer les statistiques des sorties par client
     */
    public function getStatsByClient() {
        return $this->db->fetchAll(
            "SELECT s.client, COUNT(*) as count, SUM(o.prix) as total
             FROM operation o
             JOIN sortie s ON o.numero_operation = s.id_sortie
             GROUP BY s.client
             ORDER BY total DESC"
        );
    }
    
    /**
     * Récupérer les statistiques des sorties par produit
     */
    public function getStatsByProduct() {
        return $this->db->fetchAll(
            "SELECT p.id_produit, p.designation, SUM(op.quantite) as total_quantity, 
                    SUM(op.quantite * op.prix_unitaire) as total_value
             FROM operation o
             JOIN operation_produit op ON o.numero_operation = op.numero_operation
             JOIN produit p ON op.id_produit = p.id_produit
             JOIN sortie s ON o.numero_operation = s.id_sortie
             GROUP BY p.id_produit, p.designation
             ORDER BY total_value DESC"
        );
    }
    
    /**
     * Générer une facture pour une sortie
     */
    public function generateInvoice($exitId) {
        $exit = $this->getById($exitId);
        $products = parent::getProducts($exitId);
        
        // Ici, vous pourriez générer un PDF ou simplement retourner les données
        return [
            'exit' => $exit,
            'products' => $products,
            'invoice_number' => generateReference('FAC'),
            'invoice_date' => date('Y-m-d H:i:s'),
            'total' => $exit['prix'],
            'tax' => $exit['prix'] * 0.2, // TVA à 20%
            'total_with_tax' => $exit['prix'] * 1.2
        ];
    }
}
