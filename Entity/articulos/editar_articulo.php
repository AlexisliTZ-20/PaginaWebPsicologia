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
        // Check if the ID is provided
        $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "ID del artículo no proporcionado o inválido"]);
            exit();
        }

        // Sanitize other inputs
        $psicologo_id = isset($_POST['psicologo_id']) ? filter_var($_POST['psicologo_id'], FILTER_VALIDATE_INT) : null;
        $titulo = isset($_POST['titulo']) ? filter_var($_POST['titulo'], FILTER_SANITIZE_STRING) : '';
        $contenido = isset($_POST['contenido']) ? filter_var($_POST['contenido'], FILTER_SANITIZE_STRING) : '';
        $foto_articulo = isset($_FILES['foto_articulo']) ? $_FILES['foto_articulo']['name'] : null;

        // Update article
        $sql = "UPDATE articulos SET psicologo_id = :psicologo_id, titulo = :titulo, contenido = :contenido, foto_articulo = :foto_articulo WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':psicologo_id', $psicologo_id);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':contenido', $contenido);
        $stmt->bindParam(':foto_articulo', $foto_articulo);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            if ($foto_articulo) {
                $targetDir = url('/image/articulos/');
                $targetFile = $targetDir . basename($foto_articulo);

                // Move the uploaded file to the target directory
                if (move_uploaded_file($_FILES['foto_articulo']['tmp_name'], $targetFile)) {
                    echo json_encode(["message" => "Artículo actualizado correctamente"]);
                } else {
                    echo json_encode(["message" => "Error al mover el archivo"]);
                }
            } else {
                echo json_encode(["message" => "Artículo actualizado correctamente"]);
            }
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
