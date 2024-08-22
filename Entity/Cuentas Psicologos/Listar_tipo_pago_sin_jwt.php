<?php
include '../../config/bd.php'; // Incluye el archivo de configuración para la conexión a la base de datos

// Configurar las cabeceras de respuesta
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Obtener el ID del psicólogo desde la solicitud GET
$psicologoId = isset($_GET['psicologoId']) ? $_GET['psicologoId'] : '';

if (!$psicologoId) {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "ID del psicólogo es obligatorio"]);
    exit();
}

// Consulta SQL para seleccionar todos los tipos de pago del psicólogo
$sql = "SELECT * FROM psicologo_cuentas WHERE psicologo_id = :psicologoId";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':psicologoId', $psicologoId, PDO::PARAM_INT);

if ($stmt->execute()) {
    $tiposPago = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($tiposPago) {
        // Devolver los resultados en formato JSON
        echo json_encode($tiposPago);
    } else {
        // No se encontraron registros
        echo json_encode([]);
    }
} else {
    // Error en la ejecución de la consulta
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Error al ejecutar la consulta"]);
}
?>
