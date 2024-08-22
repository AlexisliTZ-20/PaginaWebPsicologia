<?php
include '../../config/bd.php';
include '../../config/cors.php';
include '../../jwt/jwt_utils.php';

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

// Get Authorization header
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authHeader) {
    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if ($jwt && validate_jwt($jwt)) {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            try {
                // Get the input data
                $input = json_decode(file_get_contents('php://input'), true);

                if (isset($input['id']) && isset($input['estado'])) {
                    $cita_id = intval($input['id']);
                    $estado = $input['estado'];

                    // Prepare the SQL query
                    $sql = "UPDATE citas SET estado = :estado WHERE id = :cita_id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
                    $stmt->bindParam(':cita_id', $cita_id, PDO::PARAM_INT);

                    // Execute the query
                    if ($stmt->execute()) {
                        http_response_code(200); // OK
                        echo json_encode(["message" => "Estado de la cita actualizado correctamente"]);
                    } else {
                        http_response_code(500); // Internal Server Error
                        echo json_encode(["message" => "No se pudo actualizar el estado de la cita"]);
                    }
                } else {
                    http_response_code(400); // Bad Request
                    echo json_encode(["message" => "Datos de entrada inválidos"]);
                }
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
