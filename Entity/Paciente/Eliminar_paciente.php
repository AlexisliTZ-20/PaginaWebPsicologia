<?php
include '../../config/bd.php';
include '../../config/cors.php';
include '../../jwt/jwt_utils.php';

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

// Get Authorization header
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authHeader) {
    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if ($jwt && validate_jwt($jwt)) {
        // Check if the request method is DELETE
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            // Obtener el ID desde la URL
            $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT) : null;

            if ($id && is_numeric($id)) {
                // Preparar la consulta SQL para eliminar el paciente
                $sql = "DELETE FROM pacientes WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    if ($stmt->rowCount() > 0) {
                        http_response_code(200); // OK
                        echo json_encode(["message" => "Paciente eliminado con éxito"]);
                    } else {
                        http_response_code(404); // Not Found
                        echo json_encode(["message" => "Paciente no encontrado"]);
                    }
                } else {
                    http_response_code(500); // Internal Server Error
                    echo json_encode(["message" => "Error al eliminar el paciente"]);
                }
            } else {
                http_response_code(400); // Bad Request
                echo json_encode(["message" => "ID del paciente no proporcionado o inválido"]);
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
