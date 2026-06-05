<?php

    class Model{
        protected $con;

        public function __construct($con){
            $this->con = $con;
        }
    }