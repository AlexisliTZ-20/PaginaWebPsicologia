<?php
include '../../config/bd.php';
include '../../config/cors.php';
include '../../jwt/jwt_utils.php';

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

// Get Authorization header
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authHeader) {
    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if ($jwt && validate_jwt($jwt)) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $psicologo_id = isset($input['psicologo_id']) ? filter_var($input['psicologo_id'], FILTER_SANITIZE_NUMBER_INT) : null;
            $tipo_pago = isset($input['tipo_pago']) ? filter_var($input['tipo_pago'], FILTER_SANITIZE_STRING) : '';
            $titular_cuenta = isset($input['titular_cuenta']) ? filter_var($input['titular_cuenta'], FILTER_SANITIZE_STRING) : '';
            $numero_cuenta = isset($input['numero_cuenta']) ? filter_var($input['numero_cuenta'], FILTER_SANITIZE_STRING) : '';

            if ($psicologo_id && $tipo_pago && $titular_cuenta && $numero_cuenta) {
                $sql = "INSERT INTO psicologo_cuentas (psicologo_id, tipo_pago, titular_cuenta, numero_cuenta) 
                        VALUES (:psicologo_id, :tipo_pago, :titular_cuenta, :numero_cuenta)";
                $stmt = $conn->prepare($sql);

                $stmt->bindParam(':psicologo_id', $psicologo_id);
                $stmt->bindParam(':tipo_pago', $tipo_pago);
                $stmt->bindParam(':titular_cuenta', $titular_cuenta);
                $stmt->bindParam(':numero_cuenta', $numero_cuenta);

                if ($stmt->execute()) {
                    http_response_code(201); // Created
                    echo json_encode(["message" => "Cuenta de psicólogo creada exitosamente"]);
                } else {
                    http_response_code(500); // Internal Server Error
                    echo json_encode(["message" => "Error al crear la cuenta del psicólogo"]);
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
