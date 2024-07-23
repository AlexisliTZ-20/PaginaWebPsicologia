<?php
include '../../config/bd.php'; // Asegúrate de incluir el archivo correcto para la conexión a tu base de datos
include '../../config/cors.php'; // Archivo para manejar CORS, si es necesario
include '../../jwt/jwt_utils.php'; // Funciones para manejar JWT

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

        // Extract data from JSON
        $nombre = isset($data['nombre']) ? $data['nombre'] : null;
        $apellido = isset($data['apellido']) ? $data['apellido'] : null;
        $email = isset($data['email']) ? $data['email'] : null;
        $password = isset($data['password']) ? $data['password'] : null;
        $especialidad_id = isset($data['especialidad_id']) ? $data['especialidad_id'] : null;
        $telefono = isset($data['telefono']) ? $data['telefono'] : null;
        
        // Obtener el archivo de la foto
        $foto = isset($_FILES['foto']) ? $_FILES['foto'] : null;
        $foto_path = null;

        if (!$nombre || !$apellido || !$email || !$password || !$especialidad_id || !$telefono || !$foto) {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos"]);
            exit;
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Guardar la foto en una ubicación específica (en la carpeta 'image/psicologo')
            $foto_name = $foto['name'];
            $foto_tmp_name = $foto['tmp_name'];
            $foto_path ='' . $foto_name;  // Ruta donde guardar la foto en 'image/psicologo/'

            move_uploaded_file($foto_tmp_name, $foto_path);

            // Insertar en la base de datos
            $sql = "INSERT INTO psicologos (nombre, apellido, email, password, especialidad_id, telefono, foto) VALUES (:nombre, :apellido, :email, :password, :especialidad_id, :telefono, :foto)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido', $apellido);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':especialidad_id', $especialidad_id);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':foto', $foto_path);  // Guarda la ruta de la foto en la base de datos

            $stmt->execute();

            http_response_code(200);
            echo json_encode(["message" => "Psicólogo registrado correctamente"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Error al registrar psicólogo", "error" => $e->getMessage()]);
        }
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}
