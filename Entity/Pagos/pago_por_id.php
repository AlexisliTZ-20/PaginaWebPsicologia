<?php
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
                // Obtener el ID de la cita desde la consulta GET
                $cita_id = isset($_GET['cita_id']) ? intval($_GET['cita_id']) : null;

                if ($cita_id) {
                    // Preparar la consulta SQL para listar el pago por cita_id
                    $sql = "SELECT p.id, p.monto, p.fecha_pago, p.foto_pago, p.cita_id, p.tipoPagoId,pcu.tipo_pago,
                                   pc.nombre AS psicologo_nombre, pc.apellido AS psicologo_apellido
                            FROM pagos p
                            INNER JOIN citas c ON p.cita_id = c.id
                            INNER JOIN psicologos pc ON c.psicologo_id = pc.id
                            INNER JOIN psicologo_cuentas pcu on pcu.id=p.tipoPagoId
                            WHERE p.cita_id = :cita_id";

                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':cita_id', $cita_id, PDO::PARAM_INT);
                    $stmt->execute();

                    $pago = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($pago) {
                        // Definir la URL base de la imagen
                        $baseUrl = 'http://localhost/login/image/pagos/';
                        $pago['foto_pago'] = $pago['foto_pago'] ? $baseUrl . $pago['foto_pago'] : null;

                        http_response_code(200); // OK
                        echo json_encode($pago);
                    } else {
                        http_response_code(404); // Not Found
                        echo json_encode(["message" => "Pago no encontrado"]);
                    }
                } else {
                    http_response_code(400); // Bad Request
                    echo json_encode(["message" => "ID no proporcionado"]);
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
