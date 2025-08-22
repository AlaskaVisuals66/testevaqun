# Sistema de Doações - Juvilda

Sistema de doações com integração à LightPay via PIX, convertido para PHP puro.

## Estrutura do Projeto

- `/api/` - APIs PHP para integração com LightPay
- `/pagamento/` - Páginas de checkout e pagamento PIX
- `/files/` - Arquivos estáticos (CSS, JS, imagens)
- `index.html` - Página principal da vaquinha

## APIs Disponíveis

### POST /api/create-pix-payment.php
Cria um novo pagamento PIX na LightPay.

**Parâmetros:**
- `name` (string): Nome completo do doador
- `email` (string): Email do doador
- `cpf` (string): CPF do doador
- `phone` (string): Telefone do doador (opcional)
- `amount` (number): Valor da doação
- `description` (string): Descrição da doação (opcional)

### GET /api/verificar_status.php
Verifica o status de um pagamento PIX.

**Parâmetros:**
- `hash` (string): ID da transação retornado pela API de criação

### POST /api/webhook.php
Recebe notificações de status da LightPay.

**Webhook payload da LightPay:**
```json
{
   "received_at":"2025-08-18 19:47:56",
   "payload":{
      "transactionId":"b8c72d3f9a4e56d7c1f0e8a9b3c2d4f5",
      "external_id":"DEP_10_1755547676_9041",
      "status":"paid",
      "amount":500,
      "postbackUrl":"https://seusite.com/webhook"
   }
}
```
## Configuração

1. Configure as credenciais da LightPay nos arquivos PHP:
   - Client ID: `client_68a8c1ecc5c22`
   - Secret Key: `80651cbb5bcbac38e512517a466fa222`
   - Endpoint: `https://lightpaybr.com/v2/`
2. Certifique-se de que o servidor web tem suporte a PHP e cURL
3. Configure as permissões CORS se necessário

## Funcionalidades

- ✅ Criação de pagamentos PIX via LightPay
- ✅ Geração de QR Code para pagamento
- ✅ Verificação de status do pagamento
- ✅ Webhook para receber notificações de pagamento
- ✅ Interface responsiva
- ✅ Integração com Facebook Pixel
- ✅ Notificações de doações em tempo real
