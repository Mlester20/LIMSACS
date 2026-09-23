<?php

    // BASE_URL is the web path to the project root, derived from where the project
    // lives relative to DOCUMENT_ROOT. "/LIMSACS" when served from a subfolder,
    // "" when served from a domain root. Falls back to "/LIMSACS" where
    // DOCUMENT_ROOT isn't available (CLI scripts).
    if(!defined('BASE_URL')){
        $docRoot = !empty($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
        $projectRoot = realpath(dirname(__DIR__, 2));
        if($docRoot && $projectRoot && strpos($projectRoot, $docRoot) === 0){
            define('BASE_URL', rtrim(str_replace('\\', '/', substr($projectRoot, strlen($docRoot))), '/'));
        }else{
            define('BASE_URL', '/LIMSACS');
        }
        unset($docRoot, $projectRoot);
    }

    class Database{
        // Local defaults. Override per environment by creating
        // database/config/config.local.php (gitignored) that returns an array
        // with any of: host, user, password, dbname.
        private $host = "localhost";
        private $user = "root";
        private $password = "";
        private $dbname = "limsacsdb";
        private $conn;

        public function __construct(){
            $localConfig = __DIR__ . '/config.local.php';
            if(is_file($localConfig)){
                $override = require $localConfig;
                if(is_array($override)){
                    foreach(['host', 'user', 'password', 'dbname'] as $key){
                        if(isset($override[$key])){
                            $this->$key = $override[$key];
                        }
                    }
                }
            }
            $this->connect();
        }

        //establish a database mysqli connection
        private function connect(){
            try{
                $this->conn = new mysqli($this->host, $this->user, $this->password, $this->dbname);
                if($this->conn->connect_error){
                    throw new Exception("Connection failed: " . $this->conn->connect_error);
                }
                $this->conn->set_charset("utf8mb4");
            }catch(Throwable $e){
                // Log the real reason; never show host/DB details to the user.
                error_log("Database connection failed: " . $e->getMessage());
                http_response_code(503);
                exit("Service temporarily unavailable. Please try again later.");
            }
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

    $database = new Database();
    $con = $database->getConnection();

?>
