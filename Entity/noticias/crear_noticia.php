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
        // Check if the request method is POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $psicologo_id = isset($_POST['psicologo_id']) ? filter_var($_POST['psicologo_id'], FILTER_SANITIZE_NUMBER_INT) : null;
            $titulo = isset($_POST['titulo']) ? filter_var($_POST['titulo'], FILTER_SANITIZE_STRING) : '';
            $contenido = isset($_POST['contenido']) ? filter_var($_POST['contenido'], FILTER_SANITIZE_STRING) : '';

            // Handle file upload
            $foto_noticia = '';
            if (isset($_FILES['foto_noticia']) && $_FILES['foto_noticia']['error'] === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['foto_noticia']['tmp_name'];
                $name = basename($_FILES['foto_noticia']['name']);
                $upload_dir = '../../image/noticias/';
                $upload_file = $upload_dir . $name;

                if (move_uploaded_file($tmp_name, $upload_file)) {
                    $foto_noticia = $name;
                } else {
                    http_response_code(500); // Internal Server Error
                    echo json_encode(["message" => "Error al subir la foto"]);
                    exit();
                }
            }

            if ($psicologo_id && $titulo && $contenido) {
                $sql = "INSERT INTO noticias (psicologo_id, titulo, contenido, foto_noticia) VALUES (:psicologo_id, :titulo, :contenido, :foto_noticia)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':psicologo_id', $psicologo_id);
                $stmt->bindParam(':titulo', $titulo);
                $stmt->bindParam(':contenido', $contenido);
                $stmt->bindParam(':foto_noticia', $foto_noticia);

                if ($stmt->execute()) {
                    http_response_code(201); // Created
                    echo json_encode(["message" => "Noticia creada correctamente"]);
                } else {
                    http_response_code(500); // Internal Server Error
                    echo json_encode(["message" => "Error al crear la noticia"]);
                }
             }else {
                http_response_code(400); // Bad Request
                echo json_encode(["message" => "Datos de entrada inválidos"]);
            }
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode(["message" => "Método no permitido"]);
        }
    } else {
        http_response_code(403); // Forbidden
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401); // Unauthorized
    echo json_encode(["message" => "Token no proporcionado"]);
}
