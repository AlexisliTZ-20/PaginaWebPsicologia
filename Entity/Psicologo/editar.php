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
        // Parse incoming JSON data
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(["message" => "Datos no proporcionados"]);
            exit;
        }

        $id = isset($data['id']) ? intval($data['id']) : null;
        $nombre = isset($data['nombre']) ? $data['nombre'] : null;
        $apellido = isset($data['apellido']) ? $data['apellido'] : null;
        $especialidad = isset($data['especialidad']) ? $data['especialidad'] : null;
        $telefono = isset($data['telefono']) ? $data['telefono'] : null;
        $email = isset($data['email']) ? $data['email'] : null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "ID de psiquiatra no proporcionado"]);
            exit;
        }

        try {
            $sql = "UPDATE psiquiatras SET nombre = :nombre, apellido = :apellido, especialidad = :especialidad, telefono = :telefono, email = :email WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido', $apellido);
            $stmt->bindParam(':especialidad', $especialidad);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            http_response_code(200);
            echo json_encode(["message" => "Psiquiatra actualizado correctamente"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Error al actualizar psiquiatra", "error" => $e->getMessage()]);
        }
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}

