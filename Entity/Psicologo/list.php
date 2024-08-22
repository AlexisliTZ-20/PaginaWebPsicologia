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
        try {
            // Consulta SQL para seleccionar los datos, incluyendo la foto
            $sql = "SELECT id, nombre, apellido, telefono, email, foto,N_colegiatura FROM psicologos";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agregar la URL completa a la foto si es necesario
            foreach ($result as &$row) {
                if ($row['foto']) {
                    $row['foto'] = 'http://localhost/login/image/psicologo/' . basename($row['foto']);
                }
            }

            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["message" => "Error al recuperar los datos", "error" => $e->getMessage()]);
        }
    } else {
        http_response_code(403);
        echo json_encode(["message" => "TOKEN EXPIRADO"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}
