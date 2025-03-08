<?php
namespace App\Core;

// use PDO;
// use PDOException;

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() 
    {
        $config = require_once BASE_PATH . '/config/database.php';
        
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            $this->connection = new \PDO($dsn, $config['username'], $config['password'], $config['options']);
        } catch (\PDOException $e) {
            die("Erreur de connexion à la base de données pour cette raison: " . $e->getMessage());
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
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Insérer des données et retourner l'ID
     */
    public function insert($sql, $params = []) 
    {
        $this->query($sql, $params);
        return $this->connection->lastInsertId();
    }
}

