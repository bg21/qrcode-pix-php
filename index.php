<?php 

require_once('vendor/autoload.php');

use Piggly\Pix\Exceptions\InvalidPixKeyException;
use Piggly\Pix\Exceptions\InvalidPixKeyTypeException;
use Piggly\Pix\Parser;
use Piggly\Pix\StaticPayload;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Captura e sanitiza os dados do formulário
        $keyType = filter_input(INPUT_POST, 'key_type', FILTER_SANITIZE_STRING);
        $keyValue = filter_input(INPUT_POST, 'pix_key', FILTER_SANITIZE_STRING);
        $merchantName = filter_input(INPUT_POST, 'merchant_name', FILTER_SANITIZE_STRING);
        $merchantCity = filter_input(INPUT_POST, 'merchant_city', FILTER_SANITIZE_STRING);
        $amount = filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $tid = filter_input(INPUT_POST, 'tid', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

        // Mapeamento do tipo da chave Pix
        $keyTypeMap = [
            'email'     => Parser::KEY_TYPE_EMAIL,
            'document'  => Parser::KEY_TYPE_DOCUMENT,
            'phone'     => Parser::KEY_TYPE_PHONE,
            'random'    => Parser::KEY_TYPE_RANDOM
        ];

        if (!isset($keyTypeMap[$keyType])) {
            throw new Exception("Tipo de chave Pix inválido.");
        }

        $keyType = $keyTypeMap[$keyType];

        // Validação da chave Pix
        Parser::validate($keyType, $keyValue);

        // Validação dos outros campos
        if (empty($merchantName) || empty($merchantCity) || $amount <= 0) {
            throw new Exception("Todos os campos são obrigatórios e o valor deve ser maior que zero.");
        }

        // Criando o payload Pix
        $payload = (new StaticPayload())
            ->setPixKey($keyType, $keyValue)
            ->setMerchantName($merchantName)
            ->setMerchantCity($merchantCity)
            ->setAmount($amount)
            ->setTid($tid)
            ->setDescription($description);

        // Gerando o código Pix
        $pixCode = $payload->getPixCode();
        $qrCode = $payload->getQRCode();

    } catch (InvalidPixKeyTypeException $e) {
        $error = "Tipo de chave Pix inválido.";
    } catch (InvalidPixKeyException $e) {
        $error = "A chave Pix informada é inválida para o tipo selecionado.";
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
    <style>
        .container-custom {
            max-width: 1024px;
        }
        .qr-code-container {
            display: flex;
            justify-content: center;
            min-height: 100%;
        }
    </style>
</head>
<body class="bg-light">

    <div class="container container-custom mt-5">
        <div class="row">
            <!-- Coluna do Formulário -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Gerador de QR Code Pix</h2>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Tipo da Chave Pix:</label>
                                <select name="key_type" class="form-select" required>
                                    <option value="email">E-mail</option>
                                    <option value="document">CPF/CNPJ</option>
                                    <option value="phone">Telefone</option>
                                    <option value="random">Chave Aleatória</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Chave Pix:</label>
                                <input type="text" name="pix_key" class="form-control" required>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-7">
                                <label class="form-label">Nome do Recebedor:</label>
                                <input type="text" name="merchant_name" class="form-control" required>
                                </div>
                                <div class="col-md-5">
                                <label class="form-label">Cidade do Recebedor:</label>
                                <input type="text" name="merchant_city" class="form-control" required>
                                </div>
                            </div>
                            

                            <div class="mb-3">
                                <label class="form-label">Valor (R$):</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ID da Transação (TID) (opcional):</label>
                                <input type="text" name="tid" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Descrição do Pagamento:</label>
                                <input type="text" name="description" class="form-control">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Gerar QR Code</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Coluna do QR Code -->
            <div class="col-md-6 qr-code-container">
                <?php if (isset($pixCode)): ?>
                    <div class="card shadow-sm p-3">
                        <div class="card-body text-center">
                            <h5>Código Pix:</h5>
                            <p class="alert alert-success"><?= htmlspecialchars($pixCode) ?></p>
                            <h5>QR Code:</h5>
                            <img src="<?= htmlspecialchars($qrCode) ?>" alt="QR Code de Pagamento" class="img-fluid mt-2">
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
