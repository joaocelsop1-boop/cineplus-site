<?php
// ==== BANCO DE DADOS ====
// No Coolify (Docker), as credenciais vêm das variáveis de ambiente do
// painel do Coolify. No cPanel, as variáveis não existem e valem os valores
// abaixo (os mesmos de antes). O host do banco no Docker é o nome do
// serviço do docker-compose.yml ("db").
// ATENÇÃO: o banco do SITE é separado do banco do APP CinePlus (NOFLIX).
// Não use aqui o banco do app.
function env_or(string $name, string $default): string {
    $v = getenv($name);
    if ($v === false || $v === '') $v = $_SERVER[$name] ?? $_ENV[$name] ?? '';
    return ($v === '' || $v === false) ? $default : (string)$v;
}
define('DB_HOST', env_or('DB_HOST', 'localhost'));
define('DB_NAME', env_or('DB_NAME', 'storestreaming_neonplay'));
define('DB_USER', env_or('DB_USER', 'storestreaming_neonplay'));
define('DB_PASS', env_or('DB_PASS', 'n4]hKs.smoht[fJ*'));
// ==== URL DO APP CINEPLUS (NOFLIX) ====
// O site é só a página de vendas: o botão "Assinar agora" leva o cliente
// para criar a conta no app. A URL também pode ser trocada no painel do
// site (Site e WhatsApp → URL do app), sem editar arquivo.
// IMPORTANTE: cineplus.shop é o domínio do SITE. O app fica no MESMO
// domínio, no caminho /app — a URL do app é https://cineplus.shop/app.
// NÃO coloque aqui só cineplus.shop (sem /app), senão o botão
// "Assinar agora" volta para o próprio site.
define('APP_URL', env_or('APP_URL', 'https://cineplus.shop/app'));
// ===========================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
    session_start();
}

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Erro ao conectar no banco. Confira o config.php.');
        }
    }
    return $pdo;
}

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function only_digits(string $v): string { return preg_replace('/\D/', '', $v); }
function money($n): string { return number_format((float)$n, 2, ',', '.'); }
function wa_link(string $number, string $msg): string { return 'https://wa.me/' . only_digits($number) . '?text=' . rawurlencode($msg); }
function settings(): array { return db()->query('SELECT * FROM site_settings WHERE id = 1')->fetch() ?: []; }
function plans(): array { return db()->query('SELECT * FROM plans ORDER BY sort_order, id')->fetchAll(); }

// URL do app CinePlus: vale o que está salvo no painel do site (Site e
// WhatsApp → URL do app); se estiver vazio, usa o APP_URL do config.php.
function app_url(): string {
    $u = trim((string)(settings()['app_url'] ?? ''));
    if ($u === '') $u = defined('APP_URL') ? (string)APP_URL : '';
    return rtrim($u, '/');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(403); exit('Sessão expirada. Recarregue a página.'); }
}
function require_admin(): void {
    if (empty($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
}
