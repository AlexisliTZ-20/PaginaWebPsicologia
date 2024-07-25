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
        parse_str(file_get_contents("php://input"), $put_vars);

        $id = filter_var($put_vars['id'], FILTER_VALIDATE_INT);
        $texto = filter_var($put_vars['texto'], FILTER_SANITIZE_STRING);
        $fecha = filter_var($put_vars['fecha'], FILTER_SANITIZE_STRING);
        $foto_recomendacion = !empty($put_vars['foto_recomendacion']) ? $put_vars['foto_recomendacion'] : null;

        // Opcional: manejar actualización de foto

        $sql = "UPDATE recomendaciones SET texto = :texto, fecha = :fecha, foto_recomendacion = :foto_recomendacion WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':texto', $texto);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':foto_recomendacion', $foto_recomendacion);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Recomendación actualizada correctamente"]);
        } else {
            echo json_encode(["message" => "Error al actualizar recomendación"]);
        }
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}
