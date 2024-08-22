<?php
include '../../config/bd.php'; // Asegúrate de que esta ruta sea correcta
include '../../config/cors.php'; // Asegúrate de que esta ruta sea correcta

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture raw POST data
    $rawPostData = file_get_contents('php://input');
    $data = json_decode($rawPostData, true);

    // Debugging output
    error_log("Raw POST data: $rawPostData");
    error_log("Decoded data: " . print_r($data, true));

    $nombre = isset($data['nombre']) ? filter_var($data['nombre'], FILTER_SANITIZE_STRING) : '';
    $apellido = isset($data['apellido']) ? filter_var($data['apellido'], FILTER_SANITIZE_STRING) : '';
    $email = isset($data['email']) ? filter_var($data['email'], FILTER_SANITIZE_EMAIL) : '';
    $telefono = isset($data['telefono']) ? filter_var($data['telefono'], FILTER_SANITIZE_STRING) : '';
    $Telefono_Emergencia = isset($data['Telefono_Emergencia']) ? filter_var($data['Telefono_Emergencia'], FILTER_SANITIZE_STRING) : '';

    if ($nombre && $apellido && $email && $telefono) {
        $sql = "INSERT INTO pacientes (nombre, apellido, email, telefono, Telefono_Emergencia) VALUES (:nombre, :apellido, :email, :telefono, :Telefono_Emergencia)";
        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':Telefono_Emergencia', $telefono);

        if ($stmt->execute()) {
            $lastInsertId = $conn->lastInsertId(); 
            http_response_code(201);
            echo json_encode([
                "message" => "Paciente registrado correctamente",
                "id" => $lastInsertId 
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al registrar el paciente"]);
        }
    } else {
        http_response_code(400);
        echo json_encode([
            "message" => "Datos de entrada inválidos",
            "nombre" => $nombre,
            "apellido" => $apellido,
            "email" => $email,
            "telefono" => $telefono,
            "Telefono_Emergencia" => $telefono
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Método no permitido"]);
}
