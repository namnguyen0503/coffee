<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'coffee');
define('DB_PASS', '123456'); 
define('DB_NAME', 'coffee');



// Ngăn truy cập trực tiếp qua URL
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Truy cập trực tiếp bị từ chối.');
}
?>