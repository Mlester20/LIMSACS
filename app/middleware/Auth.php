<?php
require_once __DIR__ . '/../helpers/message.php';

    class AuthRole{
        public static function isAuthenticated(){
            if(!isset($_SESSION['id'])){
                setFlash("warning", "Please log in to access this page.");
                header("Location: ../../../index.php");
                exit();
            }
        }

        public static function allowOnly($allowed_roles = []){
            self::isAuthenticated();

            if(!in_array($_SESSION['role'], $allowed_roles)){
                setFlash("error", "You do not have permission to access this page.");
                header("Location: ../../../index.php");
                exit();
            }
        }
    }