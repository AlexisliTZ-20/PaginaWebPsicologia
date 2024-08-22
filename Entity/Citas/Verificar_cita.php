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
                // Obtener la entrada JSON del cuerpo de la solicitud
                $data = json_decode(file_get_contents('php://input'), true);

                // Validar los datos
                $cita_id = isset($data['id']) ? intval($data['id']) : null;
                $estado = isset($data['estado']) ? $data['estado'] : null;

                if ($cita_id && $estado) {
                    // Preparar la consulta SQL para actualizar el estado de la cita
                    $sql = "UPDATE citas SET estado = :estado WHERE id = :id";

                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
                    $stmt->bindParam(':id', $cita_id, PDO::PARAM_INT);
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        http_response_code(200); // OK
                        echo json_encode(["message" => "Cita actualizada exitosamente"]);
                    } else {
                        http_response_code(404); // Not Found
                        echo json_encode(["message" => "Cita no encontrada"]);
                    }
                } else {
                    http_response_code(400); // Bad Request
                    echo json_encode(["message" => "Datos incompletos"]);
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
