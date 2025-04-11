# QR Code PIX PHP

Uma biblioteca PHP simples e eficiente para geração de QR Codes para o sistema de pagamento instantâneo PIX do Banco Central do Brasil.

## 📋 Sumário

- [Sobre](#sobre)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Como Usar](#como-usar)
- [Exemplos](#exemplos)
- [Personalização](#personalização)
- [Licença](#licença)

## 📝 Sobre

Esta biblioteca oferece uma maneira fácil de gerar QR Codes para pagamentos via PIX, o sistema de pagamentos instantâneos do Banco Central do Brasil. Com ela, você pode facilmente integrar pagamentos PIX em seu sistema ou site.

## 🔧 Requisitos

- PHP 7.4 ou superior
- Extensão GD para PHP
- Composer (gerenciador de dependências)

## 🚀 Instalação

Você pode instalar esta biblioteca via Composer:

```bash
composer require bg21/qrcode-pix-php
```

Ou clone o repositório para seu projeto:

```bash
git clone https://github.com/bg21/qrcode-pix-php.git
cd qrcode-pix-php
composer install
```

## 💻 Como Usar

A biblioteca é simples de usar. Aqui está um exemplo básico de como gerar um QR Code PIX:

```php
<?php
require 'vendor/autoload.php';

use QrCodePix\QrCodePix;

// Inicializa o gerador de QR Code PIX
$pix = new QrCodePix([
    'keyType' => 'CPF',
    'key' => '12345678900', // CPF do recebedor
    'name' => 'Nome do Recebedor',
    'city' => 'São Paulo',
    'amount' => 100.00, // R$ 100,00
    'description' => 'Pagamento de Serviço' // Opcional
]);

// Gera o QR Code
$qrCode = $pix->getQrCode();

// Exibe o QR Code na página
echo '<img src="' . $qrCode . '" alt="QR Code PIX">';

// Obter a string de pagamento PIX
$pixCode = $pix->getPixCode();
echo '<p>Código PIX: ' . $pixCode . '</p>';
```

## 📚 Exemplos

### Exemplo 1: QR Code PIX com valor fixo

```php
$pix = new QrCodePix([
    'keyType' => 'CNPJ',
    'key' => '12345678000123', // CNPJ do recebedor
    'name' => 'Minha Empresa LTDA',
    'city' => 'Belo Horizonte',
    'amount' => 99.90, // R$ 99,90
    'txid' => 'TXID123456', // Identificador único da transação (opcional)
    'description' => 'Compra de Produto'
]);

// Gerar e exibir o QR Code
echo $pix->getQrCodeImage(); // Exibe diretamente a imagem
```

### Exemplo 2: QR Code PIX sem valor definido (cliente informa o valor)

```php
$pix = new QrCodePix([
    'keyType' => 'EMAIL',
    'key' => 'email@empresa.com', // E-mail do recebedor
    'name' => 'Empresa de Serviços',
    'city' => 'Rio de Janeiro',
    'description' => 'Doação para Campanha'
]);

// Gerar e salvar o QR Code como imagem
$pix->saveQrCode('qrcode-doacao.png');
```

## 🎨 Personalização

Você pode personalizar o QR Code gerado:

```php
$pix = new QrCodePix([
    'keyType' => 'CHAVE_ALEATORIA',
    'key' => '62b12ed1-8d70-4267-aa13-c6c8dd46a1a1', // Chave aleatória PIX
    'name' => 'João da Silva',
    'city' => 'Curitiba',
    'amount' => 150.00,
    'uniquePayment' => true, // Define se o pagamento pode ser feito apenas uma vez
]);

// Personaliza a aparência do QR Code
$pix->setQrCodeOptions([
    'size' => 400, // Tamanho em pixels
    'margin' => 10, // Margem
    'foregroundColor' => [0, 0, 0], // Cor do QR Code (RGB)
    'backgroundColor' => [255, 255, 255], // Cor de fundo (RGB)
    'logoPath' => 'logo.png', // Caminho para o logo no centro do QR Code
    'logoSize' => 100, // Tamanho do logo
]);

$qrCode = $pix->getQrCode();
```

## 📄 Licença

Este projeto está licenciado sob a MIT License - veja o arquivo LICENSE para mais detalhes.

---

Desenvolvido por [bg21](https://github.com/bg21)
