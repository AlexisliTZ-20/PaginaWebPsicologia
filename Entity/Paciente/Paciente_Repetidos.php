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
        // Check if the request method is GET
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            try {
                // Preparar la consulta SQL para contar repeticiones por nombre, apellido y correo electrónico,
                // solo para pacientes que han reservado una cita confirmada
                $sql = "SELECT p.nombre, p.apellido, p.email, COUNT(*) as repeticiones
                        FROM pacientes p
                        INNER JOIN citas c ON p.id = c.paciente_id
                        WHERE c.estado = 'confirmada'
                        GROUP BY p.nombre, p.apellido, p.email
                        HAVING COUNT(*) > 1";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute();

                // Obtener los resultados como un array asociativo
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Devolver los resultados en formato JSON
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
?>
