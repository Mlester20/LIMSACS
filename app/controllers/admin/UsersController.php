<?php
session_start();

require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../helpers/message.php';
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../../models/admin/UsersModel.php';


    class UsersController extends Controller{
        public function __construct($con){
            parent::__construct(
                new UsersModel($con)
            );
        }

        public function index(){
            return $this->model->index();
        }

        public function create($data){

        }

        public function update($id, $data){

        }

        public function delete($id){

        }
    }

    //============ bootstrap ============//
    try{
        $controller = new UsersController($con);
        $users = $controller->index();
    }catch(Exception $e){
        error_log($e->getMessage());
        exit();
    }