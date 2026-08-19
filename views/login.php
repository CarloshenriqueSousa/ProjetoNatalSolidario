<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Natal Solidário</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: var(--bg-primary);
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.12) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(244, 63, 94, 0.12) 0px, transparent 50%);
            padding: 20px;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 40px;
            box-shadow: var(--shadow-lg);
            backdrop-filter: blur(12px);
            text-align: center;
        }
        .login-logo {
            width: 54px;
            height: 54px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-accent));
            border-radius: var(--radius-md);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: bold;
            font-size: 28px;
            margin-bottom: 20px;
            box-shadow: 0 4px 14px rgba(244, 63, 94, 0.35);
        }
        .login-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .login-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 30px;
        }
    </style>
</head>
<body class="login-body">
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-logo">N</div>
            <h2 class="login-title">Natal Solidário</h2>
            <p class="login-subtitle">Acesse o sistema de gerenciamento</p>
            
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger" style="margin-bottom: 20px; text-align: left;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= url('login') ?>" method="POST" style="text-align: left;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                
                <div class="form-group">
                    <label class="form-label" for="login_input">Usuário</label>
                    <input type="text" id="login_input" name="login" class="form-control" placeholder="Digite seu login" required autocomplete="username">
                </div>
                
                <div class="form-group" style="margin-bottom: 30px;">
                    <label class="form-label" for="senha_input">Senha</label>
                    <input type="password" id="senha_input" name="senha" class="form-control" placeholder="Digite sua senha" required autocomplete="current-password">
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; height: 46px; font-size: 15px;">
                    Entrar no Sistema
                </button>
            </form>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>
