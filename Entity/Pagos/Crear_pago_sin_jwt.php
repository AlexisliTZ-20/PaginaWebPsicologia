<?php
include '../../config/bd.php';
include '../../config/cors.php';

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Initialize variables
    $cita_id = isset($_POST['citaId']) ? filter_var($_POST['citaId'], FILTER_SANITIZE_NUMBER_INT) : null;
    $monto = isset($_POST['monto']) ? filter_var($_POST['monto'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
    $tipo_pago_id = isset($_POST['tipoPagoId']) ? filter_var($_POST['tipoPagoId'], FILTER_SANITIZE_NUMBER_INT) : null;

    // Handle file upload
    $foto_pago = '';
    if (isset($_FILES['fotoPago']) && $_FILES['fotoPago']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['fotoPago']['tmp_name'];
        $name = basename($_FILES['fotoPago']['name']);
        $upload_dir = '../../image/pagos/';
        $upload_file = $upload_dir . $name;

        // Ensure the upload directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        if (move_uploaded_file($tmp_name, $upload_file)) {
            $foto_pago = $name;
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(["message" => "Error al subir la foto"]);
            exit();
        }
    }

    // Validate data
    if ($cita_id && $monto !== null && $tipo_pago_id) {
        $sql = "INSERT INTO pagos (cita_id, monto, fecha_pago, foto_pago, tipoPagoId)
                VALUES (:cita_id, :monto, CURDATE(), :foto_pago, :psicologo_cuenta_id)";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':cita_id', $cita_id, PDO::PARAM_INT);
        $stmt->bindParam(':monto', $monto, PDO::PARAM_STR);
        $stmt->bindParam(':foto_pago', $foto_pago, PDO::PARAM_STR); // The file name
        $stmt->bindParam(':psicologo_cuenta_id', $tipo_pago_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            http_response_code(201); // Created
            echo json_encode(["message" => "Pago registrado exitosamente"]);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(["message" => "Error al registrar el pago"]);
        }
    } else {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Datos de entrada inválidos"]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["message" => "Método no permitido"]);
}
?>
