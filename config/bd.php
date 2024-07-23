<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "centro_psicologo";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Conexion Fallida: " . $e->getMessage();
}
