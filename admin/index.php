<?php
require_once __DIR__ . '/header.php';

// Contadores e Métricas SaaS
$total_lancamentos = $pdo->query("SELECT COUNT(*) FROM lancamentos")->fetchColumn();
$total_leads = $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$leads_novos = $pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'Novo'")->fetchColumn();

// Cálculo de Taxa de Conversão (Convertidos / Total)
$convertidos = $pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'Convertido'")->fetchColumn();
$taxa_conversao = ($total_leads > 0) ? round(($convertidos / $total_leads) * 100, 1) : 0;

// Valor Estimado do Funil (Soma do valor_estimado dos leads não arquivados)
// Nota: Adicionamos essa coluna no saas_update.sql
try {
    $valor_funil = $pdo->query("SELECT SUM(valor_estimado) FROM leads WHERE status != 'Arquivado'")->fetchColumn();
} catch (Exception $e) {
    $valor_funil = 0;
}

// Dados para o gráfico de evolução
$grafico_data = $pdo->query("
    SELECT DATE(criado_em) as data, COUNT(*) as total 
    FROM leads 
    WHERE criado_em >= DATE_SUB(NOW(), INTERVAL 14 DAY)
    GROUP BY DATE(criado_em)
    ORDER BY data ASC
")->fetchAll();

$labels = [];
$values = [];
foreach ($grafico_data as $row) {
    $labels[] = date('d/m', strtotime($row['data']));
    $values[] = $row['total'];
}

// Últimos leads com mais detalhes
$stmt = $pdo->query("
    SELECT l.*, lan.titulo as lancamento_nome 
    FROM leads l 
    LEFT JOIN lancamentos lan ON l.lancamento_id = lan.id 
    ORDER BY l.criado_em DESC 
    LIMIT 5
");
$ultimos_leads = $stmt->fetchAll();
?>

<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card p-4 h-100 border-start border-primary border-4">
            <h6 class="text-muted small text-uppercase fw-bold mb-2">Total Leads</h6>
            <h3 class="fw-bold mb-0"><?php echo $total_leads; ?></h3>
            <small class="text-success"><i class="bi bi-graph-up"></i> Base consolidada</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-4 h-100 border-start border-warning border-4">
            <h6 class="text-muted small text-uppercase fw-bold mb-2">Leads Pendentes</h6>
            <h3 class="fw-bold mb-0"><?php echo $leads_novos; ?></h3>
            <small class="text-warning"><i class="bi bi-clock"></i> Aguardando retorno</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-4 h-100 border-start border-success border-4">
            <h6 class="text-muted small text-uppercase fw-bold mb-2">Taxa de Conversão</h6>
            <h3 class="fw-bold mb-0"><?php echo $taxa_conversao; ?>%</h3>
            <small class="text-muted">Eficiência de vendas</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-4 h-100 border-start border-accent border-4">
            <h6 class="text-muted small text-uppercase fw-bold mb-2">Pipeline Estimado</h6>
            <h3 class="fw-bold mb-0"><?php echo formatMoeda($valor_funil); ?></h3>
            <small class="text-muted">Potencial de receita</small>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Evolução de Leads (14 dias)</h5>
            </div>
            <div class="card-body">
                <canvas id="leadsChart" height="300"></canvas>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Atividade Recente</h5>
                <a href="leads.php" class="btn btn-sm btn-outline-primary">Ver CRM Completo</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Lead</th>
                                <th>Interesse</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimos_leads as $lead): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?php echo escape($lead['nome']); ?></div>
                                    <div class="small text-muted"><?php echo date('d/m/Y H:i', strtotime($lead['criado_em'])); ?></div>
                                </td>
                                <td>
                                    <span class="small"><?php echo escape($lead['lancamento_nome'] ?? 'Geral'); ?></span>
                                </td>
                                <td>
                                    <?php 
                                    $badge_class = 'bg-secondary';
                                    if ($lead['status'] == 'Novo') $badge_class = 'bg-warning text-dark';
                                    if ($lead['status'] == 'Convertido') $badge_class = 'bg-success';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?> rounded-pill"><?php echo $lead['status']; ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="leads.php?id=<?php echo $lead['id']; ?>" class="btn btn-sm btn-light border"><i class="bi bi-chevron-right"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm bg-primary text-white mb-4">
            <div class="card-body p-4">
                <h6 class="text-uppercase fw-bold ls-1 opacity-75 small mb-3">Dica de Performance</h6>
                <p class="mb-0 small">Leads atendidos nas primeiras <strong>2 horas</strong> têm 7x mais chances de conversão. Mantenha seu dashboard sempre atualizado.</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 px-4">
                <h5 class="fw-bold mb-0">Logs de Auditoria</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    <?php
                    try {
                        $logs = $pdo->query("SELECT * FROM logs_auditoria ORDER BY criado_em DESC LIMIT 6")->fetchAll();
                        foreach ($logs as $log): ?>
                        <li class="list-group-item px-4 py-3">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold"><?php echo escape($log['acao']); ?></span>
                                <span class="text-muted" style="font-size: 0.75rem;"><?php echo date('H:i', strtotime($log['criado_em'])); ?></span>
                            </div>
                            <div class="text-muted truncate-1"><?php echo escape($log['detalhes']); ?></div>
                        </li>
                        <?php endforeach;
                        if (empty($logs)): ?>
                        <li class="list-group-item px-4 py-3 text-center text-muted">Nenhum log registrado.</li>
                        <?php endif;
                    } catch (Exception $e) {
                        echo '<li class="list-group-item px-4 py-3 text-center text-muted">Auditoria desativada.</li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('leadsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Novos Leads',
                data: <?php echo json_encode($values); ?>,
                borderColor: '#d4af37',
                backgroundColor: 'rgba(212, 175, 55, 0.05)',
                borderWidth: 4,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });
</script>

<?php include_once __DIR__ . '/footer.php'; ?>
