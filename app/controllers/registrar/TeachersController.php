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
     * need an admin login just to get a new adviser into the system. Teacher records
     * created here are adviser-only data — never used to log in (same "no password,
     * role forced server-side" design as the Admin Users module's own teacher path).
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
                if(!empty($errors)){
                    FlashMessage::setFlash('error', implode(' ', $errors));
                    header('Location: ' . $redirectTo);
                    exit();
                }

                $payload = [
                    'full_name' => trim($data['full_name']),
                    'email' => trim($data['email']),
                    // Random, never-disclosed hash: satisfies the NOT NULL column but
                    // can never be guessed/used to log in — this role is data-only.
                    'password' => hashPassword(bin2hex(random_bytes(32))),
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
        ]);
    }
}catch(Exception $e){
    ErrorHandler::log($e, 'TeachersController (bootstrap)');
    exit();
}
