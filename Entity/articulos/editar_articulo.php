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
        $titulo = filter_var($put_vars['titulo'], FILTER_SANITIZE_STRING);
        $contenido = filter_var($put_vars['contenido'], FILTER_SANITIZE_STRING);
        $fecha = filter_var($put_vars['fecha'], FILTER_SANITIZE_STRING);
        $foto_articulo = !empty($put_vars['foto_articulo']) ? $put_vars['foto_articulo'] : null;

        // Opcional: manejar actualización de foto

        $sql = "UPDATE articulos SET titulo = :titulo, contenido = :contenido, fecha = :fecha, foto_articulo = :foto_articulo WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':contenido', $contenido);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':foto_articulo', $foto_articulo);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Artículo actualizado correctamente"]);
        } else {
            echo json_encode(["message" => "Error al actualizar artículo"]);
        }
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}