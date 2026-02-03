<?php
require_once __DIR__ . '/header.php';

$mensagem = '';

// Atualizar Status e Valor do Lead (Nível SaaS)
if (isset($_POST['update_lead'])) {
    $id = (int)$_POST['lead_id'];
    $novo_status = $_POST['status'];
    $valor = (float)$_POST['valor_estimado'];
    
    $stmt = $pdo->prepare("UPDATE leads SET status = ?, valor_estimado = ? WHERE id = ?");
    $stmt->execute([$novo_status, $valor, $id]);
    
    registrar_log("Atualização de Lead", "Lead ID $id atualizado para status $novo_status com valor " . formatMoeda($valor));
    $mensagem = "Informações do lead atualizadas com sucesso!";
}

// Deletar Lead
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM leads WHERE id = ?");
    $stmt->execute([$id]);
    
    registrar_log("Exclusão de Lead", "Lead ID $id removido permanentemente.");
    $mensagem = "Lead removido com sucesso!";
}

// Buscar Lead específico para visualização
$lead_detalhe = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("
        SELECT l.*, lan.titulo as lancamento_nome 
        FROM leads l 
        LEFT JOIN lancamentos lan ON l.lancamento_id = lan.id 
        WHERE l.id = ?
    ");
    $stmt->execute([(int)$_GET['id']]);
    $lead_detalhe = $stmt->fetch();
}

// Buscar todos os leads
$leads = $pdo->query("
    SELECT l.*, lan.titulo as lancamento_nome 
    FROM leads l 
    LEFT JOIN lancamentos lan ON l.lancamento_id = lan.id 
    ORDER BY l.criado_em DESC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">CRM de Vendas</h3>
</div>

<?php if ($mensagem): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <?php echo $mensagem; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Lista de Leads -->
    <div class="<?php echo $lead_detalhe ? 'col-lg-7' : 'col-lg-12'; ?>">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Data</th>
                            <th>Nome / Contato</th>
                            <th>Status / Pipeline</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $l): ?>
                        <tr class="<?php echo ($lead_detalhe && $lead_detalhe['id'] == $l['id']) ? 'table-active' : ''; ?>">
                            <td class="ps-4 small text-muted">
                                <?php echo date('d/m/y', strtotime($l['criado_em'])); ?><br>
                                <?php echo date('H:i', strtotime($l['criado_em'])); ?>
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo escape($l['nome']); ?></div>
                                <div class="small text-muted"><i class="bi bi-whatsapp"></i> <?php echo escape($l['telefone']); ?></div>
                            </td>
                            <td>
                                <?php 
                                $badge_class = 'bg-secondary';
                                if ($l['status'] == 'Novo') $badge_class = 'bg-warning text-dark';
                                if ($l['status'] == 'Em Atendimento') $badge_class = 'bg-info text-white';
                                if ($l['status'] == 'Convertido') $badge_class = 'bg-success';
                                ?>
                                <span class="badge <?php echo $badge_class; ?> rounded-pill mb-1"><?php echo $l['status']; ?></span>
                                <div class="small fw-bold text-accent"><?php echo formatMoeda($l['valor_estimado'] ?? 0); ?></div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm">
                                    <a href="?id=<?php echo $l['id']; ?>" class="btn btn-sm btn-white border"><i class="bi bi-eye"></i></a>
                                    <a href="?delete=<?php echo $l['id']; ?>" class="btn btn-sm btn-white border text-danger" onclick="return confirm('Excluir este lead permanentemente?')"><i class="bi bi-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detalhes do Lead -->
    <?php if ($lead_detalhe): ?>
    <div class="col-lg-5 mt-4 mt-lg-0">
        <div class="card border-0 shadow-lg sticky-top" style="top: 100px; z-index: 1; border-radius: 20px;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <h5 class="fw-bold mb-0">Gestão de Lead</h5>
                    <a href="leads.php" class="btn-close"></a>
                </div>
                
                <div class="mb-4">
                    <label class="text-muted small text-uppercase fw-bold ls-1">Informações do Cliente</label>
                    <h4 class="fw-bold mt-1 mb-3"><?php echo escape($lead_detalhe['nome']); ?></h4>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex align-items-center">
                            <div class="bg-light p-2 rounded-circle me-3"><i class="bi bi-envelope text-primary"></i></div>
                            <span><?php echo escape($lead_detalhe['email'] ?: 'Não informado'); ?></span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="bg-light p-2 rounded-circle me-3"><i class="bi bi-whatsapp text-success"></i></div>
                            <a href="https://wa.me/<?php echo preg_replace('/\D/', '', $lead_detalhe['telefone']); ?>" target="_blank" class="text-decoration-none fw-bold">
                                <?php echo escape($lead_detalhe['telefone']); ?>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-muted small text-uppercase fw-bold ls-1">Interesse Declarado</label>
                    <div class="p-3 bg-light rounded-3 mt-1">
                        <i class="bi bi-building me-2 text-accent"></i> <strong><?php echo escape($lead_detalhe['lancamento_nome'] ?? 'Interesse Geral'); ?></strong>
                        <div class="small text-muted mt-1">Origem: <?php echo escape($lead_detalhe['origem'] ?? 'Site Direto'); ?></div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-muted small text-uppercase fw-bold ls-1">Mensagem do Cliente</label>
                    <div class="mt-1 p-3 bg-white border rounded-3 italic shadow-sm" style="font-style: italic;">
                        "<?php echo nl2br(escape($lead_detalhe['mensagem'])); ?>"
                    </div>
                </div>

                <div class="pt-4 border-top">
                    <form method="POST">
                        <input type="hidden" name="lead_id" value="<?php echo $lead_detalhe['id']; ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Status do Funil</label>
                                <select name="status" class="form-select shadow-sm">
                                    <option value="Novo" <?php echo $lead_detalhe['status'] == 'Novo' ? 'selected' : ''; ?>>Novo</option>
                                    <option value="Em Atendimento" <?php echo $lead_detalhe['status'] == 'Em Atendimento' ? 'selected' : ''; ?>>Em Atendimento</option>
                                    <option value="Convertido" <?php echo $lead_detalhe['status'] == 'Convertido' ? 'selected' : ''; ?>>Convertido</option>
                                    <option value="Arquivado" <?php echo $lead_detalhe['status'] == 'Arquivado' ? 'selected' : ''; ?>>Arquivado</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Valor Estimado (R$)</label>
                                <input type="number" step="0.01" name="valor_estimado" class="form-control shadow-sm" value="<?php echo $lead_detalhe['valor_estimado'] ?? 0; ?>">
                            </div>
                            <div class="col-12">
                                <button type="submit" name="update_lead" class="btn btn-primary w-100 py-2 fw-bold">
                                    <i class="bi bi-save me-2"></i> Atualizar Negociação
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/footer.php'; ?>
