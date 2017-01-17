<?php
class Database {
    
    private static $instance = null;
    private $pdo;
    private $stmt;

    public function __construct() {
        $this->createPDO();
    }

    public function createPDO() {
        //TO DO code seems sketchy, check again
        try {
            $pdo = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        } catch (Exception $ex) {
            die('Failed to connect to database.');
        }  
        if (!$pdo instanceof PDO) {
            throw new Exception('Failed to connect to database.');
        }
        $pdo->query('USE '.DB_NAME);
        $this->pdo = $pdo;
        
        return $this;
    }
    
    public function prepare($query){
        $this->stmt = $this->pdo->prepare($query);
        return $this;
    }
    
    public function execute($params = array()){ 
        $this->stmt->execute($params);
        // TO DO log sql
        return $this;
    }
    
    public function fetchAllAssoc(){
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function lastInsertId(){
        return $this->pdo->lastInsertId();
    }
}
