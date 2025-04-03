<?php
namespace App\Models;
use Exception;
use App\Core\Model;
use App\Utils\Database;
class User extends Model{
    protected static $table = 'utilisateurs';
     /**
     * Récupère tous les utilisateurs
     */
    public static function getAll() 
    {
      $db = Database::getInstance();
      return $db->fetchAll("SELECT * FROM utilisateurs ORDER BY nom, prenom ASC");
    }
      /**
     * Récupère un utilisateur par son ID
     */
    public static function getById($id) 
  {
      $db = Database::getInstance();
      return $db->fetch("SELECT * FROM utilisateurs WHERE id = ?", [$id]);
  }
    
    /**
     * Récupérer un utilisateur par son nom
     */
    public function getByUsername($username) 
    {
      $db = Database::getInstance();
        return $db->fetch("SELECT * FROM utilisateurs WHERE nom = ?", [$username]);
    }
      /**
     * Récupère un utilisateur par son email
     */
    public static function getByEmail($email)
  {
      $db = Database::getInstance();
      return $db->fetch("SELECT * FROM utilisateurs WHERE email = ?", [$email]);
  }

    /**
     * Récupère un utilisateur par son token de réinitialisation
     */
    public static function getByResetToken($token) {
      $db = Database::getInstance();
      return $db->fetch("SELECT * FROM utilisateurs WHERE reset_token = ?", [$token]);
  }

    /**
     * Récupère un utilisateur par son token "Se souvenir de moi"
     */
    public static function getByRememberToken($token) {
      $db = Database::getInstance();
      return $db->fetch("SELECT * FROM utilisateurs WHERE remember_token = ? AND remember_token_expiry > ?", 
                       [$token, time()]);
  }
    /**
     * Ajoute un nouvel utilisateur
     */
    public static function add($data) {
      // die('here we are');
      $db = Database::getInstance();
      $query = "INSERT INTO utilisateurs (nom, prenom,username, email, mot_de_passe, role, statut, date_creation) 
               VALUES (?, ?, ?, ?, ?, ?, ?,NOW())";
      
      $params = [
          $data['nom'],
          $data['prenom'],
          $data['username'],
          $data['email'],
          password_hash($data['mot_de_passe'], PASSWORD_DEFAULT),
          $data['role'] ?? 'secretaire',
          $data['statut'] ?? 'actif',
      ];
      $db->query($query, $params);
      // die('here we are');
      return $db->getConnection()->lastInsertId();
  }
  
  /**
   * Met à jour un utilisateur
   */
  public static function update($id, $data) 
  {
      $db = Database::getInstance();
      $query = "UPDATE utilisateurs 
               SET nom = ?, 
                   prenom = ?, 
                   username = ?, 
                   email = ?, 
                   role = ?, 
                   statut = ?, 
                   date_modification = NOW() 
               WHERE id = ?";
      
      $params = [
          $data['nom'],
          $data['prenom'],
          $data['username'],
          $data['email'],
          $data['role'],
          $data['statut'],    
          $id
        ];
        
        return $db->query($query, $params);
    }
    /**
     * Charger les détails d'un utilisateur
     */
    
     /**
     * Supprimer un utilisateur
     */
    public static function delete($id) {
      $db = Database::getInstance();
      // Vérifier si l'utilisateur a effectué des opérations
      $operations = $db->fetch(
          "SELECT COUNT(*) as count FROM operations_stock WHERE id_utilisateur = ?",
          [$id]
      );
      
      if ($operations['count'] > 0) {
        return null;
          // throw new Exception("Impossible de supprimer l'utilisateur car il a effectué des opérations.");
      }
      return $db->query("DELETE FROM utilisateurs WHERE id = ?", [$id]);
  }
    
    /**
     * Récupérer les utilisateurs par type
     */
    public function getByType($type) {
       $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM utilisateurs WHERE type = ? ORDER BY id", [$type]);
    }
    
