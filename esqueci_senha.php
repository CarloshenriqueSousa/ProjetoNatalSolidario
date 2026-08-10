<?php
// Lógica para recuperação de senha
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="login-container">
        <h2>Recuperar Senha</h2>
        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="email">Digite seu email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit" class="btn-login">Enviar Link de Recuperação</button>
            <div class="login-links">
                <a href="login.php">← Voltar para o Login</a>
            </div>
        </form>
    </div>
</body>
</html>