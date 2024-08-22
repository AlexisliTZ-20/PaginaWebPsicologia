<?php
include '../../config/bd.php'; // Incluye el archivo de configuración para la conexión a la base de datos
include '../../config/cors.php'; // Incluye el archivo para la configuración de CORS

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Obtener el ID de la cita del parámetro de consulta
$citaId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($citaId <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'ID de cita inválido']);
    exit();
}

// Consulta SQL para seleccionar la cita por ID
$sql = "SELECT * FROM citas WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $citaId, PDO::PARAM_INT);

if ($stmt->execute()) {
    $cita = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cita) {
        echo json_encode($cita);
    } else {
        http_response_code(404); // Not Found
        echo json_encode(['message' => 'Cita no encontrada']);
    }
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['message' => 'Error al ejecutar la consulta']);
}
?>
