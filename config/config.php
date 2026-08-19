<?php
// Configurações Globais do Sistema

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'natal_solidario');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'Natal Solidário - Sistema de Gerenciamento');
define('BASE_URL', '/'); // Ajustar se rodar em subpasta

// Configurações de Sessão e Segurança
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
