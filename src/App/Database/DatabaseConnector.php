<?php

class DatabaseConnector {
    private $conn = null;

    function __construct() {
        $this->connect();
    }

    protected function connect() {
        try {
            $this->conn = new PDO("mysql:host=127.0.0.1;dbname=doctor-booking", "adminuser", "mysqlPassword");

            // set the PDO error mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//             $sql = "SELECT * FROM user";
// var_dump($conn);die();
//             foreach ($conn->query($sql) as $row) {
//                 var_dump($row);
//             }
// die();
        }
        catch(PDOException $e)
        {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    public function getConnector() {
        if (empty($this->conn)) {
            $this->connect();
        }

        return $this->conn;
    }
}

?>