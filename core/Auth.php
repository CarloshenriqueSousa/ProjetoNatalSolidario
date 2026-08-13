<?php
/**
 * Auth — Natal Solidário
 * Centraliza autenticação, sessão e controle de acesso (RBAC).
 * Usa a tabela `usuarios` do schema oficial (sql/schema.sql):
 *   email, senha_hash, perfil, ativo
 * Chave de sessão padrão: user_perfil (NÃO user_tipo)
 */
class Auth
{
    // ─── Verificação de Sessão ────────────────────────────────────────────

    /**
     * Retorna true se existe sessão válida com IP e User-Agent consistentes.
     */
    public static function check(): bool
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_ip']) || empty($_SESSION['user_agent'])) {
            return false;
        }

        // Mitigação de sequestro de sessão
        if ($_SESSION['user_ip']    !== $_SERVER['REMOTE_ADDR']
            || $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            self::logout();
            return false;
        }

        return true;
    }

    /**
     * Redireciona para login se não houver sessão ativa.
     * Usa url() do helpers.php — compatível com ?route=login.
     */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . url('login'));
            exit;
        }
    }

    // ─── Login / Logout ───────────────────────────────────────────────────

    /**
     * Autentica o usuário por e-mail e senha.
     * Retorna true em caso de sucesso.
     */
    public static function login(string $email, string $senha): bool
    {
        $db   = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT id, nome, email, senha_hash, perfil, ativo
               FROM usuarios
              WHERE email = :email
              LIMIT 1"
        );
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && (int)$user['ativo'] === 1 && password_verify($senha, $user['senha_hash'])) {
            session_regenerate_id(true);

            $_SESSION['user_id']     = $user['id'];
            $_SESSION['user_nome']   = $user['nome'];
            $_SESSION['user_email']  = $user['email'];
            $_SESSION['user_perfil'] = $user['perfil'];   // padrão do sistema
            $_SESSION['user_ip']     = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent']  = $_SERVER['HTTP_USER_AGENT'] ?? '';

            // Busca turma vinculada ao usuário (se houver)
            $_SESSION['turma_id'] = self::fetchTurmaId($user['id']);

            return true;
        }

        return false;
    }

    /**
     * Encerra a sessão e limpa o cookie de sessão.
     */
    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
    }

    // ─── Dados do Usuário ─────────────────────────────────────────────────

    /**
     * Retorna array com os dados do usuário logado, ou null se não autenticado.
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        return [
            'id'       => $_SESSION['user_id'],
            'nome'     => $_SESSION['user_nome'],
            'email'    => $_SESSION['user_email'],
            'perfil'   => $_SESSION['user_perfil'],
            'turma_id' => $_SESSION['turma_id'] ?? null,
        ];
    }

    /** Retorna o perfil do usuário logado ou null. */
    public static function getPerfil(): ?string
    {
        return $_SESSION['user_perfil'] ?? null;
    }

    /** Retorna o turma_id do usuário logado ou null. */
    public static function getTurmaId(): ?int
    {
        return isset($_SESSION['turma_id']) ? (int)$_SESSION['turma_id'] : null;
    }

    /**
     * Busca a turma vinculada a um usuário via tabela usuarios_turmas.
     */
    public static function fetchTurmaId(int $usuarioId): ?int
    {
        $db   = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT turma_id FROM usuarios_turmas WHERE usuario_id = :uid LIMIT 1"
        );
        $stmt->execute([':uid' => $usuarioId]);
        $res = $stmt->fetch();
        return $res ? (int)$res['turma_id'] : null;
    }

    // ─── RBAC ─────────────────────────────────────────────────────────────

    /**
     * Exige que o usuário logado possua um dos perfis listados.
     * Em caso de falha retorna 403 e encerra.
     */
    public static function requireRole(array $allowedRoles): void
    {
        self::requireLogin();
        $perfil = self::getPerfil();

        if (!in_array($perfil, $allowedRoles, true)) {
            http_response_code(403);
            $pageTitle = 'Acesso Negado';
            if (file_exists(__DIR__ . '/../includes/header.php')) {
                include __DIR__ . '/../includes/header.php';
            }
            echo "<div class='container' style='margin-top:50px;'>
                    <div class='alert alert-danger'>
                        <strong>Acesso Negado (403)</strong><br>
                        Seu perfil (<em>" . e($perfil) . "</em>) não possui permissão para este recurso.
                    </div>
                  </div>";
            if (file_exists(__DIR__ . '/../includes/footer.php')) {
                include __DIR__ . '/../includes/footer.php';
            }
            exit;
        }
    }

    /**
     * Bloqueia acesso para perfis 'coleta' e 'turma'.
     */
    public static function checkBlockColetaTurma(): void
    {
        $perfil = self::getPerfil();
        if (in_array($perfil, ['coleta', 'turma'], true)) {
            http_response_code(403);
            $pageTitle = 'Acesso Restrito';
            if (file_exists(__DIR__ . '/../includes/header.php')) {
                include __DIR__ . '/../includes/header.php';
            }
            echo "<div class='container' style='margin-top:50px;'>
                    <div class='alert alert-danger'>
                        <strong>Acesso Restrito (403)</strong><br>
                        Usuários de Coleta e Turma não possuem permissão para esta área.
                    </div>
                  </div>";
            if (file_exists(__DIR__ . '/../includes/footer.php')) {
                include __DIR__ . '/../includes/footer.php';
            }
            exit;
        }
    }

    /**
     * Verificação granular para Sub-Admin via tabela permissoes_subadmin.
     */
    public static function checkSubadminPermission(string $modulo, string $acao): bool
    {
        if (self::getPerfil() === 'admin') {
            return true;
        }
        if (self::getPerfil() !== 'subadmin') {
            return false;
        }

        $db   = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM permissoes_subadmin
              WHERE usuario_id = :uid AND modulo = :modulo AND acao = :acao"
        );
        $stmt->execute([
            ':uid'    => $_SESSION['user_id'],
            ':modulo' => $modulo,
            ':acao'   => $acao,
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
