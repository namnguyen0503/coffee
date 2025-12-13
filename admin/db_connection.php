<?php
    require_once 'config.php';
    $mysqli= null;
    function connect_db(){
        global $mysqli;
        $mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
        if ($mysqli->connect_errno) {
            die("Kết nối cơ sở dữ liệu thất bại: " . $mysqli->connect_error);
        }
        $mysqli->set_charset("utf8mb4");
        return $mysqli;
    };
   
    function disconnect_db($mysqli){
    mysqli_close($mysqli);
    }
?>