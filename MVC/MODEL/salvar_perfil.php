<?php
session_start();

// Directory for logo uploads
$uploadDir = dirname(__DIR__) . '/UPLOADS/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Prepare config array
$config = [
    'nome_estabelecimento' => $_POST['nome_estabelecimento'] ?? '',
    'cnpj' => $_POST['cnpj'] ?? '',
    'endereco' => $_POST['endereco'] ?? '',
    'telefone' => $_POST['telefone'] ?? '',
    'site' => $_POST['site'] ?? '',
    'messages' => [
        'header' => $_POST['mensagem_header'] ?? ''
    ]
];

// Handle logo upload
if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['logo']['tmp_name'];
    $fileName = basename($_FILES['logo']['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    if (!in_array($fileExt, $allowed)) {
        $_SESSION['msg'] = 'Invalid image type.';
        header('Location: ../VIEWS/perfil.php');
        exit;
    }
    if ($_FILES['logo']['size'] > $maxSize) {
        $_SESSION['msg'] = 'Image too large (max 2MB).';
        header('Location: ../VIEWS/perfil.php');
        exit;
    }
    $newName = 'logo_' . time() . '.' . $fileExt;
    $dest = $uploadDir . $newName;
    if (move_uploaded_file($fileTmp, $dest)) {
        $config['logo'] = $newName;
    } else {
        $_SESSION['msg'] = 'Failed to upload logo.';
        header('Location: ../VIEWS/perfil.php');
        exit;
    }
} else if (!empty($_POST['logo_atual'])) {
    $config['logo'] = $_POST['logo_atual'];
}

// Save config to file
$configFile = dirname(__DIR__) . '/CONFIG/estabelecimento.php';
file_put_contents($configFile, "<?php\nreturn " . var_export($config, true) . ";\n");

$_SESSION['msg'] = 'Profile updated successfully!';
header('Location: ../VIEWS/perfil.php');
exit; 