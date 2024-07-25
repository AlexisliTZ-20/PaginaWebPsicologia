<?php
include '../../config/bd.php';
include '../../config/cors.php';
include '../../jwt/jwt_utils.php';

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get Authorization header
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authHeader) {
    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if ($jwt && validate_jwt($jwt)) {
        // Obtener parámetros de búsqueda
        $texto = isset($_GET['texto']) ? filter_var($_GET['texto'], FILTER_SANITIZE_STRING) : '';
        $fecha = isset($_GET['fecha']) ? filter_var($_GET['fecha'], FILTER_SANITIZE_STRING) : '';

        // Construir consulta SQL dinámica basada en los parámetros proporcionados
        $sql = "SELECT * FROM recomendaciones WHERE 1=1";
        $params = [];

        if ($texto) {
            $sql .= " AND texto LIKE :texto";
            $params[':texto'] = "%$texto%";
        }

        if ($fecha) {
            $sql .= " AND fecha = :fecha";
            $params[':fecha'] = $fecha;
        }

        $stmt = $conn->prepare($sql);

        // Enlazar parámetros
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        $recommendations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($recommendations);
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}
?>
