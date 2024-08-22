<?php
// read_costos_terapia.php

include '../../config/bd.php';
include '../../config/cors.php';
include '../../jwt/jwt_utils.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? '';

if ($authHeader) {
    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if ($jwt && validate_jwt($jwt)) {
        // SQL query to select costs and the associated specialty names
        $sql = "SELECT ct.id, ct.especialidad_id, ct.costo, e.especialidad AS especialidad_nombre
                FROM costos_terapia ct
                JOIN especialidades e ON ct.especialidad_id = e.id";
        $stmt = $conn->prepare($sql);

        if ($stmt->execute()) {
            $costos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($costos)) {
                http_response_code(404); // Not Found
                echo json_encode(["message" => "No se encontraron costos de terapia"]);
            } else {
                echo json_encode($costos);
            }
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(["message" => "Error al ejecutar la consulta"]);
        }
    } else {
        http_response_code(403); // Forbidden
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401); // Unauthorized
    echo json_encode(["message" => "Token no proporcionado"]);
}


