<?php
header('Content-Type: application/json');

// Simulación de API Mock de Pagos (POST /api/v1/pedidos)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["status" => "error", "message" => "Método no permitido. Use POST."]);
    exit();
}

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if (!isset($input['monto']) || !isset($input['id_usuario'])) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Faltan parámetros: monto, id_usuario."]);
    exit();
}

$monto = floatval($input['monto']);
$id_usuario = intval($input['id_usuario']);

// Simulamos latencia de red de la pasarela
sleep(1);

// Simulamos lógica de pasarela (ej: falla si monto < 0)
if ($monto <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Monto inválido."]);
    exit();
}

// Registro simulado en log
file_put_contents(__DIR__ . '/../../payment_log.txt', "[" . date('Y-m-d H:i:s') . "] API Mock: Pago aprobado para usuario ID $id_usuario por monto $$monto\n", FILE_APPEND);

// Respuesta de éxito
echo json_encode([
    "status" => "success",
    "message" => "Pago procesado exitosamente.",
    "transaction_id" => uniqid("txn_"),
    "monto_procesado" => $monto
]);
