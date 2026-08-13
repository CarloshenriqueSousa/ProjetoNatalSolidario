<?php
/**
 * Helpers Globais — Natal Solidário
 * Funções utilitárias usadas em todo o sistema.
 * Carregado pelo index.php antes de qualquer outra coisa.
 */

// ─────────────────────────────────────────────────────────────────────────────
// GERAÇÃO DE URLs
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Gera a URL correta para uma rota do sistema (?route=X).
 * Suporta parâmetros adicionais na query string: url('products/edit&id=5')
 */
function url(string $route = ''): string
{
    $base = rtrim(BASE_URL, '/') . '/index.php';
    if ($route === '') {
        return $base;
    }
    // Permite passar parâmetros extras: 'products/edit&id=5'
    // A rota em si pode conter & para adicionar mais parâmetros
    return $base . '?route=' . $route;
}

/**
 * Alias para url() sem o index.php explícito — preserva compatibilidade
 * com eventuais chamadas diretas a assets, etc.
 */
function asset(string $path): string
{
    return rtrim(BASE_URL, '/') . '/assets/' . ltrim($path, '/');
}

// ─────────────────────────────────────────────────────────────────────────────
// SEGURANÇA — ESCAPE / XSS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Escapa HTML para uso seguro em views.
 */
function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// ─────────────────────────────────────────────────────────────────────────────
// AUTENTICAÇÃO
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Verifica se existe sessão de usuário ativa.
 */
function is_logged_in(): bool
{
    return Auth::check();
}

/**
 * Verifica se o usuário logado possui um ou mais perfis.
 *
 * @param string|string[] $roles  perfil único ou array de perfis
 */
function has_role($roles): bool
{
    if (!Auth::check()) {
        return false;
    }
    $perfil = Auth::getPerfil();
    if (is_array($roles)) {
        return in_array($perfil, $roles, true);
    }
    return $perfil === $roles;
}

// ─────────────────────────────────────────────────────────────────────────────
// REDIRECIONAMENTO
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Redireciona para uma rota do sistema e encerra a execução.
 * Exemplo: redirect('dashboard') → Location: /index.php?route=dashboard
 */
function redirect(string $route): void
{
    header('Location: ' . url($route));
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// FORMATAÇÃO DE DATAS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Formata uma data do formato Y-m-d para d/m/Y.
 */
function format_date(?string $date): string
{
    if (empty($date) || $date === '0000-00-00') {
        return '—';
    }
    $dt = DateTime::createFromFormat('Y-m-d', substr($date, 0, 10));
    return $dt ? $dt->format('d/m/Y') : $date;
}

/**
 * Formata um datetime completo para d/m/Y H:i.
 */
function format_datetime(?string $datetime): string
{
    if (empty($datetime)) {
        return '—';
    }
    try {
        $dt = new DateTime($datetime);
        return $dt->format('d/m/Y H:i');
    } catch (Exception $e) {
        return $datetime;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// CSRF — SEGURANÇA DE FORMULÁRIOS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Gera (ou reutiliza) um token CSRF na sessão e retorna o valor.
 */
function generate_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica se um token CSRF enviado pelo formulário é válido.
 */
function verify_csrf_token(string $token): bool
{
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Gera um campo hidden de CSRF pronto para inserir em formulários.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(generate_csrf_token()) . '">';
}

// ─────────────────────────────────────────────────────────────────────────────
// FORMATAÇÃO NUMÉRICA
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Formata um valor monetário em Reais.
 */
function format_money(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

/**
 * Formata um número inteiro com separador de milhar.
 */
function format_number(int $value): string
{
    return number_format($value, 0, ',', '.');
}
