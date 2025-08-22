<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Get hash parameter (transaction ID)
    $hash = $_GET['hash'] ?? '';
    
    if (empty($hash)) {
        http_response_code(400);
        echo json_encode(['error' => 'Hash não fornecido']);
        exit();
    }
    
    // For LightPay, we'll simulate the payment data since we don't have a status check endpoint
    // In a real implementation, you would store the transaction data and PIX code in a database
    // and retrieve it here, or LightPay would provide a status check endpoint
    
    // For now, we'll return a mock response that allows the PIX flow to work
    // The actual payment status would be updated via webhook
    
    $result = [
        'status' => 'pending',
        'pix' => [
            'pix_qr_code' => 'mock_pix_code_' . $hash, // This would be the actual PIX code from database
            'qr_code_base64' => null
        ],
        'transaction_amount' => 50.00, // This would come from database
        'currency_id' => 'BRL'
    ];
    
    // In a real implementation, you would:
    // 1. Query your database for the transaction using $hash
    // 2. Return the stored PIX code and current status
    // 3. The status would be updated by the webhook when payment is confirmed
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log('Erro ao verificar status: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>