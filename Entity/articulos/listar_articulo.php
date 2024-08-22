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
        // Consulta SQL para seleccionar todos los artículos junto con el nombre y apellido del psicólogo
        $sql = "SELECT a.*, p.nombre AS psicologo_nombre, p.apellido AS psicologo_apellido
                FROM articulos a
                JOIN psicologos p ON a.psicologo_id = p.id";
        $stmt = $conn->prepare($sql);

        if ($stmt->execute()) {
            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($articles)) {
                // No hay registros
                http_response_code(404); // Not Found
                echo json_encode(["message" => "No se encontraron artículos"]);
            } else {
                // Agregar la URL completa para la foto
                $baseUrl = 'http://localhost/login/image/articulos/'; // Cambia esta URL según tu configuración

                foreach ($articles as &$article) {
                    if (!empty($article['foto_articulo'])) {
                        $article['foto_articulo'] = $baseUrl . $article['foto_articulo'];
                    }
                    // Asegurarse de que los campos del psicólogo estén presentes
                    $article['psicologo_nombre'] = $article['psicologo_nombre'] ?? null;
                    $article['psicologo_apellido'] = $article['psicologo_apellido'] ?? null;
                }

                // Hay registros
                echo json_encode($articles);
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
