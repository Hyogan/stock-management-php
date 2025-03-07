<?php
/**
 * Modèle Produit
 */
class Product {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Récupérer tous les produits
     */
    public function getAll() {
        return $this->db->fetchAll("SELECT * FROM produit ORDER BY designation");
    }
    
    /**
     * Récupérer un produit par son ID
     */
    public function getById($id) {
        return $this->db->fetch("SELECT * FROM produit WHERE id_produit = ?", [$id]);
    }
    
    /**
     * Créer un nouveau produit
     */
    public function create($data) {
        // Ajouter la date d'ajout si elle n'est pas spécifiée
        if (!isset($data['date_ajout'])) {
            $data['date_ajout'] = date('Y-m-d H:i:s');
        }
        
        return $this->db->insert('produit', $data);
    }
    
    /**
     * Mettre à jour un produit
     */
    public function update($id, $data) {
        return $this->db->update('produit', $data, 'id_produit = ?', [$id]);
    }
    
    /**
     * Supprimer un produit
     */
    public function delete($id) {
        return $this->db->delete('produit', 'id_produit = ?', [$id]);
    }
    
    /**
     * Rechercher des produits
     */
    public function search($keyword) {
        $keyword = "%{$keyword}%";
        return $this->db->fetchAll(
            "SELECT * FROM produit WHERE designation LIKE ? ORDER BY designation",
            [$keyword]
        );
    }
    
    /**
     * Mettre à jour le stock d'un produit
     */
    public function updateStock($id, $quantity, $type) {
        $product = $this->getById($id);
        if (!$product) {
            return false;
        }
        // $newQuantity = $product['quantite'];
        $newQuantity = ($type == '-') ? $product['quantite'] - $quantity : $product['quantite'] + $quantity;
        
        // Empêcher les quantités négatives
        if ($newQuantity < 0) {
            return false;
        }
        
        return $this->db->update(
            'produit',
            ['quantite' => $newQuantity],
            'id_produit = ?',
            [$id]
        );
    }
    
    /**
     * Vérifier si un produit est en stock
     */
    public function isInStock($id, $quantity = 1) {
        $product = $this->getById($id);
        return $product && $product['quantite'] >= $quantity;
    }
    
    /**
     * Récupérer les produits avec un stock faible
     */
    public function getLowStock($threshold = 10) {
        return $this->db->fetchAll(
            "SELECT * FROM produit WHERE quantite <= ? ORDER BY quantite",
            [$threshold]
        );
    }
    
    /**
     * Calculer la valeur totale du stock
     */
    public function getTotalStockValue() {
        return $this->db->fetch(
            "SELECT SUM(quantite * prix_achat) as total_value FROM produit"
        )['total_value'] ?? 0;
    }
    
    /**
     * Récupérer les produits les plus vendus
     */
    public function getMostSold($limit = 10) {
        return $this->db->fetchAll(
            "SELECT p.*, SUM(o.nombre_produit) as total_sold 
             FROM produit p
             JOIN operation_produit op ON p.id_produit = op.id_produit
             JOIN operation o ON op.numero_operation = o.numero_operation
             WHERE o.type = 'sortie'
             GROUP BY p.id_produit
             ORDER BY total_sold DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function recordStockMovement($data) {
      return $this->db->insert('mouvement_stock', $data);
  }
  
  /**
   * Récupérer l'historique des mouvements d'un produit
   */
  public function getMovementHistory($productId) {
      return $this->db->fetchAll(
          "SELECT m.*, u.nom, u.prenom 
          FROM mouvement_stock m 
          LEFT JOIN utilisateur u ON m.id_utilisateur = u.id_utilisateur 
          WHERE m.id_produit = ? 
          ORDER BY m.date_mouvement DESC",
          [$productId]
      );
  }
  
  /**
   * Compter le nombre total de produits
   */
  public function countProducts() {
      $result = $this->db->fetch("SELECT COUNT(*) AS total FROM produit");
      return $result ? $result['total'] : 0;
  }
  
  /**
   * Récupérer le produit le plus cher
   */
  public function getMostExpensiveProduct() {
      return $this->db->fetch("SELECT * FROM produit ORDER BY prix_vente DESC LIMIT 1");
  }
  
  /**
   * Récupérer le produit avec le plus grand stock
   */
  public function getMostInStockProduct() {
      return $this->db->fetch("SELECT * FROM produit ORDER BY quantite DESC LIMIT 1");
  }
  
  /**
   * Récupérer les mouvements de stock récents
   */
  public function getRecentMovements($limit = 10) {
      return $this->db->fetchAll(
          "SELECT m.*, p.designation, u.nom, u.prenom 
          FROM mouvement_stock m 
          JOIN produit p ON m.id_produit = p.id_produit 
          LEFT JOIN utilisateur u ON m.id_utilisateur = u.id_utilisateur 
          ORDER BY m.date_mouvement DESC 
          LIMIT ?",
          [$limit]
      );
  }

    /**
     * Récupérer les produits en rupture de stock
     */
    public function getOutOfStock() {
      return $this->db->fetchAll("SELECT * FROM produit WHERE quantite = 0 ORDER BY designation");
  }

    
    /**
     * Récupérer les produits les plus vendus
     */
    public function getTopSelling($limit = 5) {
      $query = "SELECT p.*, SUM(od.quantite) as total_vendu 
                FROM produits p
                JOIN commande_details od ON p.id = od.produit_id
                JOIN commandes o ON od.commande_id = o.id
                WHERE o.statut = 'completed'
                GROUP BY p.id
                ORDER BY total_vendu DESC
                LIMIT :limit";
      
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
      $stmt->execute();
      
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

    /**
     * Récupérer les produits récemment ajoutés
     */
    public function getRecent($limit = 10) {
      $query = "SELECT * FROM produits ORDER BY date_creation DESC LIMIT :limit";
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
      $stmt->execute();
      
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
