<?php

namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * Modèle pour la gestion des paiements
 */
class Payment extends Model
{
    protected $table = 'paiements';
    
    /**
     * Récupère le montant total des paiements
     * 
     * @return float Montant total des paiements
     */
    public function getTotalAmount()
    {
        $sql = "SELECT SUM(montant) as total FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    /**
     * Récupère le montant total des paiements du mois en cours
     * 
     * @return float Montant total des paiements du mois
     */
    public function getMonthlyAmount($month = null)
    {
        $month = $month ?? date('Y-m');
        $sql = "SELECT SUM(montant) as total FROM {$this->table} 
                WHERE DATE_FORMAT(date_paiement, '%Y-%m') = :month";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':month', $month);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    /**
     * Récupère les paiements récents
     * 
     * @param int $limit Nombre maximum de paiements à récupérer
     * @return array Liste des paiements récents
     */
    public function getRecentPayments($limit = 5)
    {
        $sql = "SELECT p.*, f.numero as facture_numero, c.nom as client_nom 
                FROM {$this->table} p
                LEFT JOIN factures f ON p.id_facture = f.id
                LEFT JOIN clients c ON f.id_client = c.id
                ORDER BY p.date_paiement DESC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les statistiques par mode de paiement
     * 
     * @return array Statistiques par mode de paiement
     */
    public function getStatsByMethod()
    {
        $sql = "SELECT 
                    mode_paiement,
                    COUNT(*) as nombre_paiements,
                    SUM(montant) as montant_total
                FROM {$this->table}
                GROUP BY mode_paiement
                ORDER BY montant_total DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Ajoute un nouveau paiement
     * 
     * @param array $data Données du paiement
     * @return int|bool ID du paiement créé ou false en cas d'échec
     */
    public function addPayment($data)
    {
        $sql = "INSERT INTO {$this->table} (id_facture, montant, date_paiement, mode_paiement, reference, notes, id_utilisateur) 
                VALUES (:id_facture, :montant, :date_paiement, :mode_paiement, :reference, :notes, :id_utilisateur)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_facture', $data['id_facture'], PDO::PARAM_INT);
        $stmt->bindParam(':montant', $data['montant']);
        $stmt->bindParam(':date_paiement', $data['date_paiement']);
        $stmt->bindParam(':mode_paiement', $data['mode_paiement']);
        $stmt->bindParam(':reference', $data['reference']);
        $stmt->bindParam(':notes', $data['notes']);
        $stmt->bindParam(':id_utilisateur', $data['id_utilisateur'], PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            // Mettre à jour le statut de la facture
            $this->updateInvoiceStatus($data['id_facture']);
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Met à jour le statut d'une facture après un paiement
     * 
     * @param int $invoiceId ID de la facture
     * @return bool Succès de la mise à jour
     */
    private function updateInvoiceStatus($invoiceId)
    {
        // Récupérer le modèle de facture
        $invoiceModel = new Invoice();
        
        // Calculer le montant restant à payer
        $remainingAmount = $invoiceModel->getRemainingAmount($invoiceId);
        
        // Déterminer le nouveau statut
        $newStatus = 'impayee';
        if ($remainingAmount <= 0) {
            $newStatus = 'payee';
        } elseif ($remainingAmount > 0) {
            $newStatus = 'partielle';
        }
        
        // Mettre à jour le statut
        return $invoiceModel->updateStatus($invoiceId, $newStatus);
    }
    
    /**
     * Récupère les paiements d'une facture
     * 
     * @param int $invoiceId ID de la facture
     * @return array Liste des paiements
     */
    public function getPaymentsByInvoice($invoiceId)
    {
        $sql = "SELECT p.*, u.nom as utilisateur_nom 
                FROM {$this->table} p
                LEFT JOIN utilisateurs u ON p.id_utilisateur = u.id
                WHERE p.id_facture = :id_facture
                ORDER BY p.date_paiement DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_facture', $invoiceId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Supprime un paiement
     * 
     * @param int $id ID du paiement
     * @return bool Succès de la suppression
     */
    public function deletePayment($id)
    {
        // Récupérer l'ID de la facture avant de supprimer le paiement
        $sql = "SELECT id_facture FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payment) {
            return false;
        }
        
        $invoiceId = $payment['id_facture'];
        
        // Supprimer le paiement
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            // Mettre à jour le statut de la facture
            $this->updateInvoiceStatus($invoiceId);
            return true;
        }
        
        return false;
    }
}
