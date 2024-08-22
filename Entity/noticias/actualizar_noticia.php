<?php
include '../../config/bd.php';
include '../../config/cors.php';
include '../../jwt/jwt_utils.php';

// Establecer los encabezados de la respuesta
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Obtener el encabezado de autorización
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authHeader) {
    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if ($jwt && validate_jwt($jwt)) {
        // Verificar si se proporciona el ID
        $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "ID de la noticia no proporcionado o inválido"]);
            exit();
        }

        // Sanitizar otros inputs
        $psicologo_id = isset($_POST['psicologo_id']) ? filter_var($_POST['psicologo_id'], FILTER_VALIDATE_INT) : null;
        $titulo = isset($_POST['titulo']) ? filter_var($_POST['titulo'], FILTER_SANITIZE_STRING) : '';
        $contenido = isset($_POST['contenido']) ? filter_var($_POST['contenido'], FILTER_SANITIZE_STRING) : '';
        $foto_noticia = isset($_FILES['foto_noticia']) ? $_FILES['foto_noticia']['name'] : null;


        // Consulta SQL para actualizar la noticia
        $sql = "UPDATE noticias SET 
                    psicologo_id = :psicologo_id, 
                    titulo = :titulo, 
                    contenido = :contenido" . 
                    ($foto_noticia ? ", foto_noticia = :foto_noticia" : "") . 
                " WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':psicologo_id', $psicologo_id);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':contenido', $contenido);


        if ($foto_noticia) {
            $stmt->bindParam(':foto_noticia', $foto_noticia);
        }
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            if ($foto_noticia) {
                $targetDir = "C:/xampp/htdocs/login/image/noticias/";
                $targetFile = $targetDir . basename($foto_noticia);

                // Mover el archivo subido al directorio de destino
                if (move_uploaded_file($_FILES['foto_noticia']['tmp_name'], $targetFile)) {
                    echo json_encode(["message" => "Noticia actualizada correctamente"]);
                } else {
                    echo json_encode(["message" => "Error al mover el archivo"]);
                }
            } else {
                echo json_encode(["message" => "Noticia actualizada correctamente"]);
            }
        } else {
            http_response_code(500); // Error Interno del Servidor
            echo json_encode(["message" => "Error al actualizar la noticia"]);
        }
    } else {
        http_response_code(403); // Prohibido
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401); // No Autorizado
    echo json_encode(["message" => "Token no proporcionado"]);
}
?>
