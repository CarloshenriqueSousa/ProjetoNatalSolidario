<?php
require_once __DIR__ . '/../config/database.php';

class Auth {

    public static function check(): bool {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_ip']) || !isset($_SESSION['user_agent'])) {
            return false;
        }

        // Validação de IP e User-Agent para mitigação de sequestro de sessão
        if ($_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR'] || $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            self::logout();
            return false;
        }

        return true;
    }

    public static function requireLogin(): void {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }

    public static function login(string $email, string $senha): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id, nome, email, senha_hash, perfil, ativo FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && (int)$user['ativo'] === 1 && password_verify($senha, $user['senha_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nome'] = $user['nome'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_perfil'] = $user['perfil'];
            $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';

            // Buscar turma vinculada se for perfil 'coleta' ou 'turma'
            $_SESSION['turma_id'] = self::fetchTurmaId($user['id']);

            return true;
        }

        return false;
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function user(): ?array {
        if (!self::check()) return null;
        return [
            'id' => $_SESSION['user_id'],
            'nome' => $_SESSION['user_nome'],
            'email' => $_SESSION['user_email'],
            'perfil' => $_SESSION['user_perfil'],
            'turma_id' => $_SESSION['turma_id'] ?? null
        ];
    }

    public static function getPerfil(): ?string {
        return $_SESSION['user_perfil'] ?? null;
    }

    public static function getTurmaId(): ?int {
        return $_SESSION['turma_id'] ?? null;
    }

    public static function fetchTurmaId(int $usuarioId): ?int {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT turma_id FROM usuarios_turmas WHERE usuario_id = :uid LIMIT 1");
        $stmt->execute([':uid' => $usuarioId]);
        $res = $stmt->fetch();
        return $res ? (int)$res['turma_id'] : null;
    }

    // RBAC: Verificação de Papéis
    public static function requireRole(array $allowedRoles): void {
        self::requireLogin();
        $perfil = self::getPerfil();

        if (!in_array($perfil, $allowedRoles, true)) {
            http_response_code(403);
            require_once __DIR__ . '/../views/layouts/header.php';
            echo "<div class='container' style='margin-top: 50px;'><div class='alert alert-danger'><h2>Acesso Negado (403)</h2><p>Seu perfil ('{$perfil}') não possui permissão para acessar este recurso.</p></div></div>";
            require_once __DIR__ . '/../views/layouts/footer.php';
            exit;
        }
    }

    // RBAC: Verificação para Sub-Admin
    public static function checkSubadminPermission(string $modulo, string $acao): bool {
        if (self::getPerfil() === 'admin') return true;
        if (self::getPerfil() !== 'subadmin') return false;

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM permissoes_subadmin WHERE usuario_id = :uid AND modulo = :modulo AND acao = :acao");
        $stmt->execute([
            ':uid' => $_SESSION['user_id'],
            ':modulo' => $modulo,
            ':acao' => $acao
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // Regra Zero - Bloqueio de Acesso Restrito a 'coleta' e 'turma'
    public static function checkBlockColetaTurma(): void {
        $perfil = self::getPerfil();
        if (in_array($perfil, ['coleta', 'turma'], true)) {
            http_response_code(403);
            require_once __DIR__ . '/../views/layouts/header.php';
            echo "<div class='container' style='margin-top: 50px;'><div class='alert alert-danger'><h2>Acesso Restrito (403)</h2><p>Usuários de Coleta e Turma não possuem permissão para visualizar Financeiro, Ranking ou Pontuações Gerais.</p></div></div>";
            require_once __DIR__ . '/../views/layouts/footer.php';
            exit;
        }
    }
}
