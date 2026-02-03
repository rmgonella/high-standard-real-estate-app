<?php
require_once __DIR__ . '/includes/db.php';

// Iniciar sessão se necessário
if (session_status() === PHP_SESSION_NONE) session_start();

// Buscar configurações
try {
    $configs_raw = $pdo->query("SELECT chave, valor FROM configuracoes")->fetchAll();
    $config = [];
    foreach ($configs_raw as $c) {
        $config[$c['chave']] = $c['valor'];
    }
} catch (Exception $e) {
    die("Erro ao carregar configurações do site.");
}

// Buscar todos os lançamentos com imagem principal
$stmt = $pdo->query("
    SELECT l.*, 
    (SELECT caminho FROM imagens WHERE lancamento_id = l.id ORDER BY ordem ASC, id ASC LIMIT 1) as imagem 
    FROM lancamentos l 
    ORDER BY l.destaque DESC, l.criado_em DESC
");
$todos_lancamentos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Imóveis de alto padrão e lançamentos exclusivos. Curadoria selecionada pela <?php echo escape($config['site_nome']); ?>.">
    <title><?php echo escape($config['site_nome']); ?> | Imóveis de Luxo e Lançamentos</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;900&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#"><?php echo escape($config['site_nome']); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#lancamentos">Lançamentos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#diferenciais">Diferenciais</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contato">Contato</a></li>
                    <li class="nav-item ms-lg-3 mt-3 mt-lg-0">
                        <a class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold" href="admin/login.php">
                            <i class="bi bi-lock-fill me-1"></i> Área Restrita
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="container">
            <div class="row">
                <div class="col-lg-10" data-aos="fade-up">
                    <span class="text-uppercase ls-2 mb-3 d-block" style="color: var(--accent-color)">Exclusividade & Elegância</span>
                    <h1>Onde o luxo encontra o seu novo endereço.</h1>
                    <p>Curadoria exclusiva de imóveis de alto padrão e lançamentos icônicos nas localizações mais desejadas.</p>
                    <div class="d-flex gap-3">
                        <a href="#lancamentos" class="btn btn-premium">Explorar Portfólio</a>
                        <a href="#contato" class="btn btn-outline-light px-4 py-3 text-uppercase fw-bold ls-1" style="border-radius: 4px; font-size: 0.8rem;">Agendar Visita</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Lançamentos Section -->
    <section class="py-5" id="lancamentos">
        <div class="container py-5">
            <div class="row mb-5" data-aos="fade-up">
                <div class="col-lg-6">
                    <span class="text-accent text-uppercase fw-bold ls-2">Portfólio</span>
                    <h2 class="display-5 mb-4">Lançamentos Selecionados</h2>
                </div>
                <div class="col-lg-6 d-flex align-items-end">
                    <p class="text-muted">Acompanhe as tendências do mercado imobiliário de luxo com projetos que unem arquitetura inovadora e localizações privilegiadas.</p>
                </div>
            </div>
            
            <div class="row g-4">
                <?php foreach ($todos_lancamentos as $l): ?>
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="property-card">
                        <div class="property-img-wrapper">
                            <span class="property-badge"><?php echo escape($l['status']); ?></span>
                            <img src="<?php echo $l['imagem'] ? 'assets/uploads/'.escape($l['imagem']) : 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=800&q=80'; ?>" alt="<?php echo escape($l['titulo']); ?>">
                        </div>
                        <div class="property-info">
                            <h4><?php echo escape($l['titulo']); ?></h4>
                            <p class="text-muted small mb-3"><i class="bi bi-geo-alt text-accent"></i> <?php echo escape($l['localizacao']); ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                                <div class="property-price"><?php echo formatMoeda($l['preco_a_partir']); ?></div>
                                <a href="#contato" class="btn btn-sm btn-dark px-3" onclick="document.getElementById('lancamento_id').value = '<?php echo $l['id']; ?>'">Saiba Mais</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($todos_lancamentos)): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-building-exclamation display-1 text-muted mb-3"></i>
                    <p class="lead">Nenhum lançamento disponível no momento. Volte em breve!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Diferenciais -->
    <section class="bg-light py-5" id="diferenciais">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-md-4" data-aos="fade-up">
                    <div class="p-4 bg-white h-100 rounded-4 shadow-sm border-bottom border-4 border-accent">
                        <i class="bi bi-shield-check fs-1 mb-4 text-accent"></i>
                        <h4 class="mb-3">Segurança e Sigilo</h4>
                        <p class="text-muted">Atendimento personalizado com total discrição para investidores e compradores que valorizam a privacidade.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="p-4 bg-white h-100 rounded-4 shadow-sm border-bottom border-4 border-accent">
                        <i class="bi bi-gem fs-1 mb-4 text-accent"></i>
                        <h4 class="mb-3">Curadoria Premium</h4>
                        <p class="text-muted">Apenas os melhores empreendimentos, com acabamento impecável e arquitetura assinada pelos maiores nomes.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="p-4 bg-white h-100 rounded-4 shadow-sm border-bottom border-4 border-accent">
                        <i class="bi bi-graph-up-arrow fs-1 mb-4 text-accent"></i>
                        <h4 class="mb-3">Alto Potencial</h4>
                        <p class="text-muted">Foco estratégico em valorização e rentabilidade para o seu patrimônio imobiliário de longo prazo.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contato -->
    <section class="contact-section" id="contato">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-5 mb-lg-0" data-aos="fade-right">
                    <span class="text-accent text-uppercase fw-bold ls-2">Atendimento</span>
                    <h2 class="display-5 mb-4 text-white">Agende uma visita exclusiva</h2>
                    <p class="mb-5 text-white-50">Nossos consultores especializados estão prontos para apresentar cada detalhe dos empreendimentos mais sofisticados do mercado.</p>
                    
                    <div class="d-flex mb-4">
                        <div class="icon-box me-3 bg-accent p-2 rounded text-white">
                            <i class="bi bi-geo-alt fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-white mb-1">Endereço</h6>
                            <p class="text-white-50 mb-0"><?php echo escape($config['endereco']); ?></p>
                        </div>
                    </div>
                    
                    <div class="d-flex mb-4">
                        <div class="icon-box me-3 bg-accent p-2 rounded text-white">
                            <i class="bi bi-envelope fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-white mb-1">E-mail</h6>
                            <p class="text-white-50 mb-0"><?php echo escape($config['email_contato']); ?></p>
                        </div>
                    </div>

                    <div class="d-flex">
                        <div class="icon-box me-3 bg-accent p-2 rounded text-white">
                            <i class="bi bi-whatsapp fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-white mb-1">WhatsApp</h6>
                            <p class="text-white-50 mb-0"><?php echo escape($config['whatsapp']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7" data-aos="fade-left">
                    <div class="contact-card">
                        <form action="processa_lead.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label text-white-50 small">Nome Completo</label>
                                    <input type="text" name="nome" class="form-control" placeholder="Ex: João Silva" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label text-white-50 small">E-mail</label>
                                    <input type="email" name="email" class="form-control" placeholder="exemplo@email.com">
                                </div>
                                <div class="col-md-12 mb-4">
                                    <label class="form-label text-white-50 small">Telefone / WhatsApp</label>
                                    <input type="text" name="telefone" class="form-control" placeholder="(00) 00000-0000" required>
                                </div>
                                <div class="col-md-12 mb-4">
                                    <label class="form-label text-white-50 small">Interesse em</label>
                                    <select name="lancamento_id" id="lancamento_id" class="form-select">
                                        <option value="">Interesse Geral</option>
                                        <?php foreach ($todos_lancamentos as $l): ?>
                                            <option value="<?php echo $l['id']; ?>"><?php echo escape($l['titulo']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-12 mb-4">
                                    <label class="form-label text-white-50 small">Sua Mensagem</label>
                                    <textarea name="mensagem" class="form-control" rows="4" placeholder="Como podemos ajudar você hoje?"></textarea>
                                </div>
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-premium w-100 py-3">Enviar Solicitação</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row mb-5">
                <div class="col-md-4 mb-4 mb-md-0">
                    <div class="footer-logo"><?php echo escape($config['site_nome']); ?></div>
                    <p class="text-white-50">Especialistas em mercado imobiliário de alto padrão, conectando pessoas extraordinárias a imóveis excepcionais.</p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h6 class="text-white text-uppercase ls-1 mb-4">Links Úteis</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#home" class="text-white-50 text-decoration-none">Início</a></li>
                        <li class="mb-2"><a href="#lancamentos" class="text-white-50 text-decoration-none">Lançamentos</a></li>
                        <li class="mb-2"><a href="#contato" class="text-white-50 text-decoration-none">Contato</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="text-white text-uppercase ls-1 mb-4">Siga-nos</h6>
                    <div class="social-links">
                        <a href="<?php echo escape($config['instagram']); ?>" target="_blank"><i class="bi bi-instagram"></i></a>
                        <a href="<?php echo escape($config['facebook']); ?>" target="_blank"><i class="bi bi-facebook"></i></a>
                    </div>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="text-center pt-4">
                <p class="mb-0 text-white-50 small">&copy; <?php echo date('Y'); ?> <?php echo escape($config['site_nome']); ?>. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Float -->
    <a href="https://wa.me/<?php echo preg_replace('/\D/', '', $config['whatsapp']); ?>" class="whatsapp-float" target="_blank">
        <i class="bi bi-whatsapp"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                document.querySelector('.navbar').classList.add('scrolled');
            } else {
                document.querySelector('.navbar').classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>
