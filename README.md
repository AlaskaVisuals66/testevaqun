# Sistema de Doações - Juvilda

Sistema de doações com integração ao Mercado Pago via PIX, convertido para PHP puro.

## Estrutura do Projeto

- `/api/` - APIs PHP para integração com Mercado Pago
- `/pagamento/` - Páginas de checkout e pagamento PIX
- `/files/` - Arquivos estáticos (CSS, JS, imagens)
- `index.html` - Página principal da vaquinha

## APIs Disponíveis

### POST /api/create-pix-payment.php
Cria um novo pagamento PIX no Mercado Pago.

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

## Configuração

1. Configure as credenciais do Mercado Pago nos arquivos PHP
2. Certifique-se de que o servidor web tem suporte a PHP e cURL
3. Configure as permissões CORS se necessário

## Funcionalidades

- ✅ Criação de pagamentos PIX via Mercado Pago
- ✅ Geração de QR Code para pagamento
- ✅ Verificação de status do pagamento
- ✅ Interface responsiva
- ✅ Integração com Facebook Pixel
- ✅ Notificações de doações em tempo real
