<?php
date_default_timezone_set("Asia/Jakarta");

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "db_regis";

$db = new mysqli($host, $user, $pass, $dbname);

if ($db->connect_error) {
    die("Koneksi gagal: " . $db->connect_error);
}
?>
