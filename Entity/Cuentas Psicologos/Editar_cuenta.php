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
            $input = json_decode(file_get_contents('php://input'), true);
            $id = isset($input['id']) ? filter_var($input['id'], FILTER_SANITIZE_NUMBER_INT) : null;
            $tipo_pago = isset($input['tipo_pago']) ? filter_var($input['tipo_pago'], FILTER_SANITIZE_STRING) : '';
            $titular_cuenta = isset($input['titular_cuenta']) ? filter_var($input['titular_cuenta'], FILTER_SANITIZE_STRING) : '';
            $numero_cuenta = isset($input['numero_cuenta']) ? filter_var($input['numero_cuenta'], FILTER_SANITIZE_STRING) : '';

            if ($id && $tipo_pago && $titular_cuenta && $numero_cuenta) {
                $sql = "UPDATE psicologo_cuentas SET tipo_pago = :tipo_pago, titular_cuenta = :titular_cuenta, numero_cuenta = :numero_cuenta WHERE id = :id";
                $stmt = $conn->prepare($sql);

                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':tipo_pago', $tipo_pago);
                $stmt->bindParam(':titular_cuenta', $titular_cuenta);
                $stmt->bindParam(':numero_cuenta', $numero_cuenta);

                if ($stmt->execute()) {
                    http_response_code(200); // OK
                    echo json_encode(["message" => "Cuenta de psicólogo actualizada con éxito"]);
                } else {
                    http_response_code(500); // Internal Server Error
                    echo json_encode(["message" => "Error al actualizar la cuenta del psicólogo"]);
                }
            } else {
                http_response_code(400); // Bad Request
                echo json_encode(["message" => "Datos de entrada inválidos"]);
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
?>
