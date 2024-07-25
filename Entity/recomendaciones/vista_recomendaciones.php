<?php
include '../../config/bd.php';
include '../../config/cors.php';

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Consulta SQL para obtener todas las recomendaciones
$sql = "SELECT * FROM recomendaciones";
$stmt = $conn->prepare($sql);
$stmt->execute();

$recommendations = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($recommendations);