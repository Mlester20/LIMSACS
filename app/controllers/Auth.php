<?php
session_start();

require_once __DIR__ . '/../models/AuthModel.php';
require_once __DIR__ . '/../../database/config/config.php';
require_once __DIR__ . '/../helpers/message.php';

    class AuthController extends Model{
        private $authModel;

        public function __construct(){
            global $con; // Access the global connection variable

            parent::__construct($con);

            $this->authModel = new AuthModel($this->con);
        }

        public function handle(){
            if($_SERVER['REQUEST_METHOD'] === 'POST'){
                $this->login();
            }
        }

        private function login(): void{
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $row = $this->authModel->getUserByEmail($email);
            if($row && $this->authModel->verifyPassword($password, $row['password'])){
                $this->startUserSession($row);
                $this->redirectByRole($row['role']);
            }else{
                setFlash('error', 'Invalid email or password');
                header('Location: ../../index.php');
                exit();
            }
        }

        private function startUserSession(array $user): void{
            $_SESSION['id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['profile_picture'] = $user['profile_picture'];
        }

        private function redirectByRole(string $role): void{
            $routes = [
                "admin" => "../../../resources/views/admin/dashboard.php",
                "registrar" => "../../../resources/views/registrar/home.php",
            ];

            $location = $routes[$role] ?? '../../../index.php';

            header('Location: ' . $location);
            exit();
        }
    }

    // ======== bootstrap the controller ======== //
    (new AuthController())->handle();
?>