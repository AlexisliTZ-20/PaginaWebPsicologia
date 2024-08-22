<?php
include '../../config/bd.php'; // Asegúrate de que esta ruta sea correcta
include '../../config/cors.php'; // Asegúrate de que esta ruta sea correcta

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Leer los datos de la solicitud
    $input = json_decode(file_get_contents('php://input'), true);

    // Verificar si los datos se recibieron correctamente
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(["message" => "Error en la solicitud JSON: " . json_last_error_msg()]);
        exit;
    }

    // Validar los datos recibidos
    if (isset($input['id'], $input['nombre'], $input['apellido'], $input['email'], $input['telefono'])) {
        $id = filter_var($input['id'], FILTER_SANITIZE_NUMBER_INT);
        $nombre = filter_var($input['nombre'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $apellido = filter_var($input['apellido'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
        $telefono = filter_var($input['telefono'], FILTER_SANITIZE_STRING);
        $Telefono_Emergencia = isset($input['Telefono_Emergencia']) ? filter_var($input['Telefono_Emergencia'], FILTER_SANITIZE_STRING) : null;

        try {
            // Preparar la consulta SQL para actualizar el paciente
            $sql = "UPDATE pacientes SET nombre = :nombre, apellido = :apellido, email = :email, telefono = :telefono, Telefono_Emergencia = :Telefono_Emergencia WHERE id = :id";
            $stmt = $conn->prepare($sql);

            // Enlazar los parámetros
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':apellido', $apellido, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':telefono', $telefono, PDO::PARAM_STR);
            $stmt->bindParam(':Telefono_Emergencia', $Telefono_Emergencia, PDO::PARAM_STR);

            // Ejecutar la consulta
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    http_response_code(200);
                    echo json_encode(["message" => "Paciente actualizado con éxito"]);
                } else {
                    http_response_code(404);
                    echo json_encode(["message" => "Paciente no encontrado o sin cambios"]);
                }
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Error al actualizar paciente"]);
            }
        } catch (PDOException $e) {
            // Manejo de errores de la base de datos
            http_response_code(500);
            echo json_encode(["message" => "Error en la base de datos: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Datos incompletos"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Método no permitido"]);
}

