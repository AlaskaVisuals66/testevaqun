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

// Configurações do Mercado Pago
$MP_ACCESS_TOKEN = 'APP_USR-3811061902338910-082020-0bf36771f9515a8eb63d82fd07e51593-2523204749';

try {
    // Get hash parameter
    $hash = $_GET['hash'] ?? '';
    
    if (empty($hash)) {
        http_response_code(400);
        echo json_encode(['error' => 'Hash não fornecido']);
        exit();
    }
    
    // Make request to Mercado Pago API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/v1/payments/' . urlencode($hash));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $MP_ACCESS_TOKEN,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($curl_error) {
        error_log('Erro cURL: ' . $curl_error);
        http_response_code(500);
        echo json_encode(['error' => 'Erro de conexão com Mercado Pago']);
        exit();
    }
    
    if ($http_code !== 200) {
        http_response_code(404);
        echo json_encode(['error' => 'Pagamento não encontrado']);
        exit();
    }
    
    $payment = json_decode($response, true);
    
    if (!$payment) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao processar resposta do Mercado Pago']);
        exit();
    }
    
    // Return data in expected format
    $result = [
        'status' => $payment['status'],
        'pix' => [
            'pix_qr_code' => $payment['point_of_interaction']['transaction_data']['qr_code'] ?? null,
            'qr_code_base64' => $payment['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null
        ],
        'transaction_amount' => $payment['transaction_amount'],
        'currency_id' => $payment['currency_id']
    ];
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log('Erro ao verificar status: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>