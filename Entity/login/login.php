<?php
include '../../config/bd.php';  // Adjust path as per your file structure
include '../../config/cors.php';  // Adjust path as per your file structure
include '../../jwt/jwt_utils.php';  // Adjust path as per your file structure

$data = json_decode(file_get_contents("php://input"));

if(isset($data->email) && isset($data->contraseña)) {
    $email = $data->email;
    $contraseña = $data->contraseña;

    $sql = "SELECT * FROM psicologos WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($contraseña, $user['password'])) { 
        // Generate JWT token
        $token = generate_jwt($user['email'], $user['nombre'], $user['apellido']);

        // Return successful login response with token and user details
        echo json_encode([
            "message" => "Login exitoso",
            "token" => $token,
            "nombre" => $user['nombre'],
            "apellido" => $user['apellido']
        ]);
    } else {
        // Return error message for incorrect email or password
        echo json_encode(["message" => "Email o contraseña incorrectos"]);
    }
} else {
    // Return error message for incomplete data
    echo json_encode(["message" => "Datos incompletos"]);
}
