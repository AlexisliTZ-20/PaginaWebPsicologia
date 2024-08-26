<?php
include '../../config/bd.php';
include '../../config/cors.php';
require_once '../../utils/url.php';

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Consulta SQL para seleccionar todos los artículos junto con los datos del psicólogo
$sql = "
    SELECT 
        a.id, 
        a.titulo, 
        a.contenido, 
        a.fecha, 
        a.foto_articulo, 
        p.nombre AS psicologo_nombre, 
        p.apellido AS psicologo_apellido
    FROM articulos a
    INNER JOIN psicologos p ON a.psicologo_id = p.id
";
$stmt = $conn->prepare($sql);

if ($stmt->execute()) {
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($articles)) {
        // No hay registros
        http_response_code(404); // Not Found
        echo json_encode(["message" => "No se encontraron artículos"]);
    } else {
        // Agregar la URL completa para la foto
        $baseUrl = url('/image/articulos/');

        foreach ($articles as &$article) {
            if (!empty($article['foto_articulo'])) {
                $article['foto_articulo'] = $baseUrl . $article['foto_articulo'];
            }
        }

        // Hay registros
        echo json_encode($articles);
    }
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Error al ejecutar la consulta"]);
}

