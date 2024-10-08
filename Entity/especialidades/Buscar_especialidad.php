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
        // Parse incoming query parameters
        $query = isset($_GET['query']) ? $_GET['query'] : '';

        if ($query === null) {
            http_response_code(400);
            echo json_encode(["message" => "Consulta de búsqueda no proporcionada"]);
            exit;
        }

        try {
            // Sanitize the input
            $query = '%' . $conn->quote($query, PDO::PARAM_STR) . '%';

            // SQL query with JOIN to include psicólogos information
            $sql = "SELECT e.*, p.nombre AS psicologo_nombre, p.apellido AS psicologo_apellido 
                    FROM especialidades e
                    JOIN psicologos p ON e.psicologo_id = p.id
                    WHERE e.especialidad LIKE :query 
                    OR e.experiencia LIKE :query 
                    OR e.descripcion LIKE :query
                    OR p.nombre LIKE :query
                    OR p.apellido LIKE :query";
                    
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':query', $query);
            $stmt->execute();
            $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode($especialidades);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Error al buscar especialidades", "error" => $e->getMessage()]);
        }
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}
