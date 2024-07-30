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
        // Obtener el ID del artículo a eliminar
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id > 0) {
            $sql = "DELETE FROM articulos WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Artículo eliminado correctamente"]);
            } else {
                echo json_encode(["message" => "Error al eliminar artículo"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "ID no válido"]);
        }
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}
