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
        $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : null;
        $texto = isset($_POST['texto']) ? $_POST['texto'] : '';
        $psicologo_id = isset($_POST['psicologo_id']) ? $_POST['psicologo_id'] : '';

        if ($id) {
            // Prepare SQL update statement for text and psicologo_id
            $sql = "UPDATE recomendaciones SET texto = :texto, psicologo_id = :psicologo_id WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':texto', $texto);
            $stmt->bindParam(':psicologo_id', $psicologo_id);

            if ($stmt->execute()) {
                // Handle file upload if present
                if (isset($_FILES['foto_recomendacion']) && $_FILES['foto_recomendacion']['error'] == UPLOAD_ERR_OK) {

                    $targetDir = url('/image/recomendaciones/');
                    $fileName = basename($_FILES['foto_recomendacion']['name']);
                    $targetFile = $targetDir . $fileName;

                    // Move the uploaded file to the target directory
                    if (move_uploaded_file($_FILES['foto_recomendacion']['tmp_name'], $targetFile)) {
                        // Update the file path in the database
                        $sqlUpdatePhoto = "UPDATE recomendaciones SET foto_recomendacion = :foto_recomendacion WHERE id = :id";
                        $stmtPhoto = $conn->prepare($sqlUpdatePhoto);
                        $stmtPhoto->bindParam(':foto_recomendacion', $fileName);
                        $stmtPhoto->bindParam(':id', $id);

                        if ($stmtPhoto->execute()) {
                            echo json_encode(["message" => "Recomendación actualizada correctamente"]);
                        } else {
                            echo json_encode(["message" => "Error al actualizar la foto en la base de datos"]);
                        }
                    } else {
                        echo json_encode(["message" => "Error al mover el archivo"]);
                    }
                } else {
                    echo json_encode(["message" => "Recomendación actualizada correctamente, sin archivo"]);
                }
            } else {
                echo json_encode(["message" => "Error al actualizar la recomendación"]);
            }
        } else {
            echo json_encode(["message" => "ID de recomendación no proporcionado"]);
        }
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}
