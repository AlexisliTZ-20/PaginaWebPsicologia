<?php
include '../../config/bd.php';
include '../../config/cors.php';

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Consulta SQL para obtener todos los artículos
$sql = "SELECT * FROM articulos";
$stmt = $conn->prepare($sql);
$stmt->execute();

$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($articles);