    /**
     * Vérifier si un utilisateur existe
     */
    public function exists($username) {
        $user = $this->getByUsername($username);
        return $user !== false;
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
  public function getOperations($userId) 
  {
      $db = Database::getInstance();{    
      return $db->fetchAll(
          "SELECT * FROM operation_stock WHERE id_utilisateur = ? ORDER BY date DESC",
          [$userId]
      );
    }
  }
  
  /**
   * Changer le mot de passe d'un utilisateur
   */
  public static function updatePassword($userId, $currentPassword, $hashedPassword) {
      $user = self::getById($userId);
      if (!$user) {
          throw new Exception("Utilisateur non trouvé.");
      }
      if (!password_verify($currentPassword, $hashedPassword)) {
          throw new Exception("Mot de passe actuel incorrect.");
      }
      $db = Database::getInstance();
      return $db->query("UPDATE utilisateurs SET mot_de_passe = ?, date_modification = NOW() WHERE id = ?", 
                         [$hashedPassword, $userId]);
  }
  
  /**
   * Récupérer les statistiques d'activité d'un utilisateur
   */
  public function getActivityStats($userId) 
  {
    $db = Database::getInstance();
      return $db->fetch(
          "SELECT 
              COUNT(*) as total_operations,
              COUNT(CASE WHEN type = 'entree' THEN 1 END) as total_entries,
              COUNT(CASE WHEN type = 'sortie' THEN 1 END) as total_exits,
              MAX(date) as last_operation_date
           FROM operation_stock
           WHERE id_utilisateur = ?",
          [$userId]
      );
  }

    /**
     * Vérifier si un nom d'utilisateur existe déjà
     */
    public static function usernameExists($username, $excludeId = null) 
    {
      $db = Database::getInstance();
      $query = "SELECT COUNT(*) AS count FROM utilisateurs WHERE username = ?";
      $params[] = $username;
      if ($excludeId) {
          $query .= " AND id != ?";
          $params[] = $excludeId;
      }
      $result = $db->fetch($query, $params);
      return $result['count'] > 0;
  }
  
  /**
   * Vérifier si un email existe déjà
   */
  public static function emailExists($email, $excludeId = null) 
  {
    $db = Database::getInstance();
    $query = "SELECT COUNT(*) as count FROM utilisateurs WHERE email = ?";
    $params = [$email];
    
    if ($excludeId) {
        $query .= " AND id != ?";
        $params[] = $excludeId;
    }
    
    $result = $db->fetch($query, $params);
    return $result['count'] > 0;
}
  /*
  * Enregistre un token de réinitialisation de mot de passe
  */
 public static function saveResetToken($id, $token, $expiry) {
     $db = Database::getInstance();
     return $db->query("UPDATE utilisateurs SET reset_token = ?, reset_token_expiry = ? WHERE id = ?", 
                      [$token, $expiry, $id]);
 }
   /**
     * Supprime le token de réinitialisation de mot de passe
     */
    public static function clearResetToken($id) {
      $db = Database::getInstance();
      return $db->query("UPDATE utilisateurs SET reset_token = NULL, reset_token_expiry = NULL WHERE id = ?", [$id]);
  }

    /**
     * Enregistre un token "Se souvenir de moi"
     */
    public static function saveRememberToken($id, $token, $expiry) {
      $db = Database::getInstance();
      return $db->query("UPDATE utilisateurs SET remember_token = ?, remember_token_expiry = ? WHERE id = ?", 
                       [$token, $expiry, $id]);
  }


   /**
     * Met à jour la dernière connexion d'un utilisateur
     */
    public static function updateLastLogin($id) 
    {
      $db = Database::getInstance();
      return $db->query("UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?", [$id]);
    }

    /**
     * Supprime un token "Se souvenir de moi"
     */
    public static function deleteRememberToken($token) 
    {
      $db = Database::getInstance();
      return $db->query("UPDATE utilisateurs SET remember_token = NULL, remember_token_expiry = NULL WHERE remember_token = ?", 
                       [$token]);
    }

     /**
     * Récupère les statistiques des utilisateurs
     */
    public static function getStats() 
    {
      $db = Database::getInstance();
      $stats = [];
      // Nombre total d'utilisateurs
      $result = $db->fetch("SELECT COUNT(*) as count FROM utilisateurs");
      $stats['total'] = $result['count'];
      
      // Nombre d'utilisateurs par rôle
      $roles = $db->fetchAll("SELECT role, COUNT(*) as count FROM utilisateurs GROUP BY role");
      $stats['roles'] = [];
      
      foreach ($roles as $role) {
          $stats['roles'][$role['role']] = $role['count'];
      }
      
      // Nombre d'utilisateurs actifs/inactifs
      $statuses = $db->fetchAll("SELECT statut, COUNT(*) as count FROM utilisateurs GROUP BY statut");
      $stats['statuses'] = [];
      
      foreach ($statuses as $status) {
          $stats['statuses'][$status['statut']] = $status['count'];
      }
      
      return $stats;
  }
}
