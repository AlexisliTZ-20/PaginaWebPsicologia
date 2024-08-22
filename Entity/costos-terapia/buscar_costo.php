<?php

include '../../config/bd.php';
include '../../config/cors.php';
include '../../jwt/jwt_utils.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? '';

if ($authHeader) {
    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if ($jwt && validate_jwt($jwt)) {
        // Obtener el parámetro de búsqueda único
        $searchTerm = $_GET['search'] ?? '';

        // Construir la consulta SQL
        $sql = "SELECT ct.*, e.especialidad AS especialidad_nombre FROM costos_terapia ct
                JOIN especialidades e ON ct.especialidad_id = e.id
                WHERE 1=1";

        $params = [];
        
        if ($searchTerm) {
            $sql .= " AND (e.especialidad LIKE :searchTerm OR ct.costo LIKE :searchTerm)";
            $params[':searchTerm'] = "%$searchTerm%";
        }

        $stmt = $conn->prepare($sql);

        // Asignar valores a los parámetros
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($result) {
                echo json_encode($result);
            } else {
                http_response_code(404); // Not Found
                echo json_encode(["message" => "No se encontraron resultados"]);
            }
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(["message" => "Error al ejecutar la consulta"]);
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

