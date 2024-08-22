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
        // Define the SQL query to fetch the data
        $sql = "
            SELECT 
                c.id AS cita_id,
                p.nombre AS paciente_nombre,
                p.apellido AS paciente_apellido,
                p.email AS paciente_correo,
                s.nombre AS psicologo_nombre,
                s.apellido AS psicologo_apellido,
                c.fecha,
                c.hora_inicio,
                c.hora_fin,
                c.estado
            FROM citas c
            JOIN pacientes p ON c.paciente_id = p.id
            JOIN psicologos s ON c.psicologo_id = s.id
        ";

        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($citas);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}
