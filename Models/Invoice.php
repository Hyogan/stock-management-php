<?php

namespace App\Models;

use App\Core\Model;
use App\Utils\Database;
use PDO;

/**
 * Modèle pour la gestion des factures
 */
class Invoice extends Model
{
    protected $table = 'factures';

    /**
     * Compte le nombre total de factures
     *
     * @return int Nombre de factures
     */
    public function countAll()
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Compte le nombre de factures du mois en cours
     *
     * @return int Nombre de factures du mois
     */
    public function countMonthly()
    {
        $currentMonth = date('Y-m');
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE DATE_FORMAT(date_emission, '%Y-%m') = :month";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':month', $currentMonth);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Compte le nombre de factures impayées
     *
     * @return int Nombre de factures impayées
     */
    public function countUnpaid()
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE statut = 'impayee' OR statut = 'partielle'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Récupère les factures impayées
     *
     * @param int $limit Nombre maximum de factures à récupérer
     * @return array Liste des factures impayées
     */
    public function getUnpaidInvoices($limit = 5)
    {
        $sql = "SELECT f.*, c.nom as client_nom 
                FROM {$this->table} f
                LEFT JOIN clients c ON f.id_client = c.id
                WHERE f.statut = 'impayee' OR f.statut = 'partielle'
                ORDER BY f.date_echeance ASC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compte le nombre de factures par statut
     *
     * @param string $status Statut des factures
     * @return int Nombre de factures
     */
    public function countByStatus($status)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE statut = :status";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Récupère le montant total des factures par statut
     *
     * @param string $status Statut des factures
     * @return float Montant total
     */
    public function getTotalAmountByStatus($status)
    {
        $sql = "SELECT SUM(montant_total) as total FROM {$this->table} WHERE statut = :status";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Récupère le chiffre d'affaires mensuel
     *
     * @param string $month Mois au format YYYY-MM
     * @return float Montant total des factures du mois
     */
    public function getMonthlyRevenue($month)
    {
        $sql = "SELECT SUM(montant_total) as total FROM {$this->table} 
                WHERE DATE_FORMAT(date_emission, '%Y-%m') = :month";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':month', $month);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Récupère le chiffre d'affaires annuel
     *
     * @param int $year Année
     * @return float Montant total des factures de l'année
     */
    public function getYearlyRevenue($year)
    {
        $sql = "SELECT SUM(montant_total) as total FROM {$this->table} 
                WHERE YEAR(date_emission) = :year";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Compte le nombre de factures par année
     *
     * @param int $year Année
     * @return int Nombre de factures
     */
    public function countByYear($year)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE YEAR(date_emission) = :year";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Récupère les statistiques annuelles
     *
     * @return array Statistiques par année
     */
    public function getYearlyStats()
    {
        $sql = "SELECT 
                    YEAR(date_emission) as annee,
                    COUNT(*) as nombre_factures,
                    SUM(montant_total) as montant_total,
                    SUM(CASE WHEN statut = 'payee' THEN montant_total ELSE 0 END) as montant_paye,
                    AVG(montant_total) as montant_moyen
                FROM {$this->table}GROUP BY YEAR(date_emission)
                ORDER BY annee DESC
                LIMIT 3";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les statistiques mensuelles pour l'année en cours
     *
     * @return array Statistiques par mois
     */
    public function getMonthlyStats()
    {
        $currentYear = date('Y');
        $sql = "SELECT 
                    DATE_FORMAT(date_emission, '%Y-%m') as mois,
                    DATE_FORMAT(date_emission, '%b') as mois_nom,
                    COUNT(*) as nombre_factures,
                    SUM(montant_total) as montant_total,
                    SUM(CASE WHEN statut = 'payee' THEN montant_total ELSE 0 END) as montant_paye
                FROM {$this->table}
                WHERE YEAR(date_emission) = :year
                GROUP BY mois, mois_nom
                ORDER BY mois ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':year', $currentYear, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les statistiques par client
     *
     * @param int $limit Nombre maximum de clients à récupérer
     * @return array Statistiques par client
     */
    public function getClientStats($limit = 10)
    {
        $sql = "SELECT 
                    c.id,
                    c.nom,
                    COUNT(f.id) as nombre_factures,
                    SUM(f.montant_total) as montant_total,
                    MAX(f.date_emission) as derniere_facture
                FROM {$this->table} f
                JOIN clients c ON f.id_client = c.id
                GROUP BY c.id, c.nom
                ORDER BY montant_total DESC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour le statut d'une facture
     *
     * @param int $id ID de la facture
     * @param string $status Nouveau statut
     * @return bool Succès de la mise à jour
     */
    public function updateStatus($id, $status)
    {
        $sql = "UPDATE {$this->table} SET statut = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    /**
     * Calcule le montant restant à payer pour une facture
     *
     * @param int $id ID de la facture
     * @return float Montant restant à payer
     */
    public function getRemainingAmount($id)
    {
        // Récupérer le montant total de la facture
        $sql = "SELECT montant_total FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$invoice) {
            return 0;
        }

        $totalAmount = $invoice['montant_total'];

        // Récupérer le montant total des paiements pour cette facture
        $sql = "SELECT SUM(montant) as total_paye FROM paiements WHERE id_facture = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        $paidAmount = $payment['total_paye'] ?? 0;

        return $totalAmount - $paidAmount;
    }
}
