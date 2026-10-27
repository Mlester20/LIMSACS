<?php
session_start();

require_once __DIR__ . '/../../models/teacher/TeacherModel.php';
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../middleware/Auth.php';

    class TeacherStudentsController{
        private $model;

        public function __construct($con){
            $this->model = new TeacherModel($con);
        }

        /**
         * @param int $teacherId
         * @return array ['section' => array|null, 'students' => array]
         */
        public function index($teacherId){
            $section = $this->model->getAssignedSection($teacherId);

            if(!$section){
                return ['section' => null, 'students' => []];
            }

            return [
                'section' => $section,
                'students' => $this->model->getMyStudents($section['id'], $section['school_year_id']),
            ];
        }
    }

    //=================================== bootstrap ====================================//
    try{
        AuthRole::allowOnly(['teacher']);
        $controller = new TeacherStudentsController($con);
        $myStudentsData = $controller->index($_SESSION['id']);
    }catch(Exception $e){
        error_log('TeacherStudentsController (bootstrap): ' . $e->getMessage());
        $myStudentsData = ['section' => null, 'students' => []];
    }
