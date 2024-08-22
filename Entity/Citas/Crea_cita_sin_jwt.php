<?php
include '../../config/bd.php';
include '../../config/cors.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $sql = "INSERT INTO citas (paciente_id, psicologo_id, fecha, hora_inicio, hora_fin, estado) VALUES (:paciente_id, :psicologo_id, :fecha, :hora_inicio, :hora_fin, :estado)";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':paciente_id' => $data['paciente_id'],
            ':psicologo_id' => $data['psicologo_id'],
            ':fecha' => $data['fecha'],
            ':hora_inicio' => $data['hora_inicio'],
            ':hora_fin' => $data['hora_fin'],
            ':estado' => $data['estado']
        ]);

        $cita_id = $conn->lastInsertId();
        $psicologo_id = $data['psicologo_id']; // Recupera el psicólogo ID del input
        
        echo json_encode([
            'message' => 'Cita creada con éxito',
            'cita_id' => $cita_id,
            'psicologo_id' => $psicologo_id
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Método no permitido']);
}


