<?php
/**
 * Modèle Client
 */
namespace App\Models;

use App\Core\Model;
use App\Utils\Database;

class Client extends Model {
    protected static $table = 'clients';

    /**
     * Récupérer tous les clients
     */
    public static function getAll() {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM clients ORDER BY nom, prenom");
    }

    public static function getByStatus($status) {
      $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM clients WHERE statut='$status' ORDER BY nom, prenom");
    }

    /**
     * Récupérer un client par son ID
     */
    public static function getById($id) {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM clients WHERE id = ?", [$id]);
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
    public static function search($keyword) {
        $db = Database::getInstance();
        $keyword = "%{$keyword}%";
        return $db->fetchAll(
            "SELECT * FROM clients 
             WHERE nom LIKE ? OR prenom LIKE ? OR telephone LIKE ?
             ORDER BY nom, prenom",
            [$keyword, $keyword, $keyword]
        );
    }

    /**
     * Vérifier la solvabilité d'un client
     */
    public static function checkSolvency($id) {
        $db = Database::getInstance();
        $unpaidOrders = $db->fetch(
            "SELECT SUM(montant_total) as total_unpaid
             FROM commandes
             WHERE id_client = ? AND statut_paiement != 'paid'",
            [$id]
        );

        $totalUnpaid = $unpaidOrders['total_unpaid'] ?? 0;
        $threshold = 5000;

        return $totalUnpaid < $threshold;
    }

    /**
     * Récupérer l'historique des commandes d'un client
     */
    public static function getOrderHistory($id) {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT c.*
             FROM commandes c
             WHERE c.id_client = ?
             ORDER BY c.date_creation DESC",
            [$id]
        );
    }

    /**
     * Récupérer les statistiques d'achat d'un client
     */
    public static function getPurchaseStats($id) {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT COUNT(c.id) as total_orders,
                    SUM(c.montant_total) as total_amount,
                    AVG(c.montant_total) as avg_order_value,
                    MAX(c.date_creation) as last_order_date
             FROM commandes c
             WHERE c.id_client = ?",
            [$id]
        );
    }

    /**
     * Récupérer les produits les plus achetés par un client
     */
    public static function getMostPurchasedProducts($id, $limit = 5) {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT p.id, p.designation, SUM(dc.quantite) as total_quantity
             FROM commandes c
             JOIN details_commande dc ON c.id = dc.id_commande
             JOIN produits p ON dc.id_produit = p.id
             WHERE c.id_client = ?
             GROUP BY p.id, p.designation
             ORDER BY total_quantity DESC
             LIMIT ?",
            [$id, $limit]
        );
    }

    /**
     * Récupérer les clients les plus actifs
     */
    public static function getMostActiveClients($limit = 10) {
      $db = Database::getInstance();
      return $db->fetchAll(
          "SELECT c.id, c.nom, c.prenom, COUNT(co.id) as order_count,
                  SUM(co.montant_total) as total_spent
           FROM clients c
           JOIN commandes co ON c.id = co.id_client
           GROUP BY c.id, c.nom, c.prenom
           ORDER BY order_count DESC, total_spent DESC
           LIMIT ?",
          [$limit]
      );
  }

  /**
   * Récupérer les clients par ville
   */
  public static function getByCity($city) {
      $db = Database::getInstance();
      return $db->fetchAll(
          "SELECT * FROM clients WHERE ville LIKE ? ORDER BY nom, prenom",
          ["%{$city}%"]
      );
  }

  /**
   * Récupérer les statistiques des clients par ville
   */
  public static function getStatsByCity() {
      $db = Database::getInstance();
      return $db->fetchAll(
          "SELECT ville, COUNT(*) as client_count
           FROM clients
           GROUP BY ville
           ORDER BY client_count DESC"
      );
  }

  /**
   * Récupérer les clients inactifs (sans commande depuis une période donnée)
   */
  public static function getInactiveClients($months = 6) {
      $db = Database::getInstance();
      $date = date('Y-m-d', strtotime("-{$months} months"));

      return $db->fetchAll(
          "SELECT c.*
           FROM clients c
           LEFT JOIN (
               SELECT id_client, MAX(date_creation) as last_order_date
               FROM commandes
               GROUP BY id_client
           ) co ON c.id = co.id_client
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
      $db = Database::getInstance();
      $sql = "SELECT c.*,
          CASE
              WHEN c.statut = 'pending' THEN 'En attente'
              WHEN c.statut = 'approved' THEN 'Validée'
              WHEN c.statut = 'delivered' THEN 'Livrée'
              WHEN c.statut = 'cancelled' THEN 'Annulée'
              WHEN c.statut = 'rejected' THEN 'Rejetée'
              ELSE c.statut
          END AS statut_libelle,
          DATE_FORMAT(c.date_creation, '%d/%m/%Y') AS date_formatee
          FROM commandes c
          WHERE c.id_client = ?
          ORDER BY c.date_creation DESC";

      $params = [$clientId];

      if ($limit !== null) {
          $sql .= " LIMIT ?";
          $params[] = (int)$limit;

          if ($offset !== null) {
              $sql .= " OFFSET ?";
              $params[] = (int)$offset;
          }
      }

      return $db->fetchAll($sql, $params);
  }


}

