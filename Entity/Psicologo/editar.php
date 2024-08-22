<?php
include '../../config/bd.php';
include '../../config/cors.php';
include '../../jwt/jwt_utils.php';

// Configura las cabeceras de respuesta
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Obtén el encabezado de autorización
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authHeader) {
    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if ($jwt && validate_jwt($jwt)) {
        // Extrae el ID de los parámetros de la consulta
        $id = isset($_POST['id']) ? intval($_POST['id']) : null; // Cambia $_GET a $_POST para FormData

        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "ID no proporcionado"]);
            exit;
        }

        // Verifica que se han recibido datos y archivos
        $fotoPath = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $foto = $_FILES['foto'];

            // Valida el tipo de imagen
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($foto['type'], $allowedMimeTypes)) {
                http_response_code(400);
                echo json_encode(["message" => "Solo se permiten archivos JPEG, PNG y GIF"]);
                exit();
            }

            // Genera un nombre y ruta de archivo únicos
            $fotoName = uniqid() . '.' . pathinfo($foto['name'], PATHINFO_EXTENSION);
            $dest_folder = '../../image/psicologo/';
            if (!file_exists($dest_folder)) {
                mkdir($dest_folder, 0777, true);
            }
            $fotoPath = "{$dest_folder}{$fotoName}";

            // Mueve el archivo a la carpeta destino
            if (!move_uploaded_file($foto['tmp_name'], $fotoPath)) {
                http_response_code(500);
                echo json_encode(["message" => "Error al subir la foto"]);
                exit();
            }
        }

        // Analiza los datos JSON entrantes
        $data = $_POST; // Cambia file_get_contents a $_POST para FormData
        $data['foto'] = $fotoPath;

        // Verifica si se han proporcionado los datos necesarios
        $nombre = isset($data['nombre']) ? $data['nombre'] : null;
        $apellido = isset($data['apellido']) ? $data['apellido'] : null;
        $telefono = isset($data['telefono']) ? $data['telefono'] : null;
        $N_colegiatura = isset($data['N_colegiatura']) ? $data['N_colegiatura'] : null;
        $email = isset($data['email']) ? $data['email'] : null;
        $password = isset($data['password']) ? $data['password'] : null;

        try {
            // Consulta SQL de actualización
            $sql = "UPDATE psicologos SET nombre = :nombre, apellido = :apellido, email = :email, telefono = :telefono, N_colegiatura = :N_colegiatura" . ($fotoPath ? ", foto = :foto" : "") . ($password ? ", password = :password" : "") . " WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido', $apellido);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':N_colegiatura', $N_colegiatura);
            if ($fotoPath) {
                $stmt->bindParam(':foto', $fotoPath);
            }
            if ($password) {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt->bindParam(':password', $hash);
            }
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            http_response_code(200);
            echo json_encode(["message" => "Psicólogo actualizado correctamente"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Error al actualizar psicólogo", "error" => $e->getMessage()]);
        }
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}

