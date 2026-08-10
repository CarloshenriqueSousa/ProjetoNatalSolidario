<?php
session_start();
require 'conexao.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $mensagem = "Preencha email e senha.";
    } else {
        $email = $conexao->real_escape_string($email);
        $sql = "SELECT id, nome, senha FROM usuarios WHERE email = '$email' AND ativo = 1";
        $result = $conexao->query($sql);

        if ($result) {
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($senha, $user['senha'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['nome'];
                    header("Location: home.php");
                    exit;
                } else {
                    $mensagem = "Senha incorreta.";
                }
            } else {
                $mensagem = "Usuário não encontrado.";
            }
        } else {
            $mensagem = "Erro na consulta: " . $conexao->error;
        }
    }
}

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if ($mensagem): ?>
            <div class='erro-login'><?php echo $mensagem; ?></div>
        <?php endif; ?>
        
        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <a href="esqueci_email.php" class="login-link">Esqueci meu email</a>
            </div>
            
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
                <a href="esqueci_senha.php" class="login-link">Esqueci minha senha</a>
            </div>
            
            <button type="submit" class="btn-login">Entrar</button>

            <!-- 🔽 LINK PARA CRIAR CONTA ADICIONADO AQUI -->
            <div class="criar-conta">
                <p>Ainda não tem conta?</p>
                <a href="criarconta.php" class="btn-criar">Criar conta</a>
            </div>
        </form>
    </div>

    <style>
        .criar-conta {
            margin-top: 15px;
            text-align: center;
        }

        .criar-conta p {
            margin: 5px 0 10px;
            color: #555;
        }

        .btn-criar {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.2s;
        }

        .btn-criar:hover {
            background-color: #45a049;
        }
    </style>

</body>
</html>
