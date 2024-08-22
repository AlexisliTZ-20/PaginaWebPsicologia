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
    if (isset($input['id']) && isset($input['paciente_id']) && isset($input['psicologo_id']) && isset($input['fecha']) && isset($input['hora_inicio']) && isset($input['hora_fin']) && isset($input['estado'])) {
        $id = filter_var($input['id'], FILTER_SANITIZE_NUMBER_INT);
        $pacienteId = filter_var($input['paciente_id'], FILTER_SANITIZE_NUMBER_INT);
        $psicologoId = filter_var($input['psicologo_id'], FILTER_SANITIZE_NUMBER_INT);
        $fecha = filter_var($input['fecha'], FILTER_SANITIZE_STRING);
        $horaInicio = filter_var($input['hora_inicio'], FILTER_SANITIZE_STRING);
        $horaFin = filter_var($input['hora_fin'], FILTER_SANITIZE_STRING);
        $estado = filter_var($input['estado'], FILTER_SANITIZE_STRING);

        try {
            // Preparar la consulta SQL para actualizar la cita
            $sql = "UPDATE citas SET paciente_id = :paciente_id, psicologo_id = :psicologo_id, fecha = :fecha, hora_inicio = :hora_inicio, hora_fin = :hora_fin, estado = :estado WHERE id = :id";
            $stmt = $conn->prepare($sql);

            // Enlazar los parámetros
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':paciente_id', $pacienteId, PDO::PARAM_INT);
            $stmt->bindParam(':psicologo_id', $psicologoId, PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmt->bindParam(':hora_inicio', $horaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':hora_fin', $horaFin, PDO::PARAM_STR);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);

            // Ejecutar la consulta
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    http_response_code(200);
                    echo json_encode(["message" => "Cita actualizada con éxito"]);
                } else {
                    http_response_code(404);
                    echo json_encode(["message" => "Cita no encontrada"]);
                }
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Error al actualizar cita"]);
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

