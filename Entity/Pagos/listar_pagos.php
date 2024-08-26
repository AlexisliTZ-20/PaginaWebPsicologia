<?php
include '../../config/bd.php';
include '../../config/cors.php';
include '../../jwt/jwt_utils.php';
require_once '../../utils/url.php';

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
                // Preparar la consulta SQL para listar pagos con datos relacionados
                $sql = "SELECT p.id, p.monto, p.fecha_pago, p.foto_pago, p.cita_id, p.tipoPagoId,
                               pc.nombre AS psicologo_nombre, pc.apellido AS psicologo_apellido,
                               pa.nombre AS paciente_nombre, pa.apellido AS paciente_apellido,
                               tp.tipo_pago AS tipo_pago
                        FROM pagos p
                        INNER JOIN citas c ON p.cita_id = c.id
                        INNER JOIN psicologos pc ON c.psicologo_id = pc.id
                        INNER JOIN pacientes pa ON c.paciente_id = pa.id
                        INNER JOIN psicologo_cuentas tp ON p.tipoPagoId = tp.id";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute();

                // Obtener los resultados como un array asociativo
                $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Definir la URL base de la imagen
                $baseUrl = url('/image/pagos/');

                // Añadir la URL completa de la foto de pago a cada registro
                foreach ($pagos as &$pago) {
                    // Solo añadir la URL si hay un nombre de archivo válido
                    $pago['foto_pago'] = $pago['foto_pago'] ? $baseUrl . $pago['foto_pago'] : null;
                }

                // Devolver los resultados en formato JSON
                http_response_code(200); // OK
                echo json_encode($pagos);
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
