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
        // Obtener el parámetro de consulta de búsqueda
        $query = isset($_GET['q']) ? filter_var($_GET['q'], FILTER_SANITIZE_STRING) : '';

        // Consulta SQL para buscar horarios y psicólogos
        $sql = "
            SELECT horarios.*, psicologos.nombre AS psicologo_nombre, psicologos.apellido AS psicologo_apellido
            FROM horarios
            JOIN psicologos ON horarios.psicologo_id = psicologos.id
            WHERE horarios.dia_semana LIKE :query 
               OR horarios.hora_inicio LIKE :query 
               OR horarios.hora_fin LIKE :query
               OR psicologos.nombre LIKE :query
               OR psicologos.apellido LIKE :query
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':query', "%$query%");
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

