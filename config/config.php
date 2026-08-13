<?php
/**
 * Natal Solidário — Configurações Globais do Sistema
 */

// ─── Banco de Dados ────────────────────────────────────────────────────────
define('DB_HOST',    '127.0.0.1');
define('DB_PORT',    '3306');
define('DB_NAME',    'natal_solidario');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// ─── Aplicação ─────────────────────────────────────────────────────────────
define('APP_NAME', 'Natal Solidário — Sistema de Gerenciamento');

/**
 * BASE_URL: URL base do projeto.
 * Se rodar em subpasta XAMPP: '/ProjetoNatalSolidario/'
 * Se rodar na raiz do servidor: '/'
 * Detectado automaticamente via SCRIPT_NAME para máxima compatibilidade.
 */
if (!defined('BASE_URL')) {
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'), '/\\');
    // Se estiver na raiz ('/'), mantém como '/', senão adiciona barra final
    define('BASE_URL', ($scriptDir === '' ? '/' : $scriptDir . '/'));
}

// ─── Sessão e Segurança ────────────────────────────────────────────────────
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
