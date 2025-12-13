<?php
    require_once 'config.php';
    global $mysqli;
    function connect_db(){
        global $mysqli;
        $mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
        if ($mysqli->connect_errno) {
            die("Kết nối cơ sở dữ liệu thất bại: " . $mysqli->connect_error);
        }
        $mysqli->set_charset("utf8mb4");
        return $mysqli;
    };
    connect_db();
?>