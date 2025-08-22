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

// Configurações da LightPay
$CLIENT_ID = 'client_68a8c1ecc5c22';
$SECRET_KEY = '80651cbb5bcbac38e512517a466fa222';
$LIGHTPAY_ENDPOINT = 'https://lightpaybr.com/v2/';

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
    
    // Clean CPF and phone (remove non-numeric characters)
    $cpf_clean = preg_replace('/\D/', '', $cpf);
    $phone_clean = preg_replace('/\D/', '', $phone);
    
    // Ensure phone has at least 10 digits
    if (strlen($phone_clean) < 10) {
        $phone_clean = '11999999999'; // Default phone if not provided or invalid
    }
    
    // Convert amount to centavos (LightPay expects integer in centavos)
    $amount_centavos = (int)($amount * 100);
    
    // Prepare payment data for LightPay
    $payment_data = [
        'nome' => $name,
        'cpf' => $cpf_clean,
        'celular' => $phone_clean,
        'email' => $email,
        'valor' => $amount_centavos,
        'rua' => 'Rua da Doação',
        'numero' => '123',
        'cep' => '01310100',
        'bairro' => 'Centro',
        'cidade' => 'São Paulo',
        'estado' => 'SP'
    ];
    
    error_log('Criando pagamento PIX LightPay: ' . json_encode($payment_data));
    
    // Create Basic Auth header
    $auth_string = base64_encode($CLIENT_ID . ':' . $SECRET_KEY);
    
    // Make request to LightPay API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $LIGHTPAY_ENDPOINT);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . $auth_string,
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
        echo json_encode(['error' => 'Erro de conexão com LightPay']);
        exit();
    }
    
    $lightpay_response = json_decode($response, true);
    error_log('Resposta da LightPay: ' . $response);
    
    if ($http_code !== 200) {
        error_log('Erro da LightPay: ' . $response);
        http_response_code(400);
        echo json_encode([
            'error' => 'Erro ao criar pagamento',
            'details' => $lightpay_response['message'] ?? 'Erro desconhecido'
        ]);
        exit();
    }
    
    // Check if response has required fields
    if (!isset($lightpay_response['transactionId']) || !isset($lightpay_response['pix'])) {
        error_log('Resposta inválida da LightPay: ' . $response);
        http_response_code(500);
        echo json_encode(['error' => 'Resposta inválida da API']);
        exit();
    }
    
    // Return payment data in the expected format
    $result = [
        'transaction_id' => $lightpay_response['transactionId'],
        'status' => 'pending',
        'qr_code' => $lightpay_response['pix'],
        'qr_code_base64' => null, // LightPay doesn't provide base64, only the PIX code
        'amount' => $amount,
        'currency' => 'BRL'
    ];
    
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log('Erro interno: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>