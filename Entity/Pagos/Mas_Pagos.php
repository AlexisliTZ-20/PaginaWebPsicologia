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
                // Preparar la consulta SQL para obtener psicólogos con más pagos, solo para citas confirmadas
                $sql = "SELECT 
                            pc.id AS psicologo_id,
                            CONCAT(pc.nombre, ' ', pc.apellido) AS psicologo_nombre,
                            SUM(p.monto) AS total_ganancia
                        FROM 
                            pagos p
                        INNER JOIN 
                            citas c ON p.cita_id = c.id
                        INNER JOIN 
                            psicologos pc ON c.psicologo_id = pc.id
                        WHERE 
                            c.estado = 'confirmada'
                        GROUP BY 
                            pc.id, pc.nombre, pc.apellido
                        ORDER BY 
                            total_ganancia DESC";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute();

                // Obtener los resultados como un array asociativo
                $psicologos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Devolver los resultados en formato JSON
                http_response_code(200); // OK
                echo json_encode($psicologos);
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
?>
