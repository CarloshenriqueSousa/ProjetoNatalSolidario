<?php
// Lógica para recuperação de email
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Email</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="login-container">
        <h2>Recuperar Email</h2>
        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="cpf">Digite seu CPF:</label>
                <input type="text" id="cpf" name="cpf" required>
            </div>
            <button type="submit" class="btn-login">Recuperar Email</button>
            <div class="login-links">
                <a href="login.php">← Voltar para o Login</a>
            </div>
        </form>
    </div>
</body>
</html>