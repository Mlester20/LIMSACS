<?php
session_start();

require_once __DIR__ . '/../../models/teacher/TeacherModel.php';
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../middleware/Auth.php';

    class TeacherDashboardController{
        private $model;

        public function __construct($con){
            $this->model = new TeacherModel($con);
        }

        /**
         * @param int $teacherId
         * @return array ['section' => array|null, 'stats' => array]
         */
        public function index($teacherId){
            $section = $this->model->getAssignedSection($teacherId);

            if(!$section){
                return [
                    'section' => null,
                    'stats' => $this->model->getDashboardStats(0, 0),
                ];
            }

            return [
                'section' => $section,
                'stats' => $this->model->getDashboardStats($section['id'], $section['school_year_id']),
            ];
        }
    }

    //=================================== bootstrap ====================================//
    try{
        AuthRole::allowOnly(['teacher']);
        $controller = new TeacherDashboardController($con);
        $dashboardData = $controller->index($_SESSION['id']);
    }catch(Exception $e){
        error_log('TeacherDashboardController (bootstrap): ' . $e->getMessage());
        $dashboardData = ['section' => null, 'stats' => []];
    }
