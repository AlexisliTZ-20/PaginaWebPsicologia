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
        // SQL query to select all news along with psychologist's name and surname
        $sql = "SELECT n.*, p.nombre AS psicologo_nombre, p.apellido AS psicologo_apellido
                FROM noticias n
                JOIN psicologos p ON n.psicologo_id = p.id";
        $stmt = $conn->prepare($sql);

        if ($stmt->execute()) {
            $news = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($news)) {
                // No records found
                http_response_code(404); // Not Found
                echo json_encode(["message" => "No se encontraron noticias"]);
            } else {
                // Add the full URL for the news photo
                $baseUrl = 'http://localhost/login/image/noticias/'; // Change this URL based on your configuration

                foreach ($news as &$newsItem) {
                    if (!empty($newsItem['foto_noticia'])) {
                        $newsItem['foto_noticia'] = $baseUrl . $newsItem['foto_noticia'];
                    }
                    // Ensure psychologist fields are present
                    $newsItem['psicologo_nombre'] = $newsItem['psicologo_nombre'] ?? null;
                    $newsItem['psicologo_apellido'] = $newsItem['psicologo_apellido'] ?? null;
                }

                // Records found
                echo json_encode($news);
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

