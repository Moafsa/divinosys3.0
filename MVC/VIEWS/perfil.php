<?php
// Start session and include config
session_start();
require_once '../CONFIG/estabelecimento.php';

// Load current config
$config = file_exists('../CONFIG/estabelecimento.php') ? include '../CONFIG/estabelecimento.php' : [];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link href="../COMMON/CSS/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-logo-preview {
            width: 120px;
            height: 120px;
            object-fit: contain;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 1rem;
            background: #f7f7f7;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Profile</h2>
    <form id="profileForm" method="POST" enctype="multipart/form-data" action="../MODEL/salvar_perfil.php">
        <div class="mb-3">
            <label for="nome_estabelecimento" class="form-label">Business Name</label>
            <input type="text" class="form-control" id="nome_estabelecimento" name="nome_estabelecimento" value="<?php echo htmlspecialchars($config['nome_estabelecimento'] ?? ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="cnpj" class="form-label">CNPJ</label>
            <input type="text" class="form-control" id="cnpj" name="cnpj" value="<?php echo htmlspecialchars($config['cnpj'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label for="endereco" class="form-label">Address</label>
            <input type="text" class="form-control" id="endereco" name="endereco" value="<?php echo htmlspecialchars($config['endereco'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label for="telefone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="telefone" name="telefone" value="<?php echo htmlspecialchars($config['telefone'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label for="site" class="form-label">Website</label>
            <input type="text" class="form-control" id="site" name="site" value="<?php echo htmlspecialchars($config['site'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label for="mensagem_header" class="form-label">Header Message (optional)</label>
            <input type="text" class="form-control" id="mensagem_header" name="mensagem_header" value="<?php echo htmlspecialchars($config['messages']['header'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label for="logo" class="form-label">Logo</label><br>
            <img id="logoPreview" class="profile-logo-preview" src="<?php echo !empty($config['logo']) ? '../UPLOADS/' . htmlspecialchars($config['logo']) : '../COMMON/IMG/logo-default.png'; ?>" alt="Logo Preview">
            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
<script>
// Preview logo before upload
const logoInput = document.getElementById('logo');
const logoPreview = document.getElementById('logoPreview');
logoInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(evt) {
            logoPreview.src = evt.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html> 