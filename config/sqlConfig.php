<?php
$servername = "localhost";
$username = "root";
$password = "nopoxPassword";
$dbname = "auth";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}