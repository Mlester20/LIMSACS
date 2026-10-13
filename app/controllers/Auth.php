<?php
session_start();

require_once __DIR__ . '/../models/AuthModel.php';
require_once __DIR__ . '/../../database/config/config.php';
require_once __DIR__ . '/../helpers/flashMessage.php';
require_once __DIR__ . '/../helpers/auditLogs.php';

    class AuthController extends Model{
        private AuthModel $authModel;
        private AuditLogs $logger;


        public function __construct(){
            global $con; // Access the global connection variable

            parent::__construct($con);

            $this->authModel = new AuthModel($this->con);
            $this->logger = new AuditLogs($this->con);
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
                $this->logger->log(
                    $row['id'],
                    $row['role'],
                    'LOGIN',
                    'AUTH',
                    null,
                    null,
                    $row['full_name'] . ' logged in',
                    'success'
                );
                $this->startUserSession($row);
                $this->redirectByRole($row['role']);
            }else{
                FlashMessage::setFlash('error', 'Invalid email or password');
                header('Location: ' . BASE_URL . '/index.php');
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
            // 'teacher' accounts still exist (as adviser records selectable in Sections),
            // but no longer have their own dashboard — only admin/registrar log in to the system.
            $routes = [
                "admin" => BASE_URL . "/resources/views/admin/dashboard.php",
                "registrar" => BASE_URL . "/resources/views/registrar/home.php",
                //if role is not found (or has no dashboard, e.g. teacher), redirect to index
                "default" => BASE_URL . "/index.php"
            ];

            if(!isset($routes[$role])){
                // Log them back out — they authenticated, but this role has no dashboard to send them to.
                unset($_SESSION['id'], $_SESSION['full_name'], $_SESSION['email'], $_SESSION['role'], $_SESSION['profile_picture']);
                FlashMessage::setFlash('error', 'This account does not have access to the system. Please contact the registrar.');
            }

            $location = $routes[$role] ?? $routes["default"];

            header('Location: ' . $location);
            exit();
        }
    }

    // ======== bootstrap the controller ======== //
    (new AuthController())->handle();
?>