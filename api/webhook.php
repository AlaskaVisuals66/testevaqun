<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input from LightPay webhook
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit();
    }
    
    // Log the webhook data
    error_log('Webhook LightPay recebido: ' . json_encode($input));
    
    // Extract webhook data
    $received_at = $input['received_at'] ?? '';
    $payload = $input['payload'] ?? [];
    
    if (empty($payload)) {
        http_response_code(400);
        echo json_encode(['error' => 'Payload vazio']);
        exit();
    }
    
    $transaction_id = $payload['transactionId'] ?? '';
    $external_id = $payload['external_id'] ?? '';
    $status = $payload['status'] ?? '';
    $amount = $payload['amount'] ?? 0;
    $postback_url = $payload['postbackUrl'] ?? '';
    
    // Process the webhook based on status
    switch ($status) {
        case 'paid':
            // Payment was successful
            error_log("Pagamento aprovado - Transaction ID: {$transaction_id}, Amount: {$amount}");
            
            // Here you would:
            // 1. Update the transaction status in your database
            // 2. Send confirmation email to the donor
            // 3. Update any analytics or reporting systems
            // 4. Trigger any post-payment actions
            
            break;
            
        case 'cancelled':
        case 'expired':
            // Payment was cancelled or expired
            error_log("Pagamento cancelado/expirado - Transaction ID: {$transaction_id}");
            
            // Here you would:
            // 1. Update the transaction status in your database
            // 2. Handle the cancellation logic
            
            break;
            
        default:
            error_log("Status desconhecido: {$status} - Transaction ID: {$transaction_id}");
            break;
    }
    
    // Return success response to LightPay
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Webhook processado']);
    
} catch (Exception $e) {
    error_log('Erro no webhook: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>