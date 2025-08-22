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

// Configurações do Mercado Pago
$MP_ACCESS_TOKEN = 'APP_USR-3811061902338910-082020-0bf36771f9515a8eb63d82fd07e51593-2523204749';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit();
    }
    
    // Extract data
    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    $cpf = $input['cpf'] ?? '';
    $phone = $input['phone'] ?? '';
    $amount = $input['amount'] ?? 0;
    $description = $input['description'] ?? 'Doação via PIX';
    
    // Validações básicas
    if (empty($name) || empty($email) || empty($cpf) || $amount <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Dados obrigatórios não fornecidos']);
        exit();
    }
    
    if ($amount < 10) {
        http_response_code(400);
        echo json_encode(['error' => 'Valor mínimo é R$ 10,00']);
        exit();
    }
    
    // Clean CPF (remove non-numeric characters)
    $cpf_clean = preg_replace('/\D/', '', $cpf);
    
    // Split name into first and last name
    $name_parts = explode(' ', trim($name));
    $first_name = $name_parts[0];
    $last_name = count($name_parts) > 1 ? implode(' ', array_slice($name_parts, 1)) : $first_name;
    
    // Prepare payment data for Mercado Pago
    $payment_data = [
        'transaction_amount' => (float)$amount,
        'description' => $description,
        'payment_method_id' => 'pix',
        'payer' => [
            'email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'identification' => [
                'type' => 'CPF',
                'number' => $cpf_clean
            ]
        ]
    ];
    
    error_log('Criando pagamento PIX: ' . json_encode($payment_data));
    
    // Make request to Mercado Pago API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/v1/payments');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $MP_ACCESS_TOKEN,
        'Content-Type: application/json',
        'X-Idempotency-Key: ' . time() . '-' . rand(1000, 9999)
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
    
    $mp_response = json_decode($response, true);
    error_log('Resposta do Mercado Pago: ' . $response);
    
    if ($http_code !== 201) {
        error_log('Erro do Mercado Pago: ' . $response);
        http_response_code(400);
        echo json_encode([
            'error' => 'Erro ao criar pagamento',
            'details' => $mp_response['message'] ?? 'Erro desconhecido'
        ]);
        exit();
    }
    
    // Return payment data
    $result = [
        'transaction_id' => (string)$mp_response['id'],
        'status' => $mp_response['status'],
        'qr_code' => $mp_response['point_of_interaction']['transaction_data']['qr_code'] ?? null,
        'qr_code_base64' => $mp_response['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null,
        'amount' => $mp_response['transaction_amount'],
        'currency' => 'BRL'
    ];
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log('Erro interno: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>