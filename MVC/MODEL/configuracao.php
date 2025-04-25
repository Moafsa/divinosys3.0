<?php
session_start();
include_once 'conexao.php';
require_once 'config.php';

// Initialize configuration
$config = Config::getInstance();

if ($_SESSION['nivel'] != 2) {
    header("Location: " . $config->url());
    exit;
}

// Buscar a cor atual das configurações
$stmt = mysqli_prepare($conn, "SELECT valor FROM configuracoes WHERE chave = 'cor'");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$cor = 'VERMELHO'; // Cor padrão

if ($row = mysqli_fetch_assoc($result)) {
    $cor = $row['valor'];
}
mysqli_stmt_close($stmt);

// Process form submission
if (isset($_POST['cad_mesas'])) {
    try {
        $qnt_mesas = filter_input(INPUT_POST, 'qnt_mesas', FILTER_SANITIZE_NUMBER_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

        if (!$qnt_mesas || !$status) {
            throw new Exception("Invalid input data");
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO mesas (num_mesa, status) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, 'is', $qnt_mesas, $status);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error inserting data: " . mysqli_error($conn));
        }

        $_SESSION['msg'] = "<div class='alert alert-success'>Mesa cadastrada com sucesso!</div>";
        
    } catch (Exception $e) {
        error_log("Error in configuracao.php: " . $e->getMessage());
        $_SESSION['msg'] = "<div class='alert alert-danger'>Erro ao cadastrar mesa: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Configurações do Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: <?php 
                switch($cor) {
                    case 'VERDE':
                        echo '#28a745';
                        break;
                    case 'VERMELHO':
                        echo '#ea1d2c';
                        break;
                    case 'AMARELO':
                        echo '#ffc107';
                        break;
                    case 'CIANO':
                        echo '#17a2b8';
                        break;
                    case 'AZUL':
                        echo '#007bff';
                        break;
                    case 'PRETO':
                        echo '#000000';
                        break;
                    default:
                        echo '#ea1d2c';
                }
            ?>;
            --secondary-color: <?php echo $cor === 'PRETO' ? '#333333' : '#f7f7f7'; ?>;
            --text-color: <?php echo $cor === 'PRETO' ? '#ffffff' : '#333333'; ?>;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: var(--secondary-color);
            color: var(--text-color);
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: <?php echo $cor === 'PRETO' ? '#222222' : 'white'; ?>;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: var(--text-color);
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 3px;
            margin: 10px 0;
            border: none;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .form-check-label {
            color: var(--text-color);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Configurações do Sistema</h1>
        
        <?php
        if (isset($_SESSION['msg'])) {
            echo $_SESSION['msg'];
            unset($_SESSION['msg']);
        }
        ?>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Defina a cor das abas:</h5>
                <form action="<?php echo $config->url('MVC/MODEL/salvar_configuracao.php'); ?>" method="POST">
                    <div class="mb-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cor" id="verde" value="VERDE" <?php echo $cor == 'VERDE' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="verde" style="color: #28a745">VERDE</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cor" id="vermelho" value="VERMELHO" <?php echo $cor == 'VERMELHO' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="vermelho" style="color: #ea1d2c">VERMELHO</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cor" id="amarelo" value="AMARELO" <?php echo $cor == 'AMARELO' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="amarelo" style="color: #ffc107">AMARELO</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cor" id="ciano" value="CIANO" <?php echo $cor == 'CIANO' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="ciano" style="color: #17a2b8">CIANO</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cor" id="azul" value="AZUL" <?php echo $cor == 'AZUL' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="azul" style="color: #007bff">AZUL</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cor" id="preto" value="PRETO" <?php echo $cor == 'PRETO' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="preto" style="color: #000000">PRETO</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="<?php echo $config->url('?view=Dashboard1'); ?>" class="btn btn-secondary">Voltar</a>
                </form>
            </div>
        </div>
    </div>

    <script src="<?php echo $config->url('assets/js/jquery.min.js'); ?>"></script>
    <script src="<?php echo $config->url('assets/js/bootstrap.min.js'); ?>"></script>
</body>
</html>