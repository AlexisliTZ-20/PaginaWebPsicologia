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
        // Obtener datos del cuerpo de la solicitud
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            echo json_encode(["message" => "Datos de entrada no válidos"]);
            exit();
        }

        $id = isset($input['id']) ? filter_var($input['id'], FILTER_VALIDATE_INT) : null;

        if ($id === null) {
            echo json_encode(["message" => "ID no proporcionado"]);
            exit();
        }

        // Consulta SQL para eliminar el horario
        $sql = "DELETE FROM horarios WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Horario eliminado correctamente"]);
        } else {
            echo json_encode(["message" => "Error al eliminar horario"]);
        }
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}

