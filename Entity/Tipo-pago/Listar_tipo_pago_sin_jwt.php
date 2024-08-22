<?php
include '../../config/bd.php'; // Incluye el archivo de configuración para la conexión a la base de datos
include '../../config/cors.php'; // Incluye el archivo para la configuración de CORS

// Configurar las cabeceras de respuesta
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Consulta SQL para seleccionar todos los tipos de pago
$sql = "
    SELECT *
    FROM tipo_pago
";

$stmt = $conn->prepare($sql);

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

