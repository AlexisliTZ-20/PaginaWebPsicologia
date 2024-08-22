<?php
// listar_cuentas.php
include '../../config/bd.php';
include '../../config/cors.php';
include '../../jwt/jwt_utils.php';

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

// Get Authorization header
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authHeader) {
    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if ($jwt && validate_jwt($jwt)) {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            try {
                // Query to get cuentas with psicologo names
                $sql = "
                    SELECT c.id, c.psicologo_id, c.tipo_pago, c.titular_cuenta, c.numero_cuenta,
                           p.nombre AS psicologo_nombre, p.apellido AS psicologo_apellido
                    FROM psicologo_cuentas c
                    JOIN psicologos p ON c.psicologo_id = p.id
                ";
                $stmt = $conn->prepare($sql);
                $stmt->execute();

                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                http_response_code(200); // OK
                echo json_encode($resultados);
            } catch (PDOException $e) {
                http_response_code(500); // Internal Server Error
                echo json_encode(["message" => "Error en la base de datos: " . $e->getMessage()]);
            }
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode(["message" => "Método no permitido"]);
        }
    } else {
        http_response_code(403); // Forbidden
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401); // Unauthorized
    echo json_encode(["message" => "Token no proporcionado"]);
}

