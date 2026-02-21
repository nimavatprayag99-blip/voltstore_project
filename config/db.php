<?php
/**
 * VoltStore - Database Configuration
 * 
 * This file contains database connection settings and helper functions.
 * Uses PDO for secure database operations with prepared statements.
 * 
 * @package VoltStore
 * @author VoltStore Development Team
 * @version 1.0
 */

// Database Configuration
// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_USERNAME', getenv('DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_NAME', getenv('DB_NAME') ?: 'voltstore');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('SITE_NAME', 'VoltStore');
define('SITE_URL', 'http://localhost/php/voltstore');
define('ADMIN_EMAIL', 'admin@voltstore.com');
define('CURRENCY', '₹');
define('CURRENCY_CODE', 'INR');

// Session Configuration
session_start();

/**
 * Database Connection Class
 * Provides secure database connection using PDO
 */
class Database {
    private static $instance = null;
    private $connection;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->connection = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }
    
    /**
     * Get database instance (Singleton pattern)
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }
}

/**
 * Get database connection
 * 
 * @return PDO
 */
function getDB() {
    return Database::getInstance()->getConnection();
}

/**
 * Execute a prepared query
 * 
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind
 * @return PDOStatement
 */
function executeQuery($sql, $params = []) {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch single row
 * 
 * @param string $sql SQL query
 * @param array $params Parameters
 * @return array|false
 */
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch();
}

/**
 * Fetch all rows
 * 
 * @param string $sql SQL query
 * @param array $params Parameters
 * @return array
 */
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Insert data and return last insert ID
 * 
 * @param string $table Table name
 * @param array $data Associative array of column => value
 * @return int Last insert ID
 */
function insert($table, $data) {
    $db = getDB();
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    
    $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    $stmt = $db->prepare($sql);
    $stmt->execute($data);
    
    return $db->lastInsertId();
}

/**
 * Update data in table
 * 
 * @param string $table Table name
 * @param array $data Associative array of column => value
 * @param string $where Where clause
 * @param array $whereParams Where parameters
 * @return int Affected rows
 */
function update($table, $data, $where, $whereParams = []) {
    $db = getDB();
    $setParts = [];
    foreach ($data as $key => $value) {
        $setParts[] = "{$key} = :{$key}";
    }
    $setClause = implode(', ', $setParts);
    
    $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
    $stmt = $db->prepare($sql);
    $stmt->execute(array_merge($data, $whereParams));
    
    return $stmt->rowCount();
}
