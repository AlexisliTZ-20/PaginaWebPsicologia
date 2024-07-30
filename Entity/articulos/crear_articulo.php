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
        $titulo = filter_var($_POST['titulo'], FILTER_SANITIZE_STRING);
        $contenido = filter_var($_POST['contenido'], FILTER_SANITIZE_STRING);

        // Manejo de archivo de imagen
        if (isset($_FILES['foto_articulo']) && $_FILES['foto_articulo']['error'] == UPLOAD_ERR_OK) {
            $fotoTmpPath = $_FILES['foto_articulo']['tmp_name'];
            $fotoName = basename($_FILES['foto_articulo']['name']);
            $fotoType = $_FILES['foto_articulo']['type'];

            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

            if (!in_array($fotoType, $allowedMimeTypes)) {
                echo json_encode(["message" => "Solo se permiten archivos JPEG, PNG y GIF"]);
                exit();
            }

            $dest_folder = '../../image/articulos/';
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
        $sql = "INSERT INTO articulos (psicologo_id, titulo, contenido, foto_articulo) 
                VALUES (:psicologo_id, :titulo, :contenido, :foto_articulo)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':psicologo_id', $psicologo_id);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':contenido', $contenido);
        $stmt->bindParam(':foto_articulo', $dest_path);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Artículo creado correctamente"]);
        } else {
            echo json_encode(["message" => "Error al crear artículo"]);
        }
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}
