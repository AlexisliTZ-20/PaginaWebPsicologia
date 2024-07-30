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
        // Obtener el parámetro 'id' de la URL
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if ($id === false) {
            echo json_encode(["message" => "ID inválido"]);
            exit();
        }

        // Consulta SQL para eliminar la recomendación
        $sql = "DELETE FROM recomendaciones WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Recomendación eliminada correctamente"]);
        } else {
            echo json_encode(["message" => "Error al eliminar la recomendación"]);
        }
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}

