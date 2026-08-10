<?php
session_start();
require 'conexao.php';

$mensagem = '';
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    $data_nascimento = $_POST['data_nascimento'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $termos = isset($_POST['termos']) ? true : false;

    // Validações
    if (empty($nome)) $erros[] = "Nome é obrigatório.";
    if (empty($email)) $erros[] = "Email é obrigatório.";
    if (empty($senha)) $erros[] = "Senha é obrigatória.";
    if ($senha !== $confirmar_senha) $erros[] = "As senhas não coincidem.";
    if (strlen($senha) < 6) $erros[] = "A senha deve ter pelo menos 6 caracteres.";
    if (!$termos) $erros[] = "Você deve aceitar os termos de uso.";
    
    // Verificar se email já existe
    if (empty($erros)) {
        $email_check = $conexao->real_escape_string($email);
        $sql = "SELECT id FROM usuarios WHERE email = '$email_check'";
        $result = $conexao->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $erros[] = "Este email já está cadastrado.";
        }
    }

    // Processar upload da foto
    $foto_perfil = null;
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === 0) {
        $arquivo = $_FILES['foto_perfil'];
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($extensao, $extensoes_permitidas)) {
            if ($arquivo['size'] <= 5 * 1024 * 1024) { // 5MB
                $nome_arquivo = uniqid() . '.' . $extensao;
                $caminho_destino = 'uploads/' . $nome_arquivo;
                
                // Criar diretório se não existir (com fallback)
                if (!is_dir('uploads')) {
                    if (!mkdir('uploads', 0755, true)) {
                        $erros[] = "Não foi possível criar a pasta de uploads.";
                    }
                }
                
                if (empty($erros) && move_uploaded_file($arquivo['tmp_name'], $caminho_destino)) {
                    $foto_perfil = $caminho_destino;
                } else {
                    $erros[] = "Erro ao fazer upload da foto. Verifique as permissões da pasta.";
                }
            } else {
                $erros[] = "A foto deve ter no máximo 5MB.";
            }
        } else {
            $erros[] = "Formato de arquivo não permitido. Use JPG, PNG ou GIF.";
        }
    }

    // Inserir no banco de dados - VERSÃO SIMPLIFICADA
    if (empty($erros)) {
        // Preparar valores básicos (que sabemos que existem)
        $nome_esc = $conexao->real_escape_string($nome);
        $email_esc = $conexao->real_escape_string($email);
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        
        // Query básica - apenas colunas essenciais
        $sql = "INSERT INTO usuarios (nome, email, senha, ativo, data_cadastro) 
                VALUES ('$nome_esc', '$email_esc', '$senha_hash', 1, NOW())";
        
        if ($conexao->query($sql)) {
            $mensagem = "Usuário criado com sucesso!";
            // Limpar formulário
            $nome = $email = $data_nascimento = $bio = '';
        } else {
            $erros[] = "Erro ao criar usuário: " . $conexao->error;
        }
    }
}

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Usuário</title>
    <link rel="stylesheet" href="estilos.css">
    <script>
        function previewFoto(input) {
            const preview = document.getElementById('fotoPreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function verificarForcaSenha() {
            const senha = document.getElementById('senha').value;
            const forca = document.getElementById('forcaSenha');
            
            if (senha.length === 0) {
                forca.textContent = '';
                return;
            }

            let score = 0;
            if (senha.length >= 6) score++;
            if (senha.match(/[a-z]/) && senha.match(/[A-Z]/)) score++;
            if (senha.match(/\d/)) score++;
            if (senha.match(/[^a-zA-Z\d]/)) score++;

            if (score === 1) {
                forca.textContent = 'Senha fraca';
                forca.className = 'senha-strength senha-fraca';
            } else if (score === 2 || score === 3) {
                forca.textContent = 'Senha média';
                forca.className = 'senha-strength senha-media';
            } else if (score === 4) {
                forca.textContent = 'Senha forte';
                forca.className = 'senha-strength senha-forte';
            }
        }

        function validarSenhas() {
            const senha = document.getElementById('senha').value;
            const confirmar = document.getElementById('confirmar_senha').value;
            const erro = document.getElementById('erroSenha');
            
            if (confirmar && senha !== confirmar) {
                erro.textContent = 'As senhas não coincidem';
            } else {
                erro.textContent = '';
            }
        }
    </script>
</head>
<body>
    <div class="criar-usuario-container">
        <h2>Criar Nova Conta</h2>
        
        <?php if ($mensagem): ?>
            <div class='sucesso' style="color: #27ae60; background-color: #d5f4e6; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #a3e4d7;">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($erros)): ?>
            <div class='erro-login'>
                <?php foreach ($erros as $erro): ?>
                    <div><?php echo $erro; ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="login-form" enctype="multipart/form-data">
            <!-- Foto de Perfil -->
            <div class="foto-perfil-container">
                <img src="https://via.placeholder.com/120x120/ecf0f1/666666?text=Foto" 
                     alt="Preview da Foto" 
                     class="foto-preview" 
                     id="fotoPreview">
                <br>
                <label for="foto_perfil" class="btn-foto">
                    Escolher Foto
                </label>
                <input type="file" 
                       id="foto_perfil" 
                       name="foto_perfil" 
                       accept="image/*" 
                       style="display: none;" 
                       onchange="previewFoto(this)">
            </div>

            <!-- Nome e Email -->
            <div class="form-group-row">
                <div class="form-group">
                    <label for="nome">Nome Completo:</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
            </div>

            <!-- Data de Nascimento (OPCIONAL) -->
            <div class="form-group">
                <label for="data_nascimento">Data de Nascimento (opcional):</label>
                <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($data_nascimento ?? ''); ?>">
                <small style="color: #666; font-size: 0.8rem;">Esta informação será salva quando adicionarmos a coluna no banco</small>
            </div>

            <!-- Senhas -->
            <div class="form-group-row">
                <div class="form-group">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" required onkeyup="verificarForcaSenha()">
                    <div id="forcaSenha" class="senha-strength"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Senha:</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required onkeyup="validarSenhas()">
                    <div id="erroSenha" style="color: #e74c3c; font-size: 0.8rem; margin-top: 5px;"></div>
                </div>
            </div>

            <!-- Bio (OPCIONAL) -->
            <div class="form-group">
                <label for="bio">Bio (opcional):</label>
                <textarea id="bio" name="bio" class="bio-textarea" placeholder="Conte um pouco sobre você..."><?php echo htmlspecialchars($bio ?? ''); ?></textarea>
                <small style="color: #666; font-size: 0.8rem;">Esta informação será salva quando adicionarmos a coluna no banco</small>
            </div>

            <!-- Termos -->
            <div class="termos-container">
                <input type="checkbox" id="termos" name="termos" required>
                <label for="termos">
                    Concordo com os <a href="termos.php" class="termos-link" target="_blank">Termos de Uso</a> 
                    e <a href="privacidade.php" class="termos-link" target="_blank">Política de Privacidade</a>
                </label>
            </div>

            <button type="submit" class="btn-login">Criar Conta</button>
            
            <div class="links-container">
                <a href="login.php" class="login-link">← Já tenho uma conta</a>
            </div>
        </form>
    </div>
</body>
</html>