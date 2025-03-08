<?php 
// use PDO;
// use PDOException;
namespace App\Utils;
class Database{
  private static $instance = null;
  private $connection;
  private function __construct() 
  {
      $config = require_once BASE_PATH . '/config/database.php';
      
      try {
          $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
          $this->connection = new \PDO($dsn, $config['username'], $config['password'], $config['options']);
      } catch (\PDOException $e) {
          die("Erreur de connexion à la base de données: " . $e->getMessage());
      }
  }

  /**
   * Obtenir l'instance unique de la base de données (Singleton)
   */
    public static function getInstance() 
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

  /**
   * Obtenir la connexion PDO
   */
  public function getConnection() 
  {
      return $this->connection;
  }

  /**
   * Exécuter une requête SQL
   */
  public function query($sql, $params = []) 
  {
      $stmt = $this->connection->prepare($sql);
      $stmt->execute($params);
      return $stmt;
  }

  /**
   * Récupérer une seule ligne
   */
  public function fetch($sql, $params = []) 
  {
      $stmt = $this->query($sql, $params);
      return $stmt->fetch(\PDO::FETCH_ASSOC);
  }

  /**
   * Récupérer toutes les lignes
   */
  public function fetchAll($sql, $params = []) 
  {
      $stmt = $this->query($sql, $params);
      return $stmt->fetchAll();
  }

  /**
   * Insérer des données et retourner l'ID
   */
  public function insert($table, $data) 
  {
      $columns = implode(', ', array_keys($data));
      $placeholders = implode(', ', array_fill(0, count($data), '?'));
      $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
      $this->query($sql, array_values($data));
      
      return $this->connection->lastInsertId();
  }

  /**
   * Mettre à jour des données
   */
  public function update($table, $data, $where, $whereParams = []) 
  {
      $set = [];
      foreach (array_keys($data) as $column) {
          $set[] = "{$column} = ?";
      }
      $setClause = implode(', ', $set);
      
      $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
      $params = array_merge(array_values($data), $whereParams);
      
      return $this->query($sql, $params)->rowCount();
  }

  /**
   * Supprimer des données
   */
  public function delete($table, $where, $params = [])
  {
      $sql = "DELETE FROM {$table} WHERE {$where}";
      return $this->query($sql, $params)->rowCount();
  }


    /**
     * Exécuter une requête SQL directe
     */
    public function execute($sql, $params = []) 
    {
      $stmt = $this->query($sql, $params);
      return $stmt->rowCount();
    }
    /**
     * Commencer une transaction
     */
    public function beginTransaction()
    {
      return $this->connection->beginTransaction();
  }

  /**
   * Valider une transaction
   */
  public function commit() 
  {
      return $this->connection->commit();
  }

  /**
   * Annuler une transaction
   */
  public function rollback() 
  {
      return $this->connection->rollBack();
  }

  /**
   * Échapper une valeur pour l'utiliser dans une requête SQL
   */
  public function escape($value) 
  {
      return $this->connection->quote($value);
  }

  public function prepare($sql) {
    return $this->connection->prepare($sql);
}

  /**
     * Récupérer le dernier ID inséré
     */
    public function lastInsertId() 
    {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Échapper une valeur pour l'utiliser dans une requête SQL
     */
    public function quote($value) 
    {
        return $this->connection->quote($value);
    }
    
    /**
     * Exécuter une requête SQL et retourner le nombre de lignes affectées
     */
    public function exec($sql) 
    {
        return $this->connection->exec($sql);
    }
    
    /**
     * Vérifier si une table existe
     */
    public function tableExists($table) 
    {
        $result = $this->fetch("SHOW TABLES LIKE ?", [$table]);
        return !empty($result);
    }
    
    /**
     * Récupérer les colonnes d'une table
     */
    public function getColumns($table) 
    {
        return $this->fetchAll("SHOW COLUMNS FROM $table");
    }
}
