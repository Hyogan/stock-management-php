<?php

namespace App\Models;

use App\Core\Model;
use App\Utils\Database;
use Exception;

/**
 * Modèle Commande
 */
class Order extends Model
{
    protected static $table = 'commandes';

    /**
     * Récupérer toutes les commandes
     */
    public static function getAll()
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT c.*, cl.nom, cl.prenom
             FROM commandes c
             JOIN clients cl ON c.id_client = cl.id
             ORDER BY c.date_commande DESC"
        );
    }

    /**
     * Récupérer une commande par son numéro
     */
    public static function getById($id)
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT c.*, cl.nom, cl.prenom, cl.telephone, cl.ville, cl.quartier
             FROM commandes c
             JOIN clients cl ON c.id_client = cl.id
             WHERE c.numero_commande = ?",
            [$id]
        );
    }

    /**
     * Récupérer les détails d'une commande (produits)
     */
    public static function getItems($orderId)
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT d.*, p.designation, p.reference
             FROM details_commande d
             JOIN produits p ON d.id_produit = p.id
             WHERE d.numero_commande = ?",
            [$orderId]
        );
    }

    /**
     * Créer une nouvelle commande
     */
    public static function add($data, $products)
    {
        $db = Database::getInstance();
        $db->getConnection()->beginTransaction();

        try {
            if (isset($data['check_solvency']) && $data['check_solvency']) {
                if (!Client::checkSolvency($data['id_client'])) {
                    throw new Exception("Le client n'est pas solvable pour cette commande.");
                }
            }

            $totalAmount = 0;
            foreach ($products as $product) {
                $totalAmount += $product['quantite'] * $product['prix_unitaire'];
            }

            $orderData = [
                'numero_commande' => $data['numero_commande'] ?? generateReference('CMD'),
                'date_commande' => $data['date'] ?? date('Y-m-d H:i:s'),
                'montant_total' => $totalAmount,
                'nombre_produits' => count($products),
                'id_client' => $data['id_client'],
                'statut' => $data['statut'] ?? ORDER_STATUS_PENDING,
                'statut_paiement' => $data['statut_paiement'] ?? PAYMENT_STATUS_PENDING
            ];

            $orderId = $db->insert('commandes', $orderData);

            foreach ($products as $product) {
                $db->insert('commandes_produits', [
                    'numero_commande' => $orderId,
                    'id_produit' => $product['id_produit'],
                    'quantite' => $product['quantite'],
                    'prix_unitaire' => $product['prix_unitaire']
                ]);
            }

            $db->getConnection()->commit();

            return $orderId;
        } catch (Exception $e) {
            $db->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * Mettre à jour le statut d'une commande
     */
    public static function updateStatus($orderId, $status)
    {
        $db = Database::getInstance();
        return $db->update(
            'commandes',
            ['statut' => $status],
            'numero_commande = ?',
            [$orderId]
        );
    }

    /**
     * Récupérer le client d'une commande
     */
    public static function getClient($clientId)
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM clients WHERE id = ?",
            [$clientId]
        );
    }

    /**
     * Mettre à jour une commande
     */
    public static function update($id, $data)
    {
        $db = Database::getInstance();
        $db->getConnection()->beginTransaction();

        try {
            $orderData = [
                'date_commande' => $data['date'] ?? null,
                'id_client' => $data['id_client'] ?? null,
                'statut' => $data['statut'] ?? null,
                'statut_paiement' => $data['statut_paiement'] ?? null
            ];

            $orderData = array_filter($orderData,
            function ($value) {
                return $value !== null;
            }
        );

            if ($data !== null) {
                $totalAmount = 0;
                foreach ($data as $product) {
                    $totalAmount += $product['quantite'] * $product['prix_unitaire'];
                }

                $orderData['montant_total'] = $totalAmount;
                $orderData['nombre_produits'] = count($data);

                $db->delete('commandes_produits', 'numero_commande = ?', [$id]);

                foreach ($data as $product) {
                    $db->insert('commandes_produits', [
                        'numero_commande' => $id,
                        'id_produit' => $product['id_produit'],
                        'quantite' => $product['quantite'],
                        'prix_unitaire' => $product['prix_unitaire']
                    ]);
                }
            }

            if (!empty($orderData)) {
                $db->update('commandes', $orderData, 'numero_commande = ?', [$id]);
            }

            $db->getConnection()->commit();

            return true;
        } catch (Exception $e) {
            $db->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * Supprimer une commande
     */
    public static function delete($id)
    {
        $db = Database::getInstance();
        $db->getConnection()->beginTransaction();

        try {
            $exits = $db->fetchAll(
                "SELECT id_sortie FROM sorties_commandes WHERE numero_commande = ?",
                [$id]
            );

            if (!empty($exits)) {
                throw new Exception("Impossible de supprimer la commande car elle a des sorties associées.");
            }

            $db->delete('commandes_produits', 'numero_commande = ?', [$id]);
            $db->delete('commandes', 'numero_commande = ?', [$id]);

            $db->getConnection()->commit();

            return true;
        } catch (Exception $e) {
            $db->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * Récupérer les produits d'une commande
     */
    public static function getProducts($id)
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT cp.*, p.designation
             FROM commandes_produits cp
             JOIN produits p ON cp.id_produit = p.id
             WHERE cp.numero_commande = ?",
            [$id]
        );
    }

    /**
     * Approuver une commande
     */
    public static function approve($id)
    {
        $db = Database::getInstance();
        return $db->update(
            'commandes',
            ['statut' => ORDER_STATUS_APPROVED],
            'numero_commande = ?',
            [$id]
        );
    }

    /**
     * Rejeter une commande
     */
    public static function reject($id)
    {
        $db = Database::getInstance();
        return $db->update(
            'commandes',
            ['statut' => ORDER_STATUS_REJECTED],
            'numero_commande = ?',
            [$id]
        );
    }

    /**
     * Mettre à jour le statut de paiement d'une commande
     */
    public static function updatePaymentStatus($id, $status)
    {
        $db = Database::getInstance();
        return $db->update(
            'commandes',
            ['statut_paiement' => $status],
            'numero_commande = ?',
            [$id]
        );
    }

    /**
     * Récupérer les commandes par client
     */
    public static function getByClient($clientId)
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT c.*, cl.nom, cl.prenom
             FROM commandes c
             JOIN clients cl ON c.id_client = cl.id
             WHERE c.id_client = ?
             ORDER BY c.date_commande DESC",
            [$clientId]
        );
    }

    /**
     * Récupérer les commandes par statut
     */
    public static function getByStatus($status)
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT c.*, cl.nom, cl.prenom
             FROM commandes c
             JOIN clients cl ON c.id_client = cl.id
             WHERE c.statut = ?
             ORDER BY c.date_commande DESC",
            [$status]
        );
    }

    /**
     * Récupérer les commandes par statut de paiement
     */
    public static function getByPaymentStatus($status)
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT c.*, cl.nom, cl.prenom
             FROM commandes c
             JOIN clients cl ON c.id_client = cl.id
             WHERE c.statut_paiement = ?
             ORDER BY c.date_commande DESC",
            [$status]
        );
    }

    /**
     * Récupérer les commandes par période
     */
    public static function getByPeriod($startDate, $endDate)
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT c.*, cl.nom, cl.prenom
             FROM commandes c
             JOIN clients cl ON c.id_client = cl.id
             WHERE c.date_commande BETWEEN ? AND ?
             ORDER BY c.date_commande DESC",
            [$startDate, $endDate]
        );
    }

    /**
     * Récupérer les statistiques des commandes par période
     */
    public static function getStatsByPeriod($period = 'month')
    {
        $db = Database::getInstance();
        $sql = "";

        switch ($period) {
            case 'day':
                $sql = "SELECT DATE(date_commande) as period, COUNT(*) as count, SUM(montant_total) as total
                        FROM commandes
                        GROUP BY DATE(date_commande)
                        ORDER BY DATE(date_commande) DESC
                        LIMIT 30";
                break;
            case 'week':
                $sql = "SELECT YEAR(date_commande) as year, WEEK(date_commande) as week, 
                               COUNT(*) as count, SUM(montant_total) as total
                        FROM commandes
                        GROUP BY YEAR(date_commande), WEEK(date_commande)
                        ORDER BY YEAR(date_commande) DESC, WEEK(date_commande) DESC
                        LIMIT 12";
                break;
            case 'month':
            default:
                $sql = "SELECT YEAR(date_commande) as year, MONTH(date_commande) as month, 
                               COUNT(*) as count, SUM(montant_total) as total
                        FROM commandes
                        GROUP BY YEAR(date_commande), MONTH(date_commande)
                        ORDER BY YEAR(date_commande) DESC, MONTH(date_commande) DESC
                        LIMIT 12";
                break;
        }

        return $db->fetchAll($sql);
    }

    /**
     * Récupérer les statistiques des commandes par client
     */
    public static function getStatsByClient()
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT c.id, c.nom, c.prenom, COUNT(*) as count, SUM(co.montant_total) as total
             FROM commandes co
             JOIN clients c ON co.id_client = c.id
             GROUP BY c.id, c.nom, c.prenom
             ORDER BY total DESC
             LIMIT 10"
        );
    }

    /**
     * Récupérer les statistiques des commandes par produit
     */
    public static function getStatsByProduct()
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT p.id, p.designation, SUM(cp.quantite) as total_quantity, 
                    SUM(cp.quantite * cp.prix_unitaire) as total_value
             FROM commandes_produits cp
             JOIN produits p ON cp.id_produit = p.id
             GROUP BY p.id, p.designation
             ORDER BY total_value DESC
             LIMIT 10"
        );
    }

    /**
     * Générer une facture pour une commande
     */
    public static function generateInvoice($id)
    {
        $order = self::getById($id);
        $products = self::getProducts($id);

        return [
            'order' => $order,'products' => $products,
            'invoice_number' => generateReference('FAC'),
            'invoice_date' => date('Y-m-d H:i:s'),
            'total' => $order['montant_total'],
            'tax' => $order['montant_total'] * 0.2,
            'total_with_tax' => $order['montant_total'] * 1.2
        ];
    }

    /**
     * Récupérer les commandes par utilisateur
     */
    public static function getByUserId($userId)
    {
        $db = Database::getInstance();
        $query = "SELECT * FROM commandes WHERE id_utilisateur = :user_id ORDER BY date_commande DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les détails d'une commande
     */
    public static function getOrderDetails($orderId)
    {
        $db = Database::getInstance();
        $query = "SELECT * FROM details_commande WHERE numero_commande = :commande_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':commande_id', $orderId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Mettre à jour la date de livraison d'une commande
     */
    public static function updateDeliveryDate($id, $date)
    {
        $db = Database::getInstance();
        $query = "UPDATE commandes SET date_livraison = :date_livraison WHERE numero_commande = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':date_livraison', $date, \PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, \PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Récupérer les commandes filtrées par date et statut
     */
    public static function getFiltered($startDate, $endDate, $status = '')
    {
        $db = Database::getInstance();
        $query = "SELECT * FROM commandes WHERE date_commande BETWEEN :start_date AND :end_date";

        if (!empty($status)) {
            $query .= " AND statut = :statut";
        }

        $query .= " ORDER BY date_commande DESC";

        $stmt = $db->prepare($query);
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
    public static function getTopProducts($startDate, $endDate, $limit = 10)
    {
        $db = Database::getInstance();
        $query = "SELECT p.id, p.designation, SUM(cd.quantite) as total_quantite, SUM(cd.quantite * cd.prix_unitaire) as total_montant
                FROM produits p
                JOIN details_commande cd ON p.id = cd.id_produit
                JOIN commandes c ON cd.numero_commande = c.numero_commande
                WHERE c.date_commande BETWEEN :start_date AND :end_date
                GROUP BY p.id
                ORDER BY total_quantite DESC
                LIMIT :limit";

        $stmt = $db->prepare($query);
        $startDateTime = "{$startDate} 00:00:00";
        $endDateTime = "{$endDate} 23:59:59";
        $stmt->bindParam(':start_date', $startDateTime, \PDO::PARAM_STR);
        $stmt->bindParam(':end_date', $endDateTime, \PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Rechercher des commandes
     */
    public static function search($keyword, $status = '', $startDate = '', $endDate = '')
    {
        $db = Database::getInstance();
        $query = "SELECT c.*, u.nom, u.prenom 
                FROM commandes c
                JOIN utilisateurs u ON c.id_utilisateur = u.id
                WHERE 1=1";

        $params = [];

        if (!empty($keyword)) {
            $query .= " AND (c.numero_commande LIKE :keyword OR u.nom LIKE :keyword OR u.prenom LIKE :keyword)";
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

        $stmt = $db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Calculer les statistiques des commandes (suite)
     */
    public static function getStats($startDate, $endDate)
    {
        $db = Database::getInstance();
        $stats = [
            'total_commandes' => 0,
            'total_montant' => 0,
            'par_statut' => [],
            'par_jour' => []
        ];

        $query = "SELECT COUNT(*) as total, SUM(montant_total) as montant 
                FROM commandes 
                WHERE date_commande BETWEEN :start_date AND :end_date";
        $stmt = $db->prepare($query);
        $startDateTime = "{$startDate} 00:00:00";
        $endDateTime = "{$endDate} 23:59:59";
        $stmt->bindParam(':start_date', $startDateTime, \PDO::PARAM_STR);
        $stmt->bindParam(':end_date', $endDateTime, \PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stats['total_commandes'] = $result['total'];
        $stats['total_montant'] = $result['montant'];

        $query = "SELECT statut, COUNT(*) as total, SUM(montant_total) as montant 
                FROM commandes 
                WHERE date_commande BETWEEN :start_date AND :end_date 
                GROUP BY statut";

        $stmt = $db->prepare($query);
        $startDateTime = "{$startDate} 00:00:00";
        $endDateTime = "{$endDate} 23:59:59";
        $stmt->bindParam(':start_date', $startDateTime, \PDO::PARAM_STR);
        $stmt->bindParam(':end_date', $endDateTime, \PDO::PARAM_STR);
        $stmt->execute();

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $stats['par_statut'][$row['statut']] = [
                'total' => $row['total'],
                'montant' => $row['montant']
            ];
        }

        $query = "SELECT DATE(date_commande) as jour, COUNT(*) as total, SUM(montant_total) as montant 
                FROM commandes 
                WHERE date_commande BETWEEN :start_date AND :end_date 
                GROUP BY DATE(date_commande) 
                ORDER BY jour";

        $stmt = $db->prepare($query);
        $startDateTime = "{$startDate} 00:00:00";
        $endDateTime = "{$endDate} 23:59:59";
        $stmt->bindParam(':start_date', $startDateTime, \PDO::PARAM_STR);
        $stmt->bindParam(':end_date', $endDateTime, \PDO::PARAM_STR);
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
    public static function getCountByStatus()
    {
        $db = Database::getInstance();
        $query = "SELECT statut, COUNT(*) as total FROM commandes GROUP BY statut";
        $stmt = $db->prepare($query);
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
        $db = Database::getInstance();
        $sql = "SELECT * FROM commandes WHERE {$field} = ?";
        $params = [$value];

        if ($orderByField) {
            $sql .= " ORDER BY " . $orderByField . " " . strtoupper($orderByDirection);
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
