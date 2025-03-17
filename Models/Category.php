<?php
namespace App\Models;

use App\Core\Model;
use App\Utils\Database;

class Category extends Model {
    protected static $table = 'categories';
    
    /**
     * Récupère toutes les catégories
     */
    public static function getAll() {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM categories ORDER BY nom ASC");
    }
    
    /**
     * Récupère une catégorie par son ID
     */
    public static function getById($id) {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM categories WHERE id = ?", [$id]);
    }
    
    /**
     * Ajoute une nouvelle catégorie
     */
    public static function create($data) {
        $db = Database::getInstance();
        $query = "INSERT INTO categories (nom, description, statut, date_creation) 
                 VALUES (?, ?, ?, NOW())";
        
        $params = [
            $data['nom'],
            $data['description'] ?? null,
            $data['statut'] ?? 'actif'
        ];
        
        $db->query($query, $params);
        return $db->getConnection()->lastInsertId();
    }
    
    /**
     * Met à jour une catégorie
     */
    public static function update($id, $data) {
        $db = Database::getInstance();
        $query = "UPDATE categories 
                 SET nom = ?, 
                     description = ?, 
                     statut = ?, 
                     date_modification = NOW() 
                 WHERE id = ?";
        
        $params = [
            $data['nom'],
            $data['description'] ?? null,
            $data['statut'],
            $id
        ];
        
        return $db->query($query, $params);
    }
    
    /**
     * Supprime une catégorie
     */
    public static function delete($id) {
        $db = Database::getInstance();
        return $db->query("DELETE FROM categories WHERE id = ?", [$id]);
    }
    
    /**
     * Vérifie si une catégorie est utilisée par des produits
     */
    public static function isUsed($id) {
        $db = Database::getInstance();
        $result = $db->fetch("SELECT COUNT(*) as count FROM produits WHERE id_categorie = ?", [$id]);
        return $result['count'] > 0;
    }
    
    /**
     * Récupère le nombre de produits par catégorie
     */
    public static function getProductCount($id) {
        $db = Database::getInstance();
        $result = $db->fetch("SELECT COUNT(*) as count FROM produits WHERE id_categorie = ?", [$id]);
        return $result['count'];
    }
    
    /**
     * Vérifie si un nom de catégorie existe déjà
     */
    public static function nameExists($name, $excludeId = null) {
        $db = Database::getInstance();
        $query = "SELECT COUNT(*) as count FROM categories WHERE nom = ?";
        $params = [$name];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $db->fetch($query, $params);
        return $result['count'] > 0;
    }
    
     /**
     * Récupère les statistiques des catégories
     */
    public static function getStats() {
      $db = Database::getInstance();
      $stats = [];
      
      // Nombre total de catégories
      $result = $db->fetch("SELECT COUNT(*) as count FROM categories");
      $stats['total'] = $result['count'];
      
      // Nombre de catégories actives/inactives
      $statuses = $db->fetchAll("SELECT statut, COUNT(*) as count FROM categories GROUP BY statut");
      $stats['statuses'] = [];
      
      foreach ($statuses as $status) {
          $stats['statuses'][$status['statut']] = $status['count'];
      }
      
      // Catégories les plus utilisées
      $topCategories = $db->fetchAll("
          SELECT c.id, c.nom, COUNT(p.id) as product_count 
          FROM categories c
          LEFT JOIN produits p ON c.id = p.id_categorie
          GROUP BY c.id
          ORDER BY product_count DESC
          LIMIT 5
      ");
      
      $stats['top'] = $topCategories;
      
      return $stats;
  }
  
  /**
   * Récupère les catégories avec le nombre de produits
   */
  public static function getAllWithProductCount() {
      $db = Database::getInstance();
      return $db->fetchAll("
          SELECT c.*, COUNT(p.id) as product_count 
          FROM categories c
          LEFT JOIN produits p ON c.id = p.id_categorie
          GROUP BY c.id
          ORDER BY c.nom ASC
      ");
  }
  
  /**
   * Récupère les catégories actives
   */
  public static function getActive() {
      $db = Database::getInstance();
      return $db->fetchAll("SELECT * FROM categories WHERE statut = 'actif' ORDER BY nom ASC");
  }
}
