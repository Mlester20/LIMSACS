<?php

    class Model{
        public $con;

        public function __construct($con){
            $this->con = $con;
        }
    }