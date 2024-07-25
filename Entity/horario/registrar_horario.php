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

        $psicologo_id = isset($input['psicologo_id']) ? filter_var($input['psicologo_id'], FILTER_VALIDATE_INT) : null;
        $dia_semana = isset($input['dia_semana']) ? filter_var($input['dia_semana'], FILTER_SANITIZE_STRING) : null;
        $hora_inicio = isset($input['hora_inicio']) ? filter_var($input['hora_inicio'], FILTER_SANITIZE_STRING) : null;
        $hora_fin = isset($input['hora_fin']) ? filter_var($input['hora_fin'], FILTER_SANITIZE_STRING) : null;

        if ($psicologo_id === null || $dia_semana === null || $hora_inicio === null || $hora_fin === null) {
            echo json_encode(["message" => "Faltan datos"]);
            exit();
        }

        // Verificar si psicologo_id existe en la tabla psicologos
        $sqlCheck = "SELECT COUNT(*) FROM psicologos WHERE id = :psicologo_id";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':psicologo_id', $psicologo_id);
        $stmtCheck->execute();
        $count = $stmtCheck->fetchColumn();

        if ($count == 0) {
            echo json_encode(["message" => "El psicólogo especificado no existe"]);
            exit();
        }

        // Consulta SQL para insertar los datos del horario
        $sql = "INSERT INTO horarios (psicologo_id, dia_semana, hora_inicio, hora_fin) 
                VALUES (:psicologo_id, :dia_semana, :hora_inicio, :hora_fin)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':psicologo_id', $psicologo_id);
        $stmt->bindParam(':dia_semana', $dia_semana);
        $stmt->bindParam(':hora_inicio', $hora_inicio);
        $stmt->bindParam(':hora_fin', $hora_fin);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Horario registrado correctamente"]);
        } else {
            echo json_encode(["message" => "Error al registrar horario"]);
        }
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}
