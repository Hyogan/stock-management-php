<?php
namespace App\Models;
use App\Core\Model;
use App\Models\Client;
use App\Models\Operation;
use App\Utils\Database;
use Exception;

/**
 * Modèle Sortie
 */
class ExitOp extends Operation {
    protected static $table = 'sorties_stock';

    /**
     * Récupérer toutes les sorties
     */
    public static function getAll() {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT s.*, u.nom as nom_utilisateur 
             FROM sorties_stock s
             LEFT JOIN utilisateurs u ON s.id_utilisateur = u.id
             ORDER BY s.date_sortie DESC"
        );
    }

    /**
     * Récupérer une sortie par son ID
     */
    public static function getById($id) {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT s.*, u.nom as nom_utilisateur 
             FROM sorties_stock s
             LEFT JOIN utilisateurs u ON s.id_utilisateur = u.id
             WHERE s.id = ?",
            [$id]
        );
    }

    /**
     * Créer une nouvelle sortie de stock
     */
    public static function create($data, $products = null) {
        $db = Database::getInstance();
        try {
            $db->beginTransaction();

            $exitId = $db->insert('sorties_stock', [
                'reference' => $data['reference'] ?? generateReference('SOR'),
                'id_produit' => $data['id_produit'],
                'quantite' => $data['quantite'],
                'motif' => $data['motif'],
                'date_sortie' => $data['date_sortie'],
                'id_utilisateur' => $data['id_utilisateur'],
                'date_creation' => date('Y-m-d H:i:s'),
            ]);

            self::updateProductStock($data['id_produit'], $data['quantite'], '-');

            $db->commit();
            return $exitId;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Mettre à jour le stock d'un produit
     */
    protected static function updateProductStock($productId, $quantity, $type) {
        $productModel = new Product();
        return $productModel->updateStock($productId, $quantity, $type);
    }

    /**
     * Mettre à jour une sortie
     */
    public static function update($id, $data, $products = null) {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $exitData = [
                'id_produit' => $data['id_produit'] ?? null,
                'quantite' => $data['quantite'] ?? null,
                'motif' => $data['motif'] ?? null,
                'date_sortie' => $data['date_sortie'] ?? null,
                'id_utilisateur' => $data['id_utilisateur'] ?? null,
                'date_modification' => date('Y-m-d H:i:s'),
            ];

            $exitData = array_filter($exitData, function ($value) {
                return $value !== null;
            });

            if (!empty($exitData)) {
                $db->update('sorties_stock', $exitData, 'id = ?', [$id]);
            }

            if ($products !== null) {
                $currentProducts = self::getProducts($id);
                foreach ($currentProducts as $product) {
                    self::updateProductStock($product['id_produit'], $product['quantite'], '+');
                }

                $db->delete('details_sortie_stock', 'id_sortie = ?', [$id]);

                foreach ($products as $product) {
                    if (!Product::isInStock($product['id_produit'], $product['quantite'])) {
                        throw new Exception("Le produit ID {$product['id_produit']} n'est pas disponible en quantité suffisante.");
                    }

                    $db->insert('details_sortie_stock', [
                        'id_sortie' => $id,
                        'id_produit' => $product['id_produit'],
                        'quantite' => $product['quantite'],
                        'prix_unitaire' => $product['prix_unitaire'],
                        'montant_total' => $product['quantite'] * $product['prix_unitaire'],
                    ]);

                    self::updateProductStock($product['id_produit'], $product['quantite'], '-');
                }
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Supprimer une sortie
     */
    public static function delete($id) {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $currentProducts = self::getProducts($id);
            foreach ($currentProducts as $product) {
                self::updateProductStock($product['id_produit'], $product['quantite'], '+');
            }

            $db->delete('details_sortie_stock', 'id_sortie = ?', [$id]);
            $db->delete('sorties_stock', 'id = ?', [$id]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Lier une sortie à une commande
     */
    public static function linkToOrder($exitId, $orderId) {
        $db = Database::getInstance();
        return $db->insert('commande_sortie', [
            'id_commande' => $orderId,
            'id_sortie' => $exitId
        ]);
    }

    /**
     * Créer un bon de livraison pour une sortie
     */
    public static function createDelivery($exitId, $data)
    {
      $db = Database::getInstance();
      $exit = self::getById($exitId);

      $deliveryData = [
          'reference' => $data['reference'] ?? generateReference('LIV'),
          'date_livraison' => $data['date_livraison'] ?? date('Y-m-d H:i:s'),
          'montant_total' => self::calculateTotal($exitId),
          'id_sortie' => $exitId,
          'id_utilisateur' => $data['id_utilisateur'] ?? null,
      ];

      return $db->insert('livraisons', $deliveryData);
  }

  /**
   * Récupérer les sorties par client
   */
  public static function getByClient($client) {
      $db = Database::getInstance();
      return $db->fetchAll(
          "SELECT s.*, u.nom as nom_utilisateur 
           FROM sorties_stock s
           LEFT JOIN utilisateurs u ON s.id_utilisateur = u.id
           LEFT JOIN clients c ON s.id_client = c.id
           WHERE c.nom LIKE ?
           ORDER BY s.date_sortie DESC",
          ["%{$client}%"]
      );
  }

  /**
   * Récupérer les sorties par commande
   */
  public static function getByOrder($orderId) {
      $db = Database::getInstance();
      return $db->fetchAll(
          "SELECT s.*, u.nom as nom_utilisateur 
           FROM sorties_stock s
           LEFT JOIN utilisateurs u ON s.id_utilisateur = u.id
           JOIN commande_sortie cs ON s.id = cs.id_sortie
           WHERE cs.id_commande = ?
           ORDER BY s.date_sortie DESC",
          [$orderId]
      );
  }

  /**
   * Récupérer les sorties par période
   */
  public static function getByPeriod($startDate, $endDate, $type = null) {
      $db = Database::getInstance();
      $sql = "SELECT s.*, p.designation, p.reference, u.nom as nom_utilisateur
              FROM sorties_stock s
              JOIN produits p ON s.id_produit = p.id
              JOIN utilisateurs u ON s.id_utilisateur = u.id
              WHERE s.date_sortie BETWEEN ? AND ?";

      $params = [$startDate, $endDate];

      if ($type !== null) {
          $sql .= " AND s.type_operation = ?";
          $params[] = $type;
      }

      $sql .= " ORDER BY s.date_sortie DESC";

      return $db->fetchAll($sql, $params);
  }

  /**
   * Récupérer les statistiques des sorties par client
   */
  public static function getStatsByClient() {
      $db = Database::getInstance();
      return $db->fetchAll(
          "SELECT c.nom as client, COUNT(s.id) as count, SUM(des.montant_total) as total
           FROM sorties_stock s
           LEFT JOIN clients c ON s.id_client = c.id
           LEFT JOIN details_sortie_stock des ON s.id = des.id_sortie
           GROUP BY s.id_client
           ORDER BY total DESC"
      );
  }

  /**
   * Récupérer les statistiques des sorties par produit
   */
  public static function getStatsByProduct() {
      $db = Database::getInstance();
      return $db->fetchAll(
          "SELECT p.id as id_produit, p.designation, SUM(des.quantite) as total_quantity, 
                  SUM(des.montant_total) as total_value
           FROM sorties_stock s
           LEFT JOIN details_sortie_stock des ON s.id = des.id_sortie
           LEFT JOIN produits p ON des.id_produit = p.id
           GROUP BY des.id_produit
           ORDER BY total_value DESC"
      );
  }

  /**
   * Générer une facture pour une sortie
   */
  public static function generateInvoice($exitId) {
      $db = Database::getInstance();
      $exit = self::getById($exitId);
      $products = self::getProducts($exitId);

      // Ici, vous pourriez générer un PDF ou simplement retourner les données
      return [
          'exit' => $exit,
          'products' => $products,
          'invoice_number' => generateReference('FAC'),
          'invoice_date' => date('Y-m-d H:i:s'),
          'total' => self::calculateTotal($exitId),
          'tax' => self::calculateTotal($exitId) * 0.2, // TVA à 20%
          'total_with_tax' => self::calculateTotal($exitId) * 1.2
      ];
  }

  /**
   * Récupérer les produits d'une sortie
   */
  public static function getProducts($exitId) {
      $db = Database::getInstance();
      return $db->fetchAll(
          "SELECT des.*, p.designation, p.prix_vente, p.prix_achat 
           FROM details_sortie_stock des
           JOIN produits p ON des.id_produit = p.id
           WHERE des.id_sortie = ?",
          [$exitId]
      );
  }
}
