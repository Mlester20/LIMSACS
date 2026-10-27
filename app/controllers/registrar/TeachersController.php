<?php
session_start();

require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../helpers/flashMessage.php';
require_once __DIR__ . '/../../helpers/auditLogs.php';
require_once __DIR__ . '/../../helpers/password.php';
require_once __DIR__ . '/../../helpers/csrf.php';
require_once __DIR__ . '/../../core/errorHandler.php';
require_once __DIR__ . '/../../middleware/Auth.php';
require_once __DIR__ . '/../../models/admin/UsersModel.php';

    /**
     * Lets a registrar add a teacher directly from the Sections page, so they don't
     * need an admin login just to get a new adviser into the system. The registrar
     * sets the teacher's password directly here (and can reset it later) — teachers
     * don't get a self-service "forgot password" flow.
     */
    class TeachersController{
        private $con;
        private $model;
        private $auditLogs;

        public function __construct($con){
            $this->con = $con;
            $this->model = new UsersModel($con);
            $this->auditLogs = new AuditLogs($con);
        }

        public function create($data){
            $redirectTo = BASE_URL . '/resources/views/registrar/sections.php';

            try{
                $errors = [];
                if(empty(trim($data['full_name'] ?? ''))){
                    $errors[] = 'Teacher name is required.';
                }
                if(empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
                    $errors[] = 'A valid email address is required.';
                }
                if(strlen($data['password'] ?? '') < 8){
                    $errors[] = 'Password must be at least 8 characters.';
                }
                if(!empty($errors)){
                    FlashMessage::setFlash('error', implode(' ', $errors));
                    header('Location: ' . $redirectTo);
                    exit();
                }

                $payload = [
                    'full_name' => trim($data['full_name']),
                    'email' => trim($data['email']),
                    'password' => hashPassword($data['password']),
                    'role' => 'teacher', // never trust client input for this
                    'profile_picture' => '',
                ];

                if($this->model->create($payload)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'CREATE TEACHER',
                        'USERS',
                        null,
                        'users',
                        ($_SESSION['full_name'] ?? 'Registrar') . ' added teacher: ' . $payload['full_name'],
                    );
                    FlashMessage::setFlash('success', 'Teacher added — they can now be selected as a section adviser.');
                }else{
                    FlashMessage::setFlash('error', 'Failed to add teacher. Please try again.');
                }
                header('Location: ' . $redirectTo);
                exit();
            }catch(Exception $e){
                ErrorHandler::redirect($e, $redirectTo, 'Failed to add teacher. Please try again.', 'TeachersController::create');
            }
        }

        public function resetPassword($id, $newPassword){
            $redirectTo = BASE_URL . '/resources/views/registrar/sections.php';

            try{
                if(strlen($newPassword ?? '') < 8){
                    FlashMessage::setFlash('error', 'Password must be at least 8 characters.');
                    header('Location: ' . $redirectTo);
                    exit();
                }

                $hashed = hashPassword($newPassword);

                if($this->model->resetPassword($id, $hashed)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'RESET TEACHER PASSWORD',
                        'USERS',
                        $id,
                        'users',
                        ($_SESSION['full_name'] ?? 'Registrar') . ' reset the password for teacher ID: ' . $id,
                    );
                    FlashMessage::setFlash('success', 'Teacher password reset successfully.');
                }else{
                    FlashMessage::setFlash('error', 'Failed to reset password. Please try again.');
                }
                header('Location: ' . $redirectTo);
                exit();
            }catch(Exception $e){
                ErrorHandler::redirect($e, $redirectTo, 'Failed to reset password. Please try again.', 'TeachersController::resetPassword');
            }
        }
    }

//=================================== bootstrap ====================================//
try{
    AuthRole::allowOnly(['registrar']);

    $controller = new TeachersController($con);

    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_teacher'])){
        Csrf::requireValidOnPost(BASE_URL . '/resources/views/registrar/sections.php');
        $controller->create([
            'full_name' => $_POST['full_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
        ]);
    }

    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_teacher_password'])){
        Csrf::requireValidOnPost(BASE_URL . '/resources/views/registrar/sections.php');
        $controller->resetPassword($_POST['id'] ?? null, $_POST['new_password'] ?? '');
    }
}catch(Exception $e){
    ErrorHandler::log($e, 'TeachersController (bootstrap)');
    exit();
}
