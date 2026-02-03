<?php
require_once __DIR__ . '/header.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['config'] as $chave => $valor) {
        $stmt = $pdo->prepare("UPDATE configuracoes SET valor = ? WHERE chave = ?");
        $stmt->execute([$valor, $chave]);
    }
    $mensagem = "Configurações atualizadas com sucesso!";
}

// Buscar todas as configurações
$configs = $pdo->query("SELECT * FROM configuracoes")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Configurações do Sistema</h3>
</div>

<?php if ($mensagem): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $mensagem; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST">
                    <div class="row g-4">
                        <?php foreach ($configs as $c): ?>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small text-uppercase ls-1 text-muted"><?php echo escape($c['descricao']); ?></label>
                            <?php if (strlen($c['valor']) > 100 || $c['chave'] == 'endereco'): ?>
                                <textarea name="config[<?php echo $c['chave']; ?>]" class="form-control" rows="2"><?php echo escape($c['valor']); ?></textarea>
                            <?php else: ?>
                                <input type="text" name="config[<?php echo $c['chave']; ?>]" class="form-control" value="<?php echo escape($c['valor']); ?>">
                            <?php endif; ?>
                            <small class="text-muted">Chave técnica: <code><?php echo $c['chave']; ?></code></small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-5 pt-4 border-top">
                        <button type="submit" class="btn btn-primary px-5 rounded-pill">Salvar Todas as Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i> Dica de Ouro</h5>
                <p class="mb-0 opacity-75">As informações cadastradas aqui refletem diretamente em todo o site, incluindo o rodapé, página de contato e links de redes sociais. Mantenha o número do WhatsApp sempre no formato internacional (ex: 5511999999999).</p>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/footer.php'; ?>
