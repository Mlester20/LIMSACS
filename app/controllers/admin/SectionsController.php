<?php
session_start();

require_once __DIR__ . '/../../models/registrar/SectionsModel.php';
require_once __DIR__ . '/../../core/errorHandler.php';
require_once __DIR__ . '/../../../database/config/config.php';

    class SectionsController{
        private $model;

        public function __construct($con){
            $this->model = new SectionsModel($con);
        }

        public function index(){
            return $this->model->getWithEnrollmentCounts();
        }
    }

try{
    $controller = new SectionsController($con);
    $sections = $controller->index() ?: [];
}catch(Exception $e){
    ErrorHandler::log($e, 'SectionsController (bootstrap)');
    $sections = [];
}
