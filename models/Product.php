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
    public function updateStock($id, $quantity) {
        $product = $this->getById($id);
        if (!$product) {
            return false;
        }
        
        $newQuantity = $product['quantite'] + $quantity;
        
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
}

<?php
/**
 * Modèle Utilisateur
 */
class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Récupérer tous les utilisateurs
     */
    public function getAll() {
        return $this->db->fetchAll("SELECT * FROM utilisateur ORDER BY nom");
    }
    
    /**
     * Récupérer un utilisateur par son ID
     */
    public function getById($id) {
        return $this->db->fetch("SELECT * FROM utilisateur WHERE id = ?", [$id]);
    }
    
    /**
     * Récupérer un utilisateur par son nom d'utilisateur
     */
    public function getByUsername($username) {
        return $this->db->fetch("SELECT * FROM utilisateur WHERE nom = ?", [$username]);
    }
    
    /**
     * Créer un nouvel utilisateur
     */
    public function create($data) {
        // Vérifier si le nom d'utilisateur existe déjà
        $existingUser = $this->getByUsername($data['nom']);
        if ($existingUser) {
            throw new Exception("Ce nom d'utilisateur existe déjà.");
        }
        
        $userData = [
            'nom' => $data['nom'],
            'mot_de_passe' => password_hash($data['mot_de_passe'], PASSWORD_DEFAULT),
            'type' => $data['type']
        ];
        
        return $this->db->insert('utilisateur', $userData);
    }
    
    /**
     * Mettre à jour un utilisateur
     */
    public function update($id, $data) {
        $userData = [];
        
        // Vérifier si le nom d'utilisateur existe déjà pour un autre utilisateur
        if (isset($data['nom'])) {
            $existingUser = $this->getByUsername($data['nom']);
            if ($existingUser && $existingUser['id'] != $id) {
                throw new Exception("Ce nom d'utilisateur existe déjà.");
            }
            $userData['nom'] = $data['nom'];
        }
        
        // Mettre à jour le mot de passe si fourni
        if (isset($data['mot_de_passe']) && !empty($data['mot_de_passe'])) {
            $userData['mot_de_passe'] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        }
        
        // Mettre à jour le type si fourni
        if (isset($data['type'])) {
            $userData['type'] = $data['type'];
        }
        
        if (!empty($userData)) {
            return $this->db->update('utilisateur', $userData, 'id = ?', [$id]);
        }
        
        return false;
    }
    
    /**
     * Supprimer un utilisateur
     */
    public function delete($id) {
        // Vérifier si l'utilisateur a effectué des opérations
        $operations = $this->db->fetch(
            "SELECT COUNT(*) as count FROM operation WHERE id_utilisateur = ?",
            [$id]
        );
        
        if ($operations['count'] > 0) {
            throw new Exception("Impossible de supprimer l'utilisateur car il a effectué des opérations.");
        }
        
        return $this->db->delete('utilisateur', 'id = ?', [$id]);
    }
    
    /**
     * Authentifier un utilisateur
     */
    public function authenticate($username, $password) {
        $user = $this->getByUsername($username);
        
        if (!$user) {
            return false;
        }
        
        if (password_verify($password, $user['mot_de_passe'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Vérifier si un utilisateur est administrateur
     */
    public function isAdmin($userId) {
        $user = $this->getById($userId);
        
        if (!$user) {
            return false;
        }
        
        return $user['type'] === 'directeur';
    }
    
    /**
     * Vérifier si un utilisateur est magasinier
     */
    public function isStorekeeper($userId) {
        $user = $this->getById($userId);
        
        if (!$user) {
            return false;
        }
        
        return $user['type'] === 'magasinier';
    }
    
    /**
     * Vérifier si un utilisateur est secrétaire
     */
    public function isSecretary($userId) {
        $user = $this->getById($userId);
        
        if (!$user) {
            return false;
        }
        
        return $user['type'] === 'secretaire';
    }
    
    /**
     * Récupérer les opérations effectuées par un utilisateur
     */
    public function getOperations($userId) {
        return $this->db->fetchAll(
            "SELECT * FROM operation WHERE id_utilisateur = ? ORDER BY date DESC",
            [$userId]
        );
    }
    
    /**
     * Changer le mot de passe d'un utilisateur
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        $user = $this->getById($userId);
        
        if (!$user) {
            throw new Exception("Utilisateur non trouvé.");
        }
        
        if (!password_verify($currentPassword, $user['mot_de_passe'])) {
            throw new Exception("Mot de passe actuel incorrect.");
        }
        
        return $this->db->update(
            'utilisateur',
            ['mot_de_passe' => password_hash($newPassword, PASSWORD_DEFAULT)],
            'id = ?',
            [$userId]
        );
    }
    
    /**
     * Récupérer les statistiques d'activité d'un utilisateur
     */
    public function getActivityStats($userId) {
        return $this->db->fetch(
            "SELECT 
                COUNT(*) as total_operations,
                COUNT(CASE WHEN type = 'entree' THEN 1 END) as total_entries,
                COUNT(CASE WHEN type = 'sortie' THEN 1 END) as total_exits,
                MAX(date) as last_operation_date
             FROM operation
             WHERE id_utilisateur = ?",
            [$userId]
        );
    }
}
