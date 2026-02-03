<?php
/**
 * ======================================================
 * CONFIGURAÇÃO DO BANCO DE DADOS - MYSQL (PDO)
 * ======================================================
 */

// Detectar ambiente (local ou produção)
$is_localhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);

if ($is_localhost) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'imobiliaria_db');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    // Valores do sistema original mantidos para compatibilidade em produção
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'nome_do_banco');
    define('DB_USER', 'nome_do_usuario');
    define('DB_PASS', 'senha');
}

define('DB_CHARSET', 'utf8mb4');

// =====================
// CONEXÃO PDO
// =====================
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => true, // Ativado para maior compatibilidade com drivers antigos
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Log interno e mensagem amigável para evitar erro 500 exposto
    error_log("Erro de conexão PDO: " . $e->getMessage());
    header('HTTP/1.1 503 Service Unavailable');
    die("<h3>Erro de Conexão</h3><p>O sistema não conseguiu conectar ao banco de dados. Verifique se o arquivo SQL foi importado corretamente.</p>");
}

// =====================
// FUNÇÕES DE SEGURANÇA E SAAS
// =====================

/**
 * Sanitiza dados de entrada
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Escapa dados para saída HTML
 */
function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Gera ou valida Token CSRF
 */
function csrf_token() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Registra log de auditoria (Nível SaaS)
 * Resiliente: Se a tabela não existir, não derruba o sistema
 */
function registrar_log($acao, $detalhes = null) {
    global $pdo;
    try {
        $usuario_id = $_SESSION['admin_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $stmt = $pdo->prepare("INSERT INTO logs_auditoria (usuario_id, acao, detalhes, ip) VALUES (?, ?, ?, ?)");
        $stmt->execute([$usuario_id, $acao, $detalhes, $ip]);
    } catch (Exception $e) {
        error_log("Erro Log Auditoria: " . $e->getMessage());
    }
}

/**
 * Gera slug amigável para URLs
 */
function createSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text ?: 'item-' . uniqid();
}

/**
 * Formata moeda para Real
 */
function formatMoeda($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}
?>
