<?php
include '../../config/bd.php';
include '../../config/cors.php';
include '../../jwt/jwt_utils.php';

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get Authorization header
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authHeader) {
    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if ($jwt && validate_jwt($jwt)) {
        // Obtener parámetros de búsqueda
        $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
        $searchText = isset($_GET['search']) ? filter_var($_GET['search'], FILTER_SANITIZE_STRING) : null;

        $baseImageUrl = 'http://localhost/login/image/recomendaciones/'; // Base URL para las imágenes

        if ($id) {
            // Obtener recomendación específica por ID
            $sql = "SELECT r.id, r.texto, r.foto_recomendacion, r.fecha, r.psicologo_id, p.nombre, p.apellido
                    FROM recomendaciones r
                    JOIN psicologos p ON r.psicologo_id = p.id
                    WHERE r.id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $recommendation = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($recommendation) {
                // Construir la URL completa para la imagen
                if (isset($recommendation['foto_recomendacion']) && !empty($recommendation['foto_recomendacion'])) {
                    $recommendation['foto_recomendacion'] = $baseImageUrl . basename($recommendation['foto_recomendacion']);
                }
                echo json_encode($recommendation);
            } else {
                echo json_encode(["message" => "Recomendación no encontrada"]);
            }
        } else if ($searchText) {
            // Buscar recomendaciones por texto y nombre del psicólogo
            $sql = "SELECT r.id, r.texto, r.foto_recomendacion, r.fecha, r.psicologo_id, p.nombre, p.apellido
                    FROM recomendaciones r
                    JOIN psicologos p ON r.psicologo_id = p.id
                    WHERE r.texto LIKE :searchText
                    OR (p.nombre LIKE :searchText OR p.apellido LIKE :searchText)";
    
            $stmt = $conn->prepare($sql);
            $searchText = "%{$searchText}%";
            $stmt->bindParam(':searchText', $searchText, PDO::PARAM_STR);
            $stmt->execute();
            $recommendations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($recommendations as &$rec) {
                if (isset($rec['foto_recomendacion']) && !empty($rec['foto_recomendacion'])) {
                    $rec['foto_recomendacion'] = $baseImageUrl . basename($rec['foto_recomendacion']);
                }
                $rec['psicologo_nombre'] = isset($rec['nombre']) ? $rec['nombre'] . ' ' . $rec['apellido'] : 'Desconocido';
            }
            echo json_encode($recommendations);
        } else {
            // Obtener todas las recomendaciones
            $sql = "SELECT r.id, r.texto, r.foto_recomendacion, r.fecha, r.psicologo_id, p.nombre, p.apellido
                    FROM recomendaciones r
                    JOIN psicologos p ON r.psicologo_id = p.id";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $recommendations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($recommendations as &$rec) {
                if (isset($rec['foto_recomendacion']) && !empty($rec['foto_recomendacion'])) {
                    $rec['foto_recomendacion'] = $baseImageUrl . basename($rec['foto_recomendacion']);
                }
                $rec['psicologo_nombre'] = isset($rec['nombre']) ? $rec['nombre'] . ' ' . $rec['apellido'] : 'Desconocido';
            }
            echo json_encode($recommendations);
        }
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}
