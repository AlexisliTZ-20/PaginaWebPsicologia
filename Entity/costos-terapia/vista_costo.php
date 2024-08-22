<?php
include '../../config/bd.php'; // Incluye el archivo de configuración para la conexión a la base de datos
include '../../config/cors.php'; // Incluye el archivo para la configuración de CORS

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Consulta SQL para seleccionar los costos y los nombres de las especialidades
$sql = "
    SELECT 
        ct.id, 
        e.especialidad AS especialidad_nombre, 
        ct.costo
    FROM costos_terapia ct
    INNER JOIN especialidades e ON ct.especialidad_id = e.id
";

$stmt = $conn->prepare($sql);

if ($stmt->execute()) {
    $costos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($costos)) {
        // No hay registros
        http_response_code(404); // Not Found
        echo json_encode(["message" => "No se encontraron costos"]);
    } else {
        // Hay registros
        echo json_encode($costos);
    }
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Error al ejecutar la consulta"]);
}
