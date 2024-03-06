<?php

$host = "192.168.1.11";  
$username = "root"; 
$password = ""; 
$database = "rtl"; 

$koneksi = new mysqli($host, $username, $password, $database);

if ($koneksi->connect_error) {
    die("Koneksi Gagal: " . $koneksi->connect_error);
}

?>
