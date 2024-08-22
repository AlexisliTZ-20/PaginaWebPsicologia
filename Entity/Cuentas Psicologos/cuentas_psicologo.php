<?php
include '../../config/bd.php';
include '../../config/cors.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $psicologo_id = isset($_GET['psicologo_id']) ? intval($_GET['psicologo_id']) : 0;

    if ($psicologo_id > 0) {
        $sql = "SELECT * FROM psicologo_cuentas WHERE psicologo_id = :psicologo_id";

        try {
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':psicologo_id', $psicologo_id, PDO::PARAM_INT);
            $stmt->execute();

            $cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($cuentas);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'ID de psicólogo inválido']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Método no permitido']);
}
