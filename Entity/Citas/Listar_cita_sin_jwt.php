<?php
include '../../config/bd.php'; // Asegúrate de que esta ruta sea correcta
include '../../config/cors.php'; // Asegúrate de que esta ruta sea correcta

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Obtener la URL completa
    $requestUri = $_SERVER['REQUEST_URI'];

    // Extraer el ID de la URL
    $parts = explode('/', $requestUri);
    $id = isset($parts[count($parts) - 1]) ? filter_var($parts[count($parts) - 1], FILTER_SANITIZE_NUMBER_INT) : '';

    if ($id) {
        try {
            // Preparar la consulta SQL
            $sql = "SELECT id, paciente_id, psicologo_id, fecha, hora_inicio, hora_fin FROM citas WHERE id = :id";
            $stmt = $conn->prepare($sql);
            
            // Enlazar el parámetro
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            // Ejecutar la consulta
            $stmt->execute();

            // Recuperar la cita
            $cita = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cita) {
                http_response_code(200);
                echo json_encode($cita);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Cita no encontrada"]);
            }
        } catch (PDOException $e) {
            // Manejo de errores de la base de datos
            http_response_code(500);
            echo json_encode(["message" => "Error en la base de datos: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "ID inválido"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Método no permitido"]);
}
