<?php
namespace App\Core;

use App\Utils\Database;

class Model {
    protected static $table;
    protected static $db;

    public function __construct() {
        // Initialiser la connexion à la base de données
        self::$db = Database::getInstance();
    }

    public static function all() {
        $table = static::$table;
        return self::$db->fetchAll("SELECT * FROM {$table}");
    }

  

    /**
     * Trouver un enregistrement par son ID
     */
    public static function find($id) {
        $table = static::$table;
        return self::$db->fetch("SELECT * FROM {$table} WHERE id = ?", [$id]);
    }

    /**
     * Créer un nouvel enregistrement
     */
    public static function create($data) 
    {
        $table = static::$table;
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        
        self::$db->query($sql, array_values($data));
        return self::$db->getConnection()->lastInsertId();
    }

    /**
     * Mettre à jour un enregistrement
     */
    public static function update($id, $data) {
        $table = static::$table;
        
        $setParts = [];
        $values = [];
        
        foreach ($data as $column => $value) {
            $setParts[] = "{$column} = ?";
            $values[] = $value;
        }
        
        $setClause = implode(', ', $setParts);
        $values[] = $id;
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE id = ?";
        
        return self::$db->query($sql, $values);
    }

    /**
     * Supprimer un enregistrement
     */
    public static function delete($id) {
        $table = static::$table;
        return self::$db->query("DELETE FROM {$table} WHERE id = ?", [$id]);
    }
}

