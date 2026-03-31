<?php

require_once BASE_PATH . 'app/config/Database.php';

class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Get database connection for direct queries
     */
    public function getDb(): PDO
    {
        return $this->db;
    }
}