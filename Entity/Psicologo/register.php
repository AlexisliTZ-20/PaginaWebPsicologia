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
        $nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
        $apellido = filter_var($_POST['apellido'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $telefono = filter_var($_POST['telefono'], FILTER_SANITIZE_STRING);
        $N_colegiatura = filter_var($_POST['N_colegiatura'], FILTER_SANITIZE_STRING);

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
            // Obtener detalles del archivo
            $fotoTmpPath = $_FILES['foto']['tmp_name'];
            $fotoName = basename($_FILES['foto']['name']);
            $fotoType = $_FILES['foto']['type'];
        
            // Array de tipos MIME permitidos
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
            // Verificar tipo MIME del archivo
            if (!in_array($fotoType, $allowedMimeTypes)) {
                echo json_encode(["message" => "Solo se permiten archivos JPEG, PNG y GIF"]);
                exit();
            }
        
            // Asegúrate de crear la carpeta `../image/psicologo/` si no existe
            $dest_folder = '../../image/psicologo/';
            if (!file_exists($dest_folder)) {
                mkdir($dest_folder, 0755, true); // Crea la carpeta si no existe
            }
        
            $dest_path = "{$dest_folder}{$fotoName}";
        
            if (move_uploaded_file($fotoTmpPath, $dest_path)) {
                // Archivo movido con éxito
            } else {
                // Error al mover el archivo
                echo json_encode(["message" => "Error al subir la foto"]);
                exit();
            }
        } else {
            $dest_path = null;
        }

        // Verificar si el correo electrónico ya existe
        $sqlCheck = "SELECT COUNT(*) FROM psicologos WHERE email = :email";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':email', $email);
        $stmtCheck->execute();
        $count = $stmtCheck->fetchColumn();

        if ($count > 0) {
            echo json_encode(["message" => "El correo electrónico ya está registrado"]);
            exit();
        }

        // Consulta SQL para insertar los datos
        $sql = "INSERT INTO psicologos (nombre, apellido, email, password, telefono, foto,N_colegiatura) 
                VALUES (:nombre, :apellido, :email, :password, :telefono, :foto, :N_colegiatura)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':N_colegiatura', $N_colegiatura);
        $stmt->bindParam(':foto', $dest_path);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Psicólogo registrado correctamente"]);
        } else {
            echo json_encode(["message" => "Error al registrar psicólogo"]);
        }
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}
