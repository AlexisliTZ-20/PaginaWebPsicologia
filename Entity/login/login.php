<?php
include '../../config/bd.php';
include '../../config/cors.php';
include '../../jwt/jwt_utils.php';

$data = json_decode(file_get_contents("php://input"));

if(isset($data->email) && isset($data->contraseña)) {
    $email = $data->email;
    $contraseña = $data->contraseña;

    $sql = "SELECT * FROM psicologos WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($contraseña, $user['password'])) { 
        $token = generate_jwt($user['email'], $user['nombre'], $user['apellido']);
        echo json_encode([
            "message" => "Login exitoso",
            "token" => $token,
            "nombre" => $user['nombre'],
            "apellido" => $user['apellido']
        ]);
    } else {
        echo json_encode(["message" => "Email o contraseña incorrectos"]);
    }
} else {
    echo json_encode(["message" => "Datos incompletos"]);
}

