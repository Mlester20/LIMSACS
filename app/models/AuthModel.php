<?php
require_once __DIR__ . '/Model.php';

    class AuthModel extends Model{
        protected $users = 'users';

        public function verifyPassword($password, $hash){
            return password_verify($password, $hash);
        }

        public function getUserByEmail($email){
            try{
                $sql = "SELECT * FROM {$this->users} WHERE email = ? LIMIT 1";
                $stmt = $this->con->prepare($sql);

                $stmt->bind_param("s", $email);
                $stmt->execute();

                $result = $stmt->get_result();
                return $result->fetch_assoc();
            }catch(Exception $e){
                error_log("Error fetching user by email: " . $e->getMessage());
                return false;

            }finally{
                if(isset($stmt)){
                    $stmt->close();
                }
            }
        }

    }