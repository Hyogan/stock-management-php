<?php
namespace App\Models;

use App\Core\Model;
use App\Utils\Database;

class Supplier extends Model {
    protected static $table = 'fournisseurs';
    /**
     * Récupère tous les fournisseurs
     */
    public static function getAll() {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM fournisseurs ORDER BY nom ASC");
    }
    
    /**
     * Récupère un fournisseur par son ID
     */
    public static function getById($id) {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM fournisseurs WHERE id = ?", [$id]);
    }
    
    /**
     * Ajoute un nouveau fournisseur
     */
    public static function add($data) {
        $db = Database::getInstance();
        $query = "INSERT INTO fournisseurs (nom, adresse, telephone, email, statut, date_creation) 
                 VALUES (?, ?, ?, ?, ?, NOW())";
        
        $params = [
            $data['nom'],
            $data['adresse'] ?? null,
            $data['telephone'] ?? null,
            $data['email'] ?? null,
            $data['statut'] ?? 'actif'
        ];
        
        $db->query($query, $params);
        return $db->getConnection()->lastInsertId();
    }
    
    /**
     * Met à jour un fournisseur
     */
    public static function update($id, $data) {
        $db = Database::getInstance();
        $query = "UPDATE fournisseurs SET 
                 nom = ?, 
                 adresse = ?, 
                 telephone = ?, 
                 email = ?, 
                 statut = ?, 
                 date_modification = NOW() 
                 WHERE id = ?";
        
        $params = [
            $data['nom'],
            $data['adresse'] ?? null,
            $data['telephone'] ?? null,
            $data['email'] ?? null,
            $data['statut'] ?? 'actif',
            $id
        ];
        
        return $db->query($query, $params);
    }
    
    /**
     * Supprime un fournisseur
     */
    public static function delete($id) {
        $db = Database::getInstance();
        return $db->query("DELETE FROM fournisseurs WHERE id = ?", [$id]);
    }
    
    /**
     * Vérifie si un fournisseur existe par son nom
     */
    public static function existsByName($name, $excludeId = null) {
        $db = Database::getInstance();
        $query = "SELECT COUNT(*) as count FROM fournisseurs WHERE nom = ?";
        $params = [$name];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $db->fetch($query, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Récupère les produits d'un fournisseur
     */
    public static function getProducts($supplierId) {
        $db = Database::getInstance();
        return $db->fetchAll("
            SELECT p.*, c.nom as categorie_nom 
            FROM produits p 
            LEFT JOIN categories c ON p.id_categorie = c.id 
            WHERE p.id_fournisseur = ? 
            ORDER BY p.designation ASC
        ", [$supplierId]);
    }
    
    /**
     * Change le statut d'un fournisseur
     */
    public static function changeStatus($id, $status) {
        $db = Database::getInstance();
        return $db->query("
            UPDATE fournisseurs 
            SET statut = ?, date_modification = NOW() 
            WHERE id = ?
        ", [$status, $id]);
    }
}
