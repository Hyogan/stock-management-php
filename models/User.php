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
        return $this->db->fetchAll("SELECT * FROM utilisateur ORDER BY id");
    }
    
    /**
     * Récupérer un utilisateur par son ID
     */
    public function getById($id) {
        return $this->db->fetch("SELECT * FROM utilisateur WHERE id = ?", [$id]);
    }
    
    /**
     * Récupérer un utilisateur par son nom
     */
    public function getByUsername($username) {
        return $this->db->fetch("SELECT * FROM utilisateur WHERE nom = ?", [$username]);
    }
    
      /**
     * Récupérer un utilisateur par son email
     */
    public function getByEmail($email) {
      $query = "SELECT * FROM users WHERE email = :email";
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':email', $email, PDO::PARAM_STR);
      $stmt->execute();
      
      return $stmt->fetch(PDO::FETCH_ASSOC);
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
     * Ajouter un nouvel utilisateur
     */
    public function add($data) {
        // Hacher le mot de passe
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (nom, prenom, email, username, password, type, date_creation) 
                  VALUES (:nom, :prenom, :email, :username, :password, :type, NOW())";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nom', $data['nom'], PDO::PARAM_STR);
        $stmt->bindParam(':prenom', $data['prenom'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindParam(':username', $data['username'], PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':type', $data['type'], PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
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
     * Récupérer les utilisateurs par type
     */
    public function getByType($type) {
        return $this->db->fetchAll("SELECT * FROM utilisateur WHERE type = ? ORDER BY id", [$type]);
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

    /**
     * Vérifier si un nom d'utilisateur existe déjà
     */
    public function usernameExists($username, $excludeId = null) {
      $query = "SELECT COUNT(*) FROM users WHERE username = :username";
      
      if ($excludeId) {
          $query .= " AND id != :id";
      }
      
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':username', $username, PDO::PARAM_STR);
      
      if ($excludeId) {
          $stmt->bindParam(':id', $excludeId, PDO::PARAM_INT);
      }
      
      $stmt->execute();
      
      return $stmt->fetchColumn() > 0;
  }
  
  /**
   * Vérifier si un email existe déjà
   */
  public function emailExists($email, $excludeId = null) {
      $query = "SELECT COUNT(*) FROM users WHERE email = :email";
      
      if ($excludeId) {
          $query .= " AND id != :id";
      }
      
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':email', $email, PDO::PARAM_STR);
      
      if ($excludeId) {
          $stmt->bindParam(':id', $excludeId, PDO::PARAM_INT);
      }
      
      $stmt->execute();
      
      return $stmt->fetchColumn() > 0;
  }
}
