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
        $psicologo_id = isset($_GET['psicologo_id']) ? filter_var($_GET['psicologo_id'], FILTER_VALIDATE_INT) : null;

        if ($psicologo_id) {
            // Query to get horarios and associated psicologo details
            $sql = "
                SELECT horarios.*, psicologos.nombre AS psicologo_nombre, psicologos.apellido AS psicologo_apellido
                FROM horarios
                JOIN psicologos ON horarios.psicologo_id = psicologos.id
                WHERE horarios.psicologo_id = :psicologo_id
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':psicologo_id', $psicologo_id);
        } else {
            // Query to get all horarios and associated psicologo details
            $sql = "
                SELECT horarios.*, psicologos.nombre AS psicologo_nombre, psicologos.apellido AS psicologo_apellido
                FROM horarios
                JOIN psicologos ON horarios.psicologo_id = psicologos.id
            ";
            $stmt = $conn->prepare($sql);
        }

        $stmt->execute();
        $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($horarios);
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}
