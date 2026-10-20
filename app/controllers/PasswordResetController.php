<?php
session_start();

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once __DIR__ . '/../../database/config/config.php';
require_once __DIR__ . '/../models/PasswordResetModel.php';
require_once __DIR__ . '/../models/AuthModel.php';
require_once __DIR__ . '/../helpers/flashMessage.php';
require_once __DIR__ . '/../helpers/auditLogs.php';
require_once __DIR__ . '/../helpers/csrf.php';
require_once __DIR__ . '/../helpers/password.php';
require_once __DIR__ . '/../helpers/mailer.php';

    class PasswordResetController extends Model{
        private AuthModel $authModel;
        private PasswordResetModel $resetModel;
        private AuditLogs $logger;

        // How long a reset link stays valid.
        private const TOKEN_TTL_SECONDS = 3600;
        // Minimum time between two reset requests for the same account.
        private const RATE_LIMIT_SECONDS = 120;

        public function __construct(){
            global $con; // Access the global connection variable

            parent::__construct($con);

            $this->authModel = new AuthModel($this->con);
            $this->resetModel = new PasswordResetModel($this->con);
            $this->logger = new AuditLogs($this->con);
        }

        public function handle(): void{
            if($_SERVER['REQUEST_METHOD'] !== 'POST'){
                return;
            }

            if(isset($_POST['request_reset'])){
                Csrf::requireValidOnPost(BASE_URL . '/forgot-password.php');
                $this->requestReset();
            }

            if(isset($_POST['confirm_reset'])){
                Csrf::requireValidOnPost(BASE_URL . '/reset-password.php?token=' . urlencode($_POST['token'] ?? ''));
                $this->confirmReset();
            }
        }

        private function requestReset(): void{
            $email = trim($_POST['email'] ?? '');

            // Same message regardless of outcome, so a response can't be used to
            // probe which emails are registered in the system.
            $genericMessage = 'If that email is registered, a reset link has been sent.';

            if($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)){
                FlashMessage::setFlash('success', $genericMessage);
                header('Location: ' . BASE_URL . '/forgot-password.php');
                exit();
            }

            $user = $this->authModel->getUserByEmail($email);

            if($user){
                $isRateLimited = $this->resetModel->isRateLimited($user['id'], self::RATE_LIMIT_SECONDS);

                if(!$isRateLimited){
                    $this->resetModel->deleteTokensForUser($user['id']);

                    $token = bin2hex(random_bytes(32));
                    $tokenHash = hash('sha256', $token);

                    $this->resetModel->createToken($user['id'], $tokenHash, self::TOKEN_TTL_SECONDS);

                    // Built from APP_URL (not BASE_URL) since this link goes into an
                    // email -- it needs a full scheme+host+subfolder URL, not just
                    // the app's relative path.
                    $appUrl = rtrim($_ENV['APP_URL'] ?? BASE_URL, '/');
                    $resetLink = $appUrl . '/reset-password.php?token=' . $token;

                    $sent = sendPasswordResetEmail($user['email'], $user['full_name'], $resetLink);

                    $this->logger->log(
                        $user['id'],
                        $user['role'],
                        'PASSWORD RESET REQUESTED',
                        'AUTH',
                        null,
                        null,
                        $user['full_name'] . ' requested a password reset' . ($sent ? '' : ' (email send failed)'),
                        $sent ? 'success' : 'error'
                    );
                }
            }

            FlashMessage::setFlash('success', $genericMessage);
            header('Location: ' . BASE_URL . '/forgot-password.php');
            exit();
        }

        private function confirmReset(): void{
            $token = $_POST['token'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $redirectBack = BASE_URL . '/reset-password.php?token=' . urlencode($token);

            if($token === ''){
                FlashMessage::setFlash('error', 'Invalid or expired reset link.');
                header('Location: ' . BASE_URL . '/forgot-password.php');
                exit();
            }

            if(strlen($password) < 8){
                FlashMessage::setFlash('error', 'Password must be at least 8 characters.');
                header('Location: ' . $redirectBack);
                exit();
            }

            if($password !== $confirmPassword){
                FlashMessage::setFlash('error', 'Passwords do not match.');
                header('Location: ' . $redirectBack);
                exit();
            }

            $tokenHash = hash('sha256', $token);
            $row = $this->resetModel->findValidToken($tokenHash);

            if(!$row){
                FlashMessage::setFlash('error', 'This reset link is invalid or has expired. Please request a new one.');
                header('Location: ' . BASE_URL . '/forgot-password.php');
                exit();
            }

            $hashed = hashPassword($password);
            $this->resetModel->updateUserPassword($row['user_id'], $hashed);
            // Invalidate every outstanding token for this user, not just the one used.
            $this->resetModel->deleteTokensForUser($row['user_id']);

            $this->logger->log(
                $row['user_id'],
                $row['role'],
                'PASSWORD RESET COMPLETED',
                'AUTH',
                null,
                null,
                $row['full_name'] . ' reset their password via the forgot-password flow'
            );

            FlashMessage::setFlash('success', 'Your password has been reset. Please sign in.');
            header('Location: ' . BASE_URL . '/index.php');
            exit();
        }
    }

    // ======== bootstrap the controller ======== //
    (new PasswordResetController())->handle();
?>
