<?php
include '../../config/bd.php';
include '../../config/cors.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$sql = "
    SELECT 
        n.id, 
        n.titulo, 
        n.contenido, 
        n.fecha, 
        n.foto_noticia, 
        p.nombre AS psicologo_nombre, 
        p.apellido AS psicologo_apellido
    FROM noticias n
    INNER JOIN psicologos p ON n.psicologo_id = p.id
";
$stmt = $conn->prepare($sql);

if ($stmt->execute()) {
    $noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($noticias)) {
        // No hay registros
        http_response_code(404); // Not Found
        echo json_encode(["message" => "No se encontraron noticias"]);
    } else {
        // Agregar la URL completa para la foto
        $baseUrl = 'http://localhost/login/image/noticias/'; // Cambia esta URL según tu configuración

        foreach ($noticias as &$noticia) {
            if (!empty($noticia['foto_noticia'])) {
                $noticia['foto_noticia'] = $baseUrl . $noticia['foto_noticia'];
            }
        }

        // Hay registros
        echo json_encode($noticias);
    }
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Error al ejecutar la consulta"]);
}
