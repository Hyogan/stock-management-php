<?php 
/**
 * Constructeur privé pour empêcher l'instanciation directe
 */
class Database{
  private static $instance = null;
  private $connexion;
  private function __construct() {
    $config = require_once BASE_PATH . '/config/database.php';
    
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        $this->connection = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données: " . $e->getMessage());
    }
}

/**
 * Obtenir l'instance unique de la base de données (Singleton)
 */
public static function getInstance() {
    if (self::$instance === null) {
        self::$instance = new self();
    }
    return self::$instance;
}

/**
 * Obtenir la connexion PDO
 */
public function getConnection() {
    return $this->connection;
}

/**
 * Exécuter une requête SQL
 */
public function query($sql, $params = []) {
    $stmt = $this->connection->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Récupérer une seule ligne
 */
public function fetch($sql, $params = []) {
    $stmt = $this->query($sql, $params);
    return $stmt->fetch();
}

/**
 * Récupérer toutes les lignes
 */
public function fetchAll($sql, $params = []) {
    $stmt = $this->query($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Insérer des données et retourner l'ID
 */
public function insert($table, $data) {
    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    
    $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    $this->query($sql, array_values($data));
    
    return $this->connection->lastInsertId();
}

/**
 * Mettre à jour des données
 */
public function update($table, $data, $where, $whereParams = []) {
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
public function delete($table, $where, $params = []) {
    $sql = "DELETE FROM {$table} WHERE {$where}";
    return $this->query($sql, $params)->rowCount();
}

}
