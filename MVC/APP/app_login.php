<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDV - Login</title>
<link href="../COMMON/css/bootstrap.min.css" rel="stylesheet"/>
    <style>
        body {
            background: #000;
            color: white;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: rgba(255,255,255,0.1);
            padding: 2rem;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
        }
        .form-control {
            background: rgba(255,255,255,0.9);
        }
        .error-message {
            display: none;
            color: #ff4444;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1 class="text-center mb-4">LOGIN</h1>
        <form method="POST" action="session.php" id="loginForm">
            <div class="form-group mb-3">
                <label for="login">Login</label>
                <input name="login" id="login" type="text" class="form-control" required>
            </div>

            <div class="form-group mb-3">
                <label for="senha">Senha</label>
                <input name="senha" id="senha" type="password" class="form-control" required>
            </div>

            <div class="error-message" id="errorMessage">
                Por favor, preencha todos os campos
            </div>

            <button type="submit" class="btn btn-success w-100">Entrar</button>
        </form>
    </div>

    <script src="../COMMON/js/jquery-3.3.1.slim.min.js"></script>
    <script src="../COMMON/js/popper.min.js"></script>
<script src="../COMMON/js/bootstrap.min.js"></script>
    <script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
            const login = document.getElementById('login').value.trim();
            const senha = document.getElementById('senha').value.trim();
            const errorMessage = document.getElementById('errorMessage');

            if (!login || !senha) {
                e.preventDefault();
                errorMessage.style.display = 'block';
            } else {
                errorMessage.style.display = 'none';
            }
        });
    </script>
</body>
</html>