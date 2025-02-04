<?php
require_once('vendor/autoload.php');

use Piggly\Pix\Exceptions\InvalidPixKeyException;
use Piggly\Pix\Exceptions\InvalidPixKeyTypeException;
use Piggly\Pix\Exceptions\InvalidEmvFieldException;
use Piggly\Pix\Exceptions\EmvIdIsRequiredException;
use Piggly\Pix\StaticPayload;

$pixCode = null;
$qrCode = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Captura os dados do formulário
        $keyType = htmlspecialchars(filter_input(INPUT_POST, 'keyType'));
        $keyValue = htmlspecialchars(filter_input(INPUT_POST, 'keyValue'));
        $merchantName = strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', htmlspecialchars(filter_input(INPUT_POST, 'merchantName'))));
        $merchantCity = strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', htmlspecialchars(filter_input(INPUT_POST, 'merchantCity'))));
        $amount = number_format((float) htmlspecialchars(filter_input(INPUT_POST, 'amount')), 2, '.', '');
        $tid = !empty($tid) ? $tid : uniqid();
        $description = htmlspecialchars(filter_input(INPUT_POST, 'description')) ?? "Pagamento via Pix";

        // Geração do Pix Estático
        $payload = (new StaticPayload())
            ->setAmount($amount)
            ->setTid($tid)
            ->setDescription($description)
            ->setPixKey($keyType, $keyValue)
            ->setMerchantName($merchantName)
            ->setMerchantCity($merchantCity);

        // Gerando o código Pix
        $pixCode = $payload->getPixCode();
        $qrCode = $payload->getQRCode();
    } catch (InvalidPixKeyException $e) {
        $error = "A chave Pix informada é inválida.";
    } catch (InvalidPixKeyTypeException $e) {
        $error = "O tipo de chave Pix informado é inválido.";
    } catch (InvalidEmvFieldException $e) {
        $error = "Um dos campos do Pix contém dados inválidos.";
    } catch (EmvIdIsRequiredException $e) {
        $error = "Um campo obrigatório do Pix não foi preenchido.";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de QR Code Pix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <style>
        .container-custom {
            padding: 20px;
            max-width: 1180px;
        }
        
        .qr-code-container {
            display: flex;
            justify-content: center;
        }
        .qrcode-img {
            width: 200px; /* Define um tamanho menor */
            height: auto;
            display: block;
            margin: 0 auto;
        }
        .pix-code-box {
            background: #f8f9fa;
            border-radius: 5px;
            text-align: center;
            word-break: break-all;
            font-size: 16px;
        }
        .copy-btn {
            display: block;
            width: 100%;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container container-custom">
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Gerador de QR Code Pix</h2>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Tipo da Chave Pix:</label>
                                <select name="keyType" id="keyType" class="form-select" required>
                                    <option value="email">E-mail</option>
                                    <option value="document">CPF/CNPJ</option>
                                    <option value="phone">Telefone</option>
                                    <option value="random">Chave Aleatória</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Chave Pix:</label>
                                <input placeholder="Email/CPF/CNPJ/Telefone/Chave Aleatória" type="text" name="keyValue" id="keyValue" class="form-control" required>
                            </div>
                            <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome do Recebedor:</label>
                                <input placeholder="Nome do recebedor" type="text" name="merchantName" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cidade do Recebedor:</label>
                                <input placeholder="Cidade do recebedor" type="text" name="merchantCity" class="form-control" required>
                            </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Descrição do Pagamento:</label>
                                <input placeholder="Descrição do Pagamento" type="text" name="description" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Valor (R$):</label>
                                <input placeholder="34" type="text" name="amount" id="amount" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Gerar QR Code</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-center qr-code-container">
                <?php if ($pixCode): ?>
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5>Código Pix:</h5>
                            <div id="pixCodeBox" class="alert alert-success p-2 mt-3">
                                <?= htmlspecialchars($pixCode) ?>
                            </div>
                            <button class="btn btn-outline-success copy-btn" onclick="copyToClipboard()">Copiar Código Pix</button>
                            <p id="copyMessage" class="text-success" style="display: none;">Copiado!</p>
                            <h5 class="mt-5">QR Code:</h5>
                            <img src="<?= htmlspecialchars($qrCode) ?>" alt="QR Code de Pagamento" class="img-fluid qrcode-img">
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#amount').mask('000000.00', {reverse: true});
            
            $('#keyType').change(function() {
                var keyType = $(this).val();
                var keyInput = $('#keyValue');
                keyInput.val('').unmask();
                if (keyType === 'phone') {
                    keyInput.mask('(00) 00000-0000');
                } else if (keyType === 'document') {
                    keyInput.mask('000.000.000-00');
                }
            });
        });
        function copyToClipboard() {
            var text = document.getElementById('pixCodeBox').innerText;
            navigator.clipboard.writeText(text).then(function() {
                document.getElementById('copyMessage').style.display = 'block';
                setTimeout(function() {
                    document.getElementById('copyMessage').style.display = 'none';
                }, 2000);
            });
        }
    </script>
</body>
</html>
