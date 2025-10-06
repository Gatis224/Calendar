<?php
$db_host = 'localhost';
$db_user = 'gatiss';
$db_password = 'Gati$123';
$db_name = 'gatis_sturitis';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

define('BASE_URL', 'http://192.168.1.138/');
?>