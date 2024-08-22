<?php
// update_costos_terapia.php

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
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        $especialidad_id = $data['especialidad_id'] ?? null;
        $costo = $data['costo'] ?? null;

        if ($id && $especialidad_id && $costo !== null) {
            $sql = "UPDATE costos_terapia SET especialidad_id = :especialidad_id, costo = :costo WHERE id = :id";
            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':especialidad_id', $especialidad_id);
            $stmt->bindParam(':costo', $costo);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Costo de terapia actualizado"]);
            } else {
                http_response_code(500); // Internal Server Error
                echo json_encode(["message" => "Error al actualizar el costo de terapia"]);
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(["message" => "Datos incompletos"]);
        }
    } else {
        http_response_code(403); // Forbidden
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401); // Unauthorized
    echo json_encode(["message" => "Token no proporcionado"]);
}
?>
