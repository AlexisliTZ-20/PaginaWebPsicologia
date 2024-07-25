<?php
include '../../config/bd.php';
include '../../config/cors.php';
include '../../jwt/jwt_utils.php';

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get Authorization header
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authHeader) {
    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if ($jwt && validate_jwt($jwt)) {
        $psicologo_id = filter_var($_GET['psicologo_id'], FILTER_VALIDATE_INT);

        $sql = "SELECT * FROM recomendaciones WHERE psicologo_id = :psicologo_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':psicologo_id', $psicologo_id);
        $stmt->execute();

        $recommendations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($recommendations);
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}