<?php
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$secret_key = "TU_SECRETO";

function generate_jwt($email, $nombre, $apellido) {
    global $secret_key;
    $issuedAt = time();
    $expirationTime = $issuedAt + 36000;  // JWT válido por 1 hora
    $payload = array(
        'email' => $email,
        'nombre' => $nombre,
        'apellido' => $apellido,
        'iat' => $issuedAt,
        'exp' => $expirationTime
    );

    return JWT::encode($payload, $secret_key, 'HS256');
}

function validate_jwt($jwt) {
    global $secret_key;
    try {
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        return (array) $decoded;
    } catch (Exception $e) {
        // Opcional: registra el error para depuración
        error_log('JWT validation error: ' . $e->getMessage());
        return false;
    }
}

// Función para obtener datos de usuario desde JWT
function get_user_from_jwt($jwt) {
    $decoded = validate_jwt($jwt);
    if ($decoded !== false && isset($decoded['email'])) {
        return [
            'email' => $decoded['email'],
            'nombre' => $decoded['nombre'],
            'apellido' => $decoded['apellido']
        ];
    }
    return null;
}


