<div style="max-width: 420px; margin: 60px auto 0 auto;">
    <div class="card">
        <div style="text-align: center; margin-bottom: 24px;">
            <div class="brand-icon" style="margin: 0 auto 12px auto; width: 50px; height: 50px; font-size: 1.6rem;">🎄</div>
            <h2>Natal Solidário</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Acesse o sistema de gerenciamento</p>
        </div>

        <form action="/login" method="POST">
            <div class="form-group">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="seu@email.com" required autofocus>
            </div>

            <div class="form-group">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" id="senha" name="senha" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px;">Entrar no Sistema</button>
        </form>
    </div>
</div>
