<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = sanitize($_POST['email']);
        $senha = $_POST['senha'];

        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha'])) {
            session_regenerate_id(true);
            
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_nome'] = $user['nome'];
            $_SESSION['last_login'] = time();
            
            // Atualizar último login (opcional, ignora erro se coluna faltar)
            try {
                $stmt_upd = $pdo->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?");
                $stmt_upd->execute([$user['id']]);
            } catch (Exception $e) {
                error_log("Aviso: Coluna ultimo_login ausente.");
            }
            
            registrar_log("Login Realizado", "Acesso ao painel administrativo via web.");
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'E-mail ou senha incorretos.';
            registrar_log("Tentativa de Login Falhou", "E-mail: $email");
        }
    } catch (Exception $e) {
        $error = "Erro no servidor: " . $e->getMessage();
        error_log("Erro no Login: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaaS Login - Amaral Imóveis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background: #0f172a; 
            font-family: 'Inter', sans-serif;
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin: 0;
        }
        .login-card { 
            width: 100%; 
            max-width: 420px; 
            padding: 3.5rem 2.5rem; 
            border-radius: 24px; 
            background: #fff; 
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }
        .logo-area { text-align: center; margin-bottom: 2.5rem; }
        .logo-area h3 { font-weight: 800; letter-spacing: -1px; color: #0f172a; }
        .logo-area span { color: #d4af37; }
        .form-label { font-weight: 600; font-size: 0.85rem; color: #475569; }
        .form-control { 
            padding: 0.9rem 1.1rem; 
            border-radius: 12px; 
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            transition: all 0.2s;
        }
        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.15);
            border-color: #d4af37;
            background: #fff;
        }
        .btn-primary { 
            background-color: #0f172a; 
            border: none; 
            padding: 1rem;
            font-weight: 700;
            border-radius: 12px;
            transition: all 0.3s;
        }
        .btn-primary:hover { background-color: #d4af37; transform: translateY(-2px); color: #0f172a; }
        .alert { border-radius: 12px; font-size: 0.9rem; border: none; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-area">
            <h3>Amaral<span>SaaS</span></h3>
            <p class="text-muted small">Enterprise Resource Planning</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center bg-danger bg-opacity-10 text-danger">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="form-label">E-mail Corporativo</label>
                <input type="email" name="email" class="form-control" placeholder="nome@empresa.com" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Chave de Acesso</label>
                <input type="password" name="senha" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-4 shadow-sm">Autenticar no Sistema</button>
            <div class="text-center">
                <a href="../index.php" class="text-decoration-none small text-muted hover-dark"><i class="bi bi-arrow-left me-1"></i> Voltar ao portal público</a>
            </div>
        </form>
    </div>
</body>
</html>
