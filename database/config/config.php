<?php

    //defined the base url for production later on
    define('BASE_URL', "/LIMSACS");
    
    class Database{
        private $host = "localhost";
        private $user = "root";
        private $password = "";
        private $dbname = "limsacsdb";
        private $conn;

        public function __construct(){
            $this->connect();
        }

        //establish a database mysqli connection
        private function connect(){
            try{
                $this->conn = new mysqli($this->host, $this->user, $this->password, $this->dbname);
                if($this->conn->connect_error){
                    throw new Exception("Connection failed: " . $this->conn->connect_error);
                }
            }catch(Exception $e){
                echo "Connection failed: " . $e->getMessage();
            }
            $this->conn->set_charset("utf8mb4");
        }

        //get the mysqli connection
        public function getConnection(){
            return $this->conn;
        }

        //close the database connection
        public function closeConnection(){
            if($this->conn){
                $this->conn->close();
            }
        }
    }

    try{
        $database = new Database();
        $con = $database->getConnection();
    }catch(Exception $e){
        echo "Error: " . $e->getMessage();
    }

?>