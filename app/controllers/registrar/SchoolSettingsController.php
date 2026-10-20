<?php
session_start();

require_once __DIR__ . '/../../models/SchoolSettingsModel.php';
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../helpers/flashMessage.php';
require_once __DIR__ . '/../../helpers/csrf.php';
require_once __DIR__ . '/../../helpers/auditLogs.php';
require_once __DIR__ . '/../Controller.php';

    class SchoolSettingsController extends Model{
        private SchoolSettingsModel $settingsModel;
        private AuditLogs $logger;

        public function __construct($con){
            parent::__construct($con);
            $this->settingsModel = new SchoolSettingsModel($con);
            $this->logger = new AuditLogs($con);
        }

        public function updateSignatories($data){
            $this->settingsModel->set('registrar_name', trim($data['registrar_name'] ?? ''));
            $this->settingsModel->set('registrar_title', trim($data['registrar_title'] ?? '') ?: 'Registrar');
            $this->settingsModel->set('principal_name', trim($data['principal_name'] ?? ''));
            $this->settingsModel->set('principal_title', trim($data['principal_title'] ?? '') ?: 'School Principal');

            $this->logger->log(
                $_SESSION['id'] ?? null,
                $_SESSION['role'] ?? 'unknown',
                'UPDATE REPORT SIGNATORIES',
                'SCHOOL_SETTINGS',
                null,
                'school_settings',
                $_SESSION['full_name'] . ' updated the report signatory settings',
            );

            FlashMessage::setFlash('success', 'Report signatories updated.');
            header('Location: ' . BASE_URL . '/resources/views/registrar/settings.php');
            exit();
        }
    }

    // ======== bootstrap ======== //
    try{
        $controller = new SchoolSettingsController($con);

        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_signatories'])){
            Csrf::requireValidOnPost(BASE_URL . '/resources/views/registrar/settings.php');
            $controller->updateSignatories($_POST);
        }
    }catch(Exception $e){
        error_log('SchoolSettingsController (bootstrap): ' . $e->getMessage());
        FlashMessage::setFlash('error', 'An error occurred while saving settings.');
        header('Location: ' . BASE_URL . '/resources/views/registrar/settings.php');
        exit();
    }
?>
