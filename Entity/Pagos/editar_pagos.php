<?php
include '../../config/bd.php';
include '../../config/cors.php';
include '../../jwt/jwt_utils.php';

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

// Get Authorization header
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authHeader) {
    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if ($jwt && validate_jwt($jwt)) {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = isset($input['id']) ? filter_var($input['id'], FILTER_SANITIZE_NUMBER_INT) : null;
            $monto = isset($input['monto']) ? filter_var($input['monto'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
            $estado = isset($input['estado']) ? filter_var($input['estado'], FILTER_SANITIZE_STRING) : null;
            $foto_pago = isset($input['foto_pago']) ? filter_var($input['foto_pago'], FILTER_SANITIZE_STRING) : '';

            if ($id && $monto !== null && $estado) {
                // Handle file upload
                if (isset($_FILES['foto_pago']) && $_FILES['foto_pago']['error'] === UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES['foto_pago']['tmp_name'];
                    $name = basename($_FILES['foto_pago']['name']);
                    $upload_dir = '../../image/pagos/';
                    $upload_file = $upload_dir . $name;

                    // Ensure the upload directory exists
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    if (move_uploaded_file($tmp_name, $upload_file)) {
                        $foto_pago = $name; // Update the photo URL
                    } else {
                        http_response_code(500); // Internal Server Error
                        echo json_encode(["message" => "Error al subir la foto"]);
                        exit();
                    }
                }

                $sql = "UPDATE pagos SET monto = :monto, estado = :estado, foto_pago = :foto_pago WHERE id = :id";
                $stmt = $conn->prepare($sql);

                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':monto', $monto, PDO::PARAM_STR);
                $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
                $stmt->bindParam(':foto_pago', $foto_pago, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    if ($stmt->rowCount() > 0) {
                        http_response_code(200); // OK
                        echo json_encode(["message" => "Pago actualizado con éxito"]);
                    } else {
                        http_response_code(404); // Not Found
                        echo json_encode(["message" => "Pago no encontrado"]);
                    }
                } else {
                    http_response_code(500); // Internal Server Error
                    echo json_encode(["message" => "Error al actualizar el pago"]);
                }
            } else {
                http_response_code(400); // Bad Request
                echo json_encode(["message" => "Datos de entrada inválidos"]);
            }
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode(["message" => "Método no permitido"]);
        }
    } else {
        http_response_code(403); // Forbidden
        echo json_encode(["message" => "Acceso denegado"]);
    }
} else {
    http_response_code(401); // Unauthorized
    echo json_encode(["message" => "Token no proporcionado"]);
}
?>
