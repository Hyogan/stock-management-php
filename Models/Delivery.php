<?php
/**
 * Modèle Livraison
 */
namespace App\Models;
use App\Utils\Database;
use App\Core\Model;

class Delivery extends Model {
    protected static $table = 'livraisons';

    /**
     * Récupérer toutes les livraisons
     */
    public static function getAll() {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT l.*, u.nom as utilisateur_nom, u.prenom as utilisateur_prenom, es.id_client, ss.id_commande
             FROM livraisons l
             LEFT JOIN utilisateurs u ON l.id_utilisateur = u.id
             LEFT JOIN entrees_stock es ON l.id = es.id_livraison
             LEFT JOIN sorties_stock ss ON l.id = ss.id_livraison
             ORDER BY l.date_livraison DESC"
        );
    }

    /**
     * Récupérer une livraison par son ID
     */
    public static function getById($id) {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT l.*, u.nom as utilisateur_nom, u.prenom as utilisateur_prenom, es.id_client, ss.id_commande
             FROM livraisons l
             LEFT JOIN utilisateurs u ON l.id_utilisateur = u.id
             LEFT JOIN entrees_stock es ON l.id = es.id_livraison
             LEFT JOIN sorties_stock ss ON l.id = ss.id_livraison
             WHERE l.id = ?",
            [$id]
        );
    }

    /**
     * Créer une nouvelle livraison
     */
    public static function create($data) {
        $deliveryData = [
            'reference' => $data['reference'] ?? generateReference('LIV'),
            'date_livraison' => $data['date_livraison'] ?? date('Y-m-d H:i:s'),
            'id_utilisateur' => $data['id_utilisateur'],
            'destination' => $data['destination'] ?? null,
            'notes' => $data['notes'] ?? null,
            'statut' => $data['statut'] ?? 'en_attente',
            'date_creation' => date('Y-m-d H:i:s'),
        ];
        $db = Database::getInstance();
        $deliveryId = $db->insert(self::$table, $deliveryData);

        // link the delivery to the correct table
        if(isset($data['id_entree'])){
            $db->update('entrees_stock',['id_livraison'=>$deliveryId],'id = ?',[$data['id_entree']]);
        }
        if(isset($data['id_sortie'])){
            $db->update('sorties_stock',['id_livraison'=>$deliveryId],'id = ?',[$data['id_sortie']]);
        }

        return $deliveryId;
    }

    /**
     * Mettre à jour une livraison
     */
    public static function update($id, $data) {
        $deliveryData = [
            'date_livraison' => $data['date_livraison'] ?? null,
            'destination' => $data['destination'] ?? null,
            'notes' => $data['notes'] ?? null,
            'statut' => $data['statut'] ?? null,
            'date_modification' => date('Y-m-d H:i:s'),
        ];

        $deliveryData = array_filter($deliveryData, function ($value) {
            return $value !== null;
        });

        if (!empty($deliveryData)) {
            $db = Database::getInstance();
            return $db->update(self::$table, $deliveryData, 'id = ?', [$id]);
        }

        return false;
    }

    /**
     * Mettre à jour le statut d'une livraison
     */
    public static function updateStatus($id, $status) {
        $db = Database::getInstance();
        return $db->execute("UPDATE livraisons SET statut = ? WHERE id = ?", [$status, $id]);
    }

    /**
     * Supprimer une livraison
     */
    public static function delete($id) {
        $db = Database::getInstance();
        return $db->delete(self::$table, 'id = ?', [$id]);
    }

    /**
     * Récupérer les livraisons par utilisateur
     */
    public function getByUser($userId) {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT l.*, u.nom as utilisateur_nom, u.prenom as utilisateur_prenom, es.id_client, ss.id_commande
             FROM livraisons l
             LEFT JOIN utilisateurs u ON l.id_utilisateur = u.id
             LEFT JOIN entrees_stock es ON l.id = es.id_livraison
             LEFT JOIN sorties_stock ss ON l.id = ss.id_livraison
             WHERE l.id_utilisateur = ?
             ORDER BY l.date_livraison DESC",
            [$userId]
        );
    }

    /**
     * Récupérer les livraisons par période
     */
    public function getByPeriod($startDate, $endDate) {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT l.*, u.nom as utilisateur_nom, u.prenom as utilisateur_prenom, es.id_client, ss.id_commande
             FROM livraisons l
             LEFT JOIN utilisateurs u ON l.id_utilisateur = u.id
             LEFT JOIN entrees_stock es ON l.id = es.id_livraison
             LEFT JOIN sorties_stock ss ON l.id = ss.id_livraison
             WHERE l.date_livraison BETWEEN ? AND ?
             ORDER BY l.date_livraison DESC",
            [$startDate, $endDate]
        );
    }

    /**
     * Récupérer les statistiques des livraisons par période
     */
    public function getStatsByPeriod($period = 'month') {
        $db = Database::getInstance();
        $sql = "";

        switch ($period) {
            case 'day':
                $sql = "SELECT DATE(date_livraison) as period, COUNT(*) as count FROM livraisons GROUP BY DATE(date_livraison) ORDER BY DATE(date_livraison) DESC LIMIT 30";
                break;
            case 'week':
                $sql = "SELECT YEAR(date_livraison) as year, WEEK(date_livraison) as week, COUNT(*) as count FROM livraisons GROUP BY YEAR(date_livraison), WEEK(date_livraison) ORDER BY YEAR(date_livraison) DESC, WEEK(date_livraison) DESC LIMIT 12";
                break;
            case 'month':
            default:
                $sql = "SELECT YEAR(date_livraison) as year, MONTH(date_livraison) as month, COUNT(*) as count FROM livraisons GROUP BY YEAR(date_livraison), MONTH(date_livraison) ORDER BY YEAR(date_livraison) DESC, MONTH(date_livraison) DESC LIMIT 12";
                break;
        }

        return $db->fetchAll($sql);
    }

    /**
     * Récupérer les statistiques des livraisons par statut
     */
    public function getStatsByStatus() {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT statut, COUNT(*) as count FROM livraisons GROUP BY statut ORDER BY count DESC");
    }
}
