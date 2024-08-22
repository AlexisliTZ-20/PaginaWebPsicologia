<?php
include '../../config/bd.php';
include '../../config/cors.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $psicologo_id = isset($_GET['psicologo_id']) ? filter_var($_GET['psicologo_id'], FILTER_VALIDATE_INT) : null;
    $fecha = isset($_GET['fecha']) ? filter_var($_GET['fecha'], FILTER_SANITIZE_STRING) : null;

    try {
        $sql = "SELECT * FROM horarios WHERE psicologo_id = :psicologo_id";
        
        if ($fecha) {
            // Convertir la fecha al nombre del día en español
            $fechaTimestamp = strtotime($fecha);
            $diaSemanaIngles = strftime('%A', $fechaTimestamp);
            
            $diasEnEspañol = [
                'Monday' => 'Lunes',
                'Tuesday' => 'Martes',
                'Wednesday' => 'Miércoles',
                'Thursday' => 'Jueves',
                'Friday' => 'Viernes',
                'Saturday' => 'Sábado',
                'Sunday' => 'Domingo'
            ];
            
            $diaSemana = $diasEnEspañol[$diaSemanaIngles] ?? null;

            if ($diaSemana) {
                $sql .= " AND dia_semana = :dia_semana";
            }
        }

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':psicologo_id', $psicologo_id, PDO::PARAM_INT);
        
        if ($diaSemana) {
            $stmt->bindParam(':dia_semana', $diaSemana, PDO::PARAM_STR);
        }

        $stmt->execute();
        $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($horarios);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Error en la base de datos: " . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Método no permitido"]);
}
