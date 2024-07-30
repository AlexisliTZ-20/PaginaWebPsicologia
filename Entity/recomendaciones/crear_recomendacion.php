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
        // Obtener datos del formulario
        $psicologo_id = filter_var($_POST['psicologo_id'], FILTER_VALIDATE_INT);
        $texto = filter_var($_POST['texto'], FILTER_SANITIZE_STRING);

        // Manejo de archivo de imagen
        if (isset($_FILES['foto_recomendacion']) && $_FILES['foto_recomendacion']['error'] == UPLOAD_ERR_OK) {
            $fotoTmpPath = $_FILES['foto_recomendacion']['tmp_name'];
            $fotoName = basename($_FILES['foto_recomendacion']['name']);
            $fotoType = $_FILES['foto_recomendacion']['type'];

            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

            if (!in_array($fotoType, $allowedMimeTypes)) {
                echo json_encode(["message" => "Solo se permiten archivos JPEG, PNG y GIF"]);
                exit();
            }

            $dest_folder = '../../image/recomendaciones/';
            if (!file_exists($dest_folder)) {
                mkdir($dest_folder, 0755, true); // Crea la carpeta si no existe
            }

            $dest_path = "{$dest_folder}{$fotoName}";

            if (!move_uploaded_file($fotoTmpPath, $dest_path)) {
                echo json_encode(["message" => "Error al subir la foto"]);
                exit();
            }
        } else {
            $dest_path = null;
        }

        // Consulta SQL para insertar los datos
        $sql = "INSERT INTO recomendaciones (psicologo_id, texto, foto_recomendacion) VALUES (:psicologo_id, :texto, :foto_recomendacion)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':psicologo_id', $psicologo_id);
        $stmt->bindParam(':texto', $texto);
        $stmt->bindParam(':foto_recomendacion', $dest_path);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Recomendación registrada correctamente"]);
        } else {
            echo json_encode(["message" => "Error al registrar recomendación"]);
        }
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}
