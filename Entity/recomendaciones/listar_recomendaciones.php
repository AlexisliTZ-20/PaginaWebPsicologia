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
        $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

        if ($id) {
            $sql = "SELECT * FROM recomendaciones WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $recommendation = $stmt->fetch(PDO::FETCH_ASSOC);


            if ($recommendation && isset($recommendation['foto_recomendacion'])) {

                $recommendation['foto_recomendacion'] = 'http://localhost/login/image/recomendaciones/' . basename($recommendation['foto_recomendacion']);
            }

            if ($recommendation) {
                echo json_encode($recommendation);
            } else {
                echo json_encode(["message" => "Recomendación no encontrada"]);
            }
        } else {
            // Fetch all recommendations
            $sql = "SELECT * FROM recomendaciones";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $recommendations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Add the full URL to the photo for each recommendation
            foreach ($recommendations as &$rec) {
                if (isset($rec['foto_recomendacion'])) {
                    // Replace with your server URL or IP address
                    $rec['foto_recomendacion'] = 'http://localhost/login/image/recomendaciones/' . basename($rec['foto_recomendacion']);
                }
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

