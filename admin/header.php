<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Buscar configurações globais para o título
try {
    $configs_raw = $pdo->query("SELECT chave, valor FROM configuracoes")->fetchAll();
    $config = [];
    foreach ($configs_raw as $c) {
        $config[$c['chave']] = $c['valor'];
    }
} catch (Exception $e) {
    // Fallback silencioso
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaaS Admin - <?php echo escape($config['site_nome'] ?? 'Imobiliária'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --sidebar-width: 260px; 
            --primary: #0f172a;
            --accent: #d4af37;
            --success: #10b981;
        }
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
        .sidebar { 
            width: var(--sidebar-width); 
            height: 100vh; 
            position: fixed; 
            background: var(--primary); 
            color: white; 
            padding-top: 20px;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }
        .main-content { margin-left: var(--sidebar-width); padding: 30px; min-height: 100vh; }
        .nav-link { 
            color: #94a3b8; 
            padding: 12px 25px; 
            display: flex; 
            align-items: center;
            transition: all 0.3s;
            font-weight: 500;
            border-left: 4px solid transparent;
        }
        .nav-link:hover { color: white; background: rgba(255,255,255,0.05); }
        .nav-link.active { 
            color: white; 
            background: rgba(255,255,255,0.1); 
            border-left-color: var(--accent);
        }
        .nav-link i { font-size: 1.2rem; margin-right: 15px; }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .btn-primary { background-color: var(--primary); border-color: var(--primary); border-radius: 10px; }
        .btn-accent { background-color: var(--accent); color: var(--primary); font-weight: 700; border-radius: 10px; }
        
        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .sidebar h4, .sidebar small, .nav-link span { display: none; }
            .main-content { margin-left: 70px; }
            .nav-link { padding: 15px; justify-content: center; }
            .nav-link i { margin-right: 0; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="px-4 mb-4">
            <h4 class="fw-bold mb-0">Alto Padrão<span class="text-accent"> Imóveis</span></h4>
            <small class="white-text text-uppercase ls-1" style="font-size: 0.65rem;">Enterprise Edition</small>
        </div>
        <nav class="nav flex-column mt-4">
            <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">
                <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
            </a>
            <a class="nav-link <?php echo $current_page == 'lancamentos.php' ? 'active' : ''; ?>" href="lancamentos.php">
                <i class="bi bi-building"></i> <span>Imóveis & Lançamentos</span>
            </a>
            <a class="nav-link <?php echo $current_page == 'leads.php' ? 'active' : ''; ?>" href="leads.php">
                <i class="bi bi-person-badge"></i> <span>CRM de Leads</span>
            </a>
            <a class="nav-link <?php echo $current_page == 'configuracoes.php' ? 'active' : ''; ?>" href="configuracoes.php">
                <i class="bi bi-sliders"></i> <span>Configurações</span>
            </a>
            <div class="mt-auto pb-4">
                <hr class="mx-3 opacity-10">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> <span>Encerrar Sessão</span>
                </a>
            </div>
        </nav>
    </div>
    <div class="main-content">
        <header class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold mb-0">Painel Administrativo</h2>
                <p class="text-muted mb-0 small">Logado como: <strong><?php echo escape($_SESSION['admin_nome']); ?></strong></p>
            </div>
            <div class="d-flex gap-2">
                <a href="../index.php" target="_blank" class="btn btn-white border rounded-pill px-4 shadow-sm">
                    <i class="bi bi-globe me-2"></i> Ver Site Público
                </a>
            </div>
        </header>
