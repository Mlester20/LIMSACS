<?php
session_start();

require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../helpers/message.php';
require_once __DIR__ . '/../../helpers/auditLogs.php';
require_once __DIR__ . '/../../helpers/password.php';
require_once __DIR__ . '/../Controller.php';

require_once __DIR__ . '/../../models/admin/UsersModel.php';


    class UsersController extends Controller{
        protected $auditLogs;

        public function __construct($con){
            parent::__construct(
                new UsersModel($con)
            );
            $this->auditLogs = new AuditLogs($con);
        }

        public function index(){
            return $this->model->index();
        }

        public function create($data){
            try{
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
                    setFlash('success', 'User created successfully');
                    header('Location: ../../../resources/views/admin/users.php');
                }else{
                    setFlash("error", "Failed to create user");
                    header("Location: ../../../resources/views/admin/users.php");
                    exit();
                }
            }catch(Exception $e){
                error_log($e->getMessage());
                exit();
            }
        }

        public function update($id, $data){
            try{
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
                    setFlash('success', 'User updated successfully');
                    header('Location: ../../../resources/views/admin/users.php');
                }else{
                    setFlash("error", "Failed to update user");
                    header("Location: ../../../resources/views/admin/users.php");
                    exit();
                }
            }catch(Exception $e){
                error_log($e->getMessage());
                exit();
            }
        }

        public function delete($id){
            try{
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
                    setFlash("success", "User deleted successfully.");
                    header("Location: ../../../resources/views/admin/users.php");
                    exit();
                }else{
                    setFlash("success", "There's something wrong with the server. Please try again.");
                    header("Location: ../../../resources/views/admin/users.php");
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
        $users = $controller->index();

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
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
        }
    }catch(Exception $e){
        error_log($e->getMessage());
        exit();
    }