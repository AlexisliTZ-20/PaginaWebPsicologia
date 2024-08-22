<?php
include '../../config/bd.php';
include '../../config/cors.php';

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust this as needed

// SQL query to select all psychologists along with their specialty data
$sql = "
    SELECT 
        p.id, 
        p.nombre AS psicologo_nombre, 
        p.apellido AS psicologo_apellido, 
        p.email AS psicologo_email, 
        p.Telefono AS psicologo_telefono,
        p.foto AS psicologo_foto,
        e.especialidad AS especialidad_nombre, 
        e.experiencia AS especialidad_experiencia, 
        e.descripcion AS especialidad_descripcion
    FROM psicologos p
    INNER JOIN especialidades e ON p.id = e.psicologo_id
";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $psicologos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($psicologos)) {
        // No records found
        http_response_code(404); // Not Found
        echo json_encode(["message" => "No se encontraron psicólogos"]);
    } else {
        // Add the full URL for the photo if available
        $baseUrl = 'http://localhost/login/image/psicologo/'; // Adjust based on your environment

        foreach ($psicologos as &$psicologo) {
            if (!empty($psicologo['psicologo_foto'])) {
                $psicologo['psicologo_foto'] = $baseUrl . $psicologo['psicologo_foto'];
            }
        }

        // Records found
        echo json_encode($psicologos);
    }
} catch (PDOException $e) {
    // Handle database errors
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Error al ejecutar la consulta", "error" => $e->getMessage()]);
}

