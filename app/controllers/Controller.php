<?php

    abstract class Controller{
        protected $model;

        public function __construct($model){
            $this->model = $model;
        }

        abstract public function index();
        abstract public function create($data);
        abstract public function update($id, $data);
        abstract public function delete($id);
    }