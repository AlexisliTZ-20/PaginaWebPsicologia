<?php
include '../../config/bd.php';
include '../../config/cors.php';

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Consulta SQL para seleccionar todas las recomendaciones junto con los datos del psicólogo
$sql = "
    SELECT 
        r.id, 
        r.texto AS contenido, 
        r.fecha AS fecha, 
        r.foto_recomendacion AS foto_recomendacion, 
        p.nombre AS psicologo_nombre, 
        p.apellido AS psicologo_apellido
    FROM recomendaciones r
    INNER JOIN psicologos p ON r.psicologo_id = p.id
";

$stmt = $conn->prepare($sql);

if ($stmt->execute()) {
    $recomendaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($recomendaciones)) {
        // No hay registros
        http_response_code(404); // Not Found
        echo json_encode(["message" => "No se encontraron recomendaciones"]);
    } else {
        // Agregar la URL completa para la foto
        $baseUrl = 'http://localhost/login/image/recomendaciones/'; // Cambia esta URL según tu configuración

        foreach ($recomendaciones as &$recomendacion) {
            if (!empty($recomendacion['foto_recomendacion'])) {
                $recomendacion['foto_recomendacion'] = $baseUrl . $recomendacion['foto_recomendacion'];
            }
        }

        // Hay registros
        echo json_encode($recomendaciones);
    }
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Error al ejecutar la consulta"]);
}
