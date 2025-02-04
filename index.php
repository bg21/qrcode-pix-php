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
        $tid = filter_input(INPUT_POST, 'tid', FILTER_SANITIZE_STRING) ?? '';
        // Garante que a descrição tenha um valor padrão
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $description = !empty($description) ? $description : "Pagamento via Pix";

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
            ->setTid($tid) // Garantindo que TID não seja null
            ->setDescription($description); // Garantindo que a descrição não seja null

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.7-beta.29/jquery.inputmask.min.js"></script>
    <style>
        .container-custom {
            max-width: 1024px;
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

    <div class="container container-custom mt-3">
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
                                <select name="key_type" id="key_type" class="form-select" required>
                                    <option value="email">E-mail</option>
                                    <option value="document">CPF/CNPJ</option>
                                    <option value="phone">Telefone</option>
                                    <option value="random">Chave Aleatória</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Chave Pix:</label>
                                <input type="text" name="pix_key" id="pix_key" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nome do Recebedor:</label>
                                <input placeholder="Nome do Recebedor" type="text" name="merchant_name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Cidade do Recebedor:</label>
                                <input placeholder="Cidade do Recebedor" type="text" name="merchant_city" class="form-control" required>
                            </div>
                            <div class="mb-3">
        <label class="form-label">Descrição do Pagamento (opcional):</label>
        <input type="text" name="description" class="form-control" placeholder="Ex: Compra de produto, pagamento mensalidade, etc.">
    </div>
                            <div class="mb-3">
                                <label class="form-label">Valor (R$):</label>
                                <input placeholder="Valor" type="number" step="0.01" name="amount" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Gerar QR Code</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Coluna do QR Code -->
            <div class="col-md-6 qr-code-container ">
                <?php if (isset($pixCode)): ?>
                    <div class="card shadow-sm">
                        <div class="card-body text-center  p-4">
                            <h5>Código Pix:</h5>
                            <div id="pixCodeBox" class="pix-code-box mt-4">
                                <p class="alert alert-success">
                                <?= htmlspecialchars($pixCode) ?>
                                </p></div>
                            <button class="btn btn-outline-success copy-btn" onclick="copyToClipboard()">Copiar Código Pix</button>
                            <p id="copyMessage" class="text-success" style="display: none;">Copiado!</p>

                            <h5 class="mt-5">QR Code:</h5>
                            <img src="<?= htmlspecialchars($qrCode) ?>" alt="QR Code de Pagamento" class="qrcode-img">
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            function aplicarMascara() {
                let tipoChave = $("#key_type").val();
                let inputChave = $("#pix_key");

                inputChave.val("").removeAttr("placeholder");

                if (tipoChave === "document") {
                    inputChave.attr("placeholder", "CPF ou CNPJ");
                    inputChave.inputmask({
                        mask: ["999.999.999-99", "99.999.999/9999-99"],
                        keepStatic: true
                    });
                } else if (tipoChave === "phone") {
                    inputChave.attr("placeholder", "(00) 00000-0000");
                    inputChave.inputmask({
                        mask: "(99) 99999-9999"
                    });
                }else if (tipoChave === "email") {
                    inputChave.attr("placeholder", "bruce@gmail.com");
                    inputChave.inputmask({
                        mask: ""
                    });
                } else {
                    inputChave.inputmask("remove");
                }
            }

            $("#key_type").change(aplicarMascara);
            aplicarMascara();
        });

        function copyToClipboard() {
            let copyText = document.getElementById("pixCodeBox").innerText;
            navigator.clipboard.writeText(copyText).then(() => {
                document.getElementById("copyMessage").style.display = "block";
                setTimeout(() => {
                    document.getElementById("copyMessage").style.display = "none";
                }, 2000);
            });
        }
    </script>

</body>
</html>
