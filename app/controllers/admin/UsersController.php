<?php
session_start();

require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../helpers/flashMessage.php';
require_once __DIR__ . '/../../helpers/auditLogs.php';
require_once __DIR__ . '/../../helpers/password.php';
require_once __DIR__ . '/../../helpers/csrf.php';
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../../models/admin/UsersModel.php';

const VALID_USER_ROLES = ['admin', 'registrar', 'teacher', 'staff'];


    class UsersController extends Controller{
        protected $auditLogs;

        public function __construct($con){
            parent::__construct(
                new UsersModel($con)
            );
            $this->auditLogs = new AuditLogs($con);
        }

        public function index($search = '', $role = '', $page = 1){
            $limit = 10;
            $page = max(1, (int)$page);

            $totalRecords = $this->model->count($search, $role);
            $totalPages = $totalRecords > 0 ? (int)ceil($totalRecords / $limit) : 1;
            $page = min($page, $totalPages);
            $offset = ($page - 1) * $limit;

            return [
                'records'       => $this->model->index($search, $role, $limit, $offset) ?: [],
                'current_page'  => $page,
                'total_pages'   => $totalPages,
                'total_records' => $totalRecords,
                'limit'         => $limit,
            ];
        }

        /**
         * @return string[] validation error messages, empty if valid
         */
        public function validate($data, $requirePassword = true){
            $errors = [];

            if(empty(trim($data['full_name'] ?? ''))){
                $errors[] = 'Full name is required.';
            }

            if(empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
                $errors[] = 'A valid email address is required.';
            }

            if($requirePassword && strlen($data['password'] ?? '') < 8){
                $errors[] = 'Password must be at least 8 characters.';
            }

            if(!in_array($data['role'] ?? '', VALID_USER_ROLES, true)){
                $errors[] = 'Please select a valid role.';
            }

            return $errors;
        }

        public function create($data){
            try{
                $errors = $this->validate($data, true);
                if(!empty($errors)){
                    FlashMessage::setFlash('error', implode(' ', $errors));
                    header('Location: ' . BASE_URL . '/resources/views/admin/users.php');
                    exit();
                }

                // Hash the password
                $data['password'] = hashPassword($data['password']);

                if($this->model->create($data)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'CREATE USER',
                        'USER',
                        null,
                        'users',
                        $_SESSION['full_name'] . ' created a new user record',
                    );
                    FlashMessage::setFlash('success', 'User created successfully');
                    header('Location: ' . BASE_URL . '/resources/views/admin/users.php');
                }else{
                    FlashMessage::setFlash("error", "Failed to create user");
                    header("Location: " . BASE_URL . "/resources/views/admin/users.php");
                    exit();
                }
            }catch(Exception $e){
                error_log($e->getMessage());
                exit();
            }
        }

        public function update($id, $data){
            try{
                $errors = $this->validate($data, false);
                if(!empty($errors)){
                    FlashMessage::setFlash('error', implode(' ', $errors));
                    header('Location: ' . BASE_URL . '/resources/views/admin/users.php');
                    exit();
                }

                if($this->model->update($id, $data)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'UPDATE USER',
                        'USER',
                        null,
                        'users',
                        $_SESSION['full_name'] . ' updated user record',
                    );
                    FlashMessage::setFlash('success', 'User updated successfully');
                    header('Location: ' . BASE_URL . '/resources/views/admin/users.php');
                }else{
                    FlashMessage::setFlash("error", "Failed to update user");
                    header("Location: " . BASE_URL . "/resources/views/admin/users.php");
                    exit();
                }
            }catch(Exception $e){
                error_log($e->getMessage());
                exit();
            }
        }

        public function resetPassword($id, $newPassword){
            try{
                if(strlen($newPassword ?? '') < 8){
                    FlashMessage::setFlash('error', 'Password must be at least 8 characters.');
                    header('Location: ' . BASE_URL . '/resources/views/admin/users.php');
                    exit();
                }

                $hashed = hashPassword($newPassword);

                if($this->model->resetPassword($id, $hashed)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'RESET PASSWORD',
                        'USER',
                        $id,
                        'users',
                        $_SESSION['full_name'] . ' reset the password for user ID: ' . $id,
                    );
                    FlashMessage::setFlash('success', 'Password reset successfully');
                    header('Location: ' . BASE_URL . '/resources/views/admin/users.php');
                }else{
                    FlashMessage::setFlash('error', 'Failed to reset password');
                    header('Location: ' . BASE_URL . '/resources/views/admin/users.php');
                }
                exit();
            }catch(Exception $e){
                error_log($e->getMessage());
                exit();
            }
        }

        public function delete($id){
            try{
                if((int)$id === (int)($_SESSION['id'] ?? 0)){
                    FlashMessage::setFlash('error', 'You cannot delete your own account while logged in.');
                    header('Location: ' . BASE_URL . '/resources/views/admin/users.php');
                    exit();
                }

                if($this->model->delete($id)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'DELETED USER',
                        'USER',
                        null,
                        'users',
                        $_SESSION['full_name'] . ' Deleted user record',
                    );
                    FlashMessage::setFlash("success", "User deleted successfully.");
                    header("Location: " . BASE_URL . "/resources/views/admin/users.php");
                    exit();
                }else{
                    FlashMessage::setFlash("success", "There's something wrong with the server. Please try again.");
                    header("Location: " . BASE_URL . "/resources/views/admin/users.php");
                    exit();
                }
            }catch(Exception $e){
                error_log($e->getMessage());
                exit();
            }
        }
    }

    //============ bootstrap ============//
    try{
        $controller = new UsersController($con);

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            Csrf::requireValidOnPost(BASE_URL . '/resources/views/admin/users.php');

            if(isset($_POST['create_user'])){
                $controller->create(
                    [
                        'full_name' => $_POST['full_name'],
                        'email' => $_POST['email'],
                        'password' => $_POST['password'],
                        'role' => $_POST['role'],
                        'profile_picture' => $_POST['profile_picture'] ?? ''
                    ]
                );
            }

            if(isset($_POST['update_user'])){
                $user_id = $_POST['id'];
                $controller->update(
                    $user_id,
                    [
                        'full_name' => $_POST['full_name'],
                        'email' => $_POST['email'],
                        'role' => $_POST['role']
                    ]
                );
            }

            if(isset($_POST['delete_user'])){
                $user_id = $_POST['id'];
                $controller->delete($user_id);
            }

            if(isset($_POST['reset_password'])){
                $user_id = $_POST['id'];
                $controller->resetPassword($user_id, $_POST['new_password'] ?? '');
            }
        }

        $search_term = trim($_GET['search'] ?? '');
        $role_filter = $_GET['role'] ?? '';
        $page = $_GET['page'] ?? 1;

        $listing = $controller->index($search_term, $role_filter, $page);
        $users = $listing['records'];
        $current_page = $listing['current_page'];
        $total_pages = $listing['total_pages'];
        $total_records = $listing['total_records'];
    }catch(Exception $e){
        error_log($e->getMessage());
        exit();
    }