<?php
// delete_costos_terapia.php

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
        $id = $_GET['id'] ?? null;

        if ($id) {
            $sql = "DELETE FROM costos_terapia WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Costo eliminado"]);
            } else {
                http_response_code(500); // Internal Server Error
                echo json_encode(["message" => "Error al eliminar el costo"]);
            }

        } else {
            http_response_code(400); // Bad Request
            echo json_encode(["message" => "ID no proporcionado"]);
        }
    } else {
        http_response_code(403); // Forbidden
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401); // Unauthorized
    echo json_encode(["message" => "Token no proporcionado"]);
}

