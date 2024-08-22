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
        $startDate = isset($_GET['start_date']) ? filter_var($_GET['start_date'], FILTER_SANITIZE_STRING) : null;
        $endDate = isset($_GET['end_date']) ? filter_var($_GET['end_date'], FILTER_SANITIZE_STRING) : null;

        $baseImageUrl = 'http://localhost/login/image/noticias/'; // Base URL para las imágenes

        $params = [];
        $sql = "SELECT n.id, n.titulo, n.contenido, n.foto_noticia, n.psicologo_id, p.nombre, p.apellido,n.fecha
                FROM noticias n
                JOIN psicologos p ON n.psicologo_id = p.id";

        // Construir condiciones SQL según los parámetros de búsqueda
        $conditions = [];

        if ($id) {
            $conditions[] = "n.id = :id";
            $params[':id'] = $id;
        }

        if ($searchText) {
            $conditions[] = "(n.titulo LIKE :searchText OR n.contenido LIKE :searchText OR p.nombre LIKE :searchText OR p.apellido LIKE :searchText)";
            $params[':searchText'] = "%{$searchText}%";
        }

        if ($startDate && $endDate) {
            $conditions[] = "n.fecha BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $startDate;
            $params[':endDate'] = $endDate;
        }

        // Añadir las condiciones a la consulta SQL
        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($noticias as &$not) {
            if (isset($not['foto_noticia']) && !empty($not['foto_noticia'])) {
                $not['foto_noticia'] = $baseImageUrl . basename($not['foto_noticia']);
            }
            $not['psicologo_nombre'] = isset($not['nombre']) ? $not['nombre'] . ' ' . $not['apellido'] : 'Desconocido';
        }
        echo json_encode($noticias);
    } else {
        http_response_code(403);
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
}

