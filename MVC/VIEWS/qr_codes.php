<?php
require_once(__DIR__ . "/../MODEL/network_utils.php");

// Debug - Mostrar informações básicas
echo "<div class='alert alert-info'>";
echo "Sistema Operacional: " . PHP_OS . "<br>";
echo "SERVER_ADDR: " . ($_SERVER['SERVER_ADDR'] ?? 'não definido') . "<br>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'não definido') . "<br>";
echo "Hostname: " . gethostname() . "<br>";

// Mostrar IP detectado
$ip = NetworkUtils::getLocalIP();
echo "<br>IP detectado pelo sistema: " . $ip;
echo "</div>";

// Obter URLs de acesso
$urlKitchen = NetworkUtils::generateAccessURL('kitchen');
$urlWaiter = NetworkUtils::generateAccessURL('waiter');
$urlCashier = NetworkUtils::generateAccessURL('cashier');

// Função para gerar URL do QR Code usando Google Charts API
function getQRCodeUrl($text) {
    return "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode($text) . "&choe=UTF-8";
}

// Gerar URLs dos QR Codes
$qrKitchen = getQRCodeUrl($urlKitchen);
$qrWaiter = getQRCodeUrl($urlWaiter);
$qrCashier = getQRCodeUrl($urlCashier);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Codes de Acesso</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <div class="row justify-content-center mb-4">
        <div class="col-12">
            <h2 class="text-center mb-4">QR Codes de Acesso</h2>
            <p class="text-center">Escaneie o QR Code correspondente à sua função ou acesse diretamente pelo link</p>
        </div>
    </div>

    <div class="row">
        <!-- Cozinha -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0 text-center">Cozinha</h5>
                </div>
                <div class="card-body text-center">
                    <img src="<?php echo $qrKitchen; ?>" alt="QR Code Cozinha" class="img-fluid mb-3" style="max-width: 200px;">
                    <p class="card-text">
                        <small>Link de acesso:<br>
                        <a href="<?php echo $urlKitchen; ?>" target="_blank"><?php echo $urlKitchen; ?></a></small>
                    </p>
                </div>
            </div>
        </div>

        <!-- Garçom -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0 text-center">Garçom</h5>
                </div>
                <div class="card-body text-center">
                    <img src="<?php echo $qrWaiter; ?>" alt="QR Code Garçom" class="img-fluid mb-3" style="max-width: 200px;">
                    <p class="card-text">
                        <small>Link de acesso:<br>
                        <a href="<?php echo $urlWaiter; ?>" target="_blank"><?php echo $urlWaiter; ?></a></small>
                    </p>
                </div>
            </div>
        </div>

        <!-- Caixa -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0 text-center">Caixa</h5>
                </div>
                <div class="card-body text-center">
                    <img src="<?php echo $qrCashier; ?>" alt="QR Code Caixa" class="img-fluid mb-3" style="max-width: 200px;">
                    <p class="card-text">
                        <small>Link de acesso:<br>
                        <a href="<?php echo $urlCashier; ?>" target="_blank"><?php echo $urlCashier; ?></a></small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <h5>Instruções:</h5>
                <ol>
                    <li>Certifique-se de que todos os dispositivos estão conectados à mesma rede Wi-Fi</li>
                    <li>Use a câmera do celular para escanear o QR Code correspondente à sua função</li>
                    <li>Ou copie e cole o link de acesso no navegador</li>
                    <li>Faça login com suas credenciais para acessar o sistema</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Atualizar QR Codes a cada 5 minutos para garantir que o IP está correto
setInterval(function() {
    location.reload();
}, 300000);
</script>

</body>
</html> 