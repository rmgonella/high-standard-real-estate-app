<?php
require_once __DIR__ . '/header.php';

$mensagem = '';
$erro = '';

// Lógica para Deletar
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Buscar imagens para deletar arquivos físicos
    $stmtImgs = $pdo->prepare("SELECT caminho FROM imagens WHERE lancamento_id = ?");
    $stmtImgs->execute([$id]);
    $imgs = $stmtImgs->fetchAll();
    foreach ($imgs as $img) {
        $file = __DIR__ . '/../assets/uploads/' . $img['caminho'];
        if (file_exists($file)) unlink($file);
    }
    
    $stmt = $pdo->prepare("DELETE FROM lancamentos WHERE id = ?");
    $stmt->execute([$id]);
    
    registrar_log("Exclusão de Lançamento", "ID: $id removido permanentemente.");
    $mensagem = "Lançamento e suas imagens foram excluídos com sucesso!";
}

// Lógica para Salvar (Novo ou Editar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $titulo = sanitize($_POST['titulo']);
    $descricao = $_POST['descricao']; 
    $localizacao = sanitize($_POST['localizacao']);
    $preco = (float)$_POST['preco_a_partir'];
    $status = $_POST['status'];
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $slug = createSlug($titulo);

    try {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE lancamentos SET titulo=?, descricao=?, localizacao=?, preco_a_partir=?, status=?, destaque=?, slug=? WHERE id=?");
            $stmt->execute([$titulo, $descricao, $localizacao, $preco, $status, $destaque, $slug, $id]);
            registrar_log("Edição de Lançamento", "Título: $titulo (ID: $id)");
            $mensagem = "Lançamento atualizado com sucesso!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO lancamentos (titulo, descricao, localizacao, preco_a_partir, status, destaque, slug) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$titulo, $descricao, $localizacao, $preco, $status, $destaque, $slug]);
            $id = $pdo->lastInsertId();
            registrar_log("Criação de Lançamento", "Título: $titulo (ID: $id)");
            $mensagem = "Lançamento cadastrado com sucesso!";
        }

        // Upload de Múltiplas Imagens
        if (!empty($_FILES['imagens']['name'][0])) {
            $files = $_FILES['imagens'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === 0) {
                    $type = $files['type'][$i];
                    if (in_array($type, $allowed_types)) {
                        $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                        $nome_arquivo = bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
                        $caminho_destino = __DIR__ . '/../assets/uploads/' . $nome_arquivo;
                        
                        if (move_uploaded_file($files['tmp_name'][$i], $caminho_destino)) {
                            $stmt = $pdo->prepare("INSERT INTO imagens (lancamento_id, caminho, ordem) VALUES (?, ?, ?)");
                            $stmt->execute([$id, $nome_arquivo, $i]);
                        }
                    } else {
                        $erro = "Alguns arquivos não foram enviados por terem formato inválido.";
                    }
                }
            }
        }
    } catch (Exception $e) {
        $erro = "Erro ao salvar: " . $e->getMessage();
    }
}

// Buscar todos os lançamentos
$lancamentos = $pdo->query("
    SELECT l.*, (SELECT caminho FROM imagens WHERE lancamento_id = l.id LIMIT 1) as capa 
    FROM lancamentos l 
    ORDER BY criado_em DESC
")->fetchAll();

// Buscar dados para edição
$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM lancamentos WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_data = $stmt->fetch();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Gestão de Imóveis</h3>
    <button class="btn btn-primary shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#formLancamento">
        <i class="bi bi-plus-lg me-2"></i> Adicionar Imóvel
    </button>
</div>

<?php if ($mensagem): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <?php echo $mensagem; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="collapse <?php echo $edit_data ? 'show' : ''; ?> mb-5" id="formLancamento">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-4"><?php echo $edit_data ? 'Editar' : 'Novo'; ?> Imóvel</h5>
            <form method="POST" enctype="multipart/form-data">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Título</label>
                        <input type="text" name="titulo" class="form-control" value="<?php echo $edit_data['titulo'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Localização</label>
                        <input type="text" name="localizacao" class="form-control" value="<?php echo $edit_data['localizacao'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Preço Base (R$)</label>
                        <input type="number" step="0.01" name="preco_a_partir" class="form-control" value="<?php echo $edit_data['preco_a_partir'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Status Comercial</label>
                        <select name="status" class="form-select">
                            <option value="Breve Lançamento" <?php echo (isset($edit_data['status']) && $edit_data['status'] == 'Breve Lançamento') ? 'selected' : ''; ?>>Breve Lançamento</option>
                            <option value="Em Obras" <?php echo (isset($edit_data['status']) && $edit_data['status'] == 'Em Obras') ? 'selected' : ''; ?>>Em Obras</option>
                            <option value="Pronto para Morar" <?php echo (isset($edit_data['status']) && $edit_data['status'] == 'Pronto para Morar') ? 'selected' : ''; ?>>Pronto para Morar</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Upload de Imagens</label>
                        <input type="file" name="imagens[]" class="form-control" multiple accept="image/*">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold small">Descrição do Empreendimento</label>
                        <textarea name="descricao" class="form-control" rows="4"><?php echo $edit_data['descricao'] ?? ''; ?></textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="destaque" id="destaque" <?php echo (isset($edit_data['destaque']) && $edit_data['destaque']) ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-bold" for="destaque">Destaque na vitrine principal</label>
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">Salvar Registro</button>
                    <a href="lancamentos.php" class="btn btn-light px-4 border">Descartar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Preview</th>
                    <th>Empreendimento</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Gestão</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lancamentos as $l): ?>
                <tr>
                    <td class="ps-4">
                        <img src="<?php echo $l['capa'] ? '../assets/uploads/'.$l['capa'] : 'https://via.placeholder.com/80x50'; ?>" class="rounded-3 shadow-sm" style="width: 80px; height: 50px; object-fit: cover;">
                    </td>
                    <td>
                        <div class="fw-bold"><?php echo escape($l['titulo']); ?></div>
                        <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?php echo escape($l['localizacao']); ?></div>
                    </td>
                    <td class="fw-bold text-primary"><?php echo formatMoeda($l['preco_a_partir']); ?></td>
                    <td>
                        <span class="badge bg-light text-dark border"><?php echo $l['status']; ?></span>
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group">
                            <a href="?edit=<?php echo $l['id']; ?>" class="btn btn-sm btn-white border shadow-sm"><i class="bi bi-pencil-square"></i></a>
                            <a href="?delete=<?php echo $l['id']; ?>" class="btn btn-sm btn-white border text-danger shadow-sm" onclick="return confirm('Deseja realmente excluir este imóvel?')"><i class="bi bi-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once __DIR__ . '/footer.php'; ?>
