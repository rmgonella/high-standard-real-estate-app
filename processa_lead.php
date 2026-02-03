<?php
require_once __DIR__ . '/includes/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die("Erro de validação de segurança. Por favor, tente novamente.");
    }

    $nome = sanitize($_POST['nome']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? sanitize($_POST['email']) : '';
    $telefone = sanitize($_POST['telefone']);
    $mensagem = sanitize($_POST['mensagem']);
    $lancamento_id = !empty($_POST['lancamento_id']) ? (int)$_POST['lancamento_id'] : null;

    if (empty($nome) || empty($telefone)) {
        echo "<script>alert('Por favor, preencha o nome e o telefone.'); window.history.back();</script>";
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO leads (nome, email, telefone, mensagem, lancamento_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $email, $telefone, $mensagem, $lancamento_id]);
        
        // Em um sistema real, aqui dispararíamos um e-mail para o administrador
        
        echo "<script>alert('Sua solicitação foi enviada com sucesso! Um de nossos consultores entrará em contato em breve.'); window.location.href='index.php';</script>";
    } catch (PDOException $e) {
        error_log("Erro ao salvar lead: " . $e->getMessage());
        echo "<script>alert('Desculpe, ocorreu um erro ao processar sua solicitação. Tente novamente mais tarde.'); window.location.href='index.php';</script>";
    }
} else {
    header('Location: index.php');
    exit;
}
?>
