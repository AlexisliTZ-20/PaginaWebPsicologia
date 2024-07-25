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
        $psicologo_id = isset($input['psicologo_id']) ? filter_var($input['psicologo_id'], FILTER_VALIDATE_INT) : null;
        $dia_semana = isset($input['dia_semana']) ? filter_var($input['dia_semana'], FILTER_SANITIZE_STRING) : null;
        $hora_inicio = isset($input['hora_inicio']) ? filter_var($input['hora_inicio'], FILTER_SANITIZE_STRING) : null;
        $hora_fin = isset($input['hora_fin']) ? filter_var($input['hora_fin'], FILTER_SANITIZE_STRING) : null;

        if ($id === null || $psicologo_id === null || $dia_semana === null || $hora_inicio === null || $hora_fin === null) {
            echo json_encode(["message" => "Faltan datos"]);
            exit();
        }

        // Consulta SQL para actualizar los datos del horario
        $sql = "UPDATE horarios SET psicologo_id = :psicologo_id, dia_semana = :dia_semana, hora_inicio = :hora_inicio, hora_fin = :hora_fin WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':psicologo_id', $psicologo_id);
        $stmt->bindParam(':dia_semana', $dia_semana);
        $stmt->bindParam(':hora_inicio', $hora_inicio);
        $stmt->bindParam(':hora_fin', $hora_fin);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Horario actualizado correctamente"]);
        } else {
            echo json_encode(["message" => "Error al actualizar horario"]);
        }
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}
