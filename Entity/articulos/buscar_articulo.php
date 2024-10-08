<?php
include '../../config/bd.php';
include '../../config/cors.php';
include '../../jwt/jwt_utils.php';
require_once '../../utils/url.php';

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get Authorization header
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authHeader) {
    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if ($jwt && validate_jwt($jwt)) {
        // Get search parameters
        $searchText = isset($_GET['search']) ? filter_var($_GET['search'], FILTER_SANITIZE_STRING) : '';

        // Consulta SQL para buscar artículos y psicólogos
        $sql = "
            SELECT a.*, p.nombre AS psicologo_nombre, p.apellido AS psicologo_apellido
            FROM articulos a
            JOIN psicologos p ON a.psicologo_id = p.id
            WHERE (a.titulo LIKE :searchText 
                OR a.contenido LIKE :searchText 
                OR CONCAT(p.nombre, ' ', p.apellido) LIKE :searchText)
        ";
        $stmt = $conn->prepare($sql);
        $searchText = "%$searchText%";
        $stmt->bindParam(':searchText', $searchText);

        if ($stmt->execute()) {
            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($articles)) {
                // No hay registros
                http_response_code(404); // Not Found
                echo json_encode(["message" => "No se encontraron artículos"]);
            } else {
                // Agregar la URL completa para la foto
                $baseUrl = url('/image/articulos/'); // Cambia esta URL según tu configuración

                foreach ($articles as &$article) {
                    if (!empty($article['foto_articulo'])) {
                        $article['foto_articulo'] = $baseUrl . $article['foto_articulo'];
                    }
                    // Agregar la fecha de creación
                    $article['fecha_creacion'] = $article['fecha_creacion'] ?? 'Fecha no disponible'; // Ajusta si el campo tiene otro nombre
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
