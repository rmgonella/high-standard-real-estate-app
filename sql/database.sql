-- Configuração de Banco de Dados Amaral App v2 Ultimate SaaS
-- Stack: PHP + MySQL

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Tabela de Usuários (Admin)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nivel` enum('Admin','Moderador') DEFAULT 'Admin',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir usuário padrão (senha: admin123)
INSERT INTO `usuarios` (`nome`, `email`, `senha`, `nivel`) VALUES
('Administrador', 'admin@imobiliaria.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin');

-- --------------------------------------------------------
-- Tabela de Lançamentos
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lancamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `localizacao` varchar(255) DEFAULT NULL,
  `preco_a_partir` decimal(15,2) DEFAULT NULL,
  `status` enum('Breve Lançamento','Em Obras','Pronto para Morar') DEFAULT 'Breve Lançamento',
  `destaque` tinyint(1) DEFAULT 0,
  `slug` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabela de Imagens dos Lançamentos
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `imagens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lancamento_id` int(11) DEFAULT NULL,
  `caminho` varchar(255) NOT NULL,
  `ordem` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `lancamento_id` (`lancamento_id`),
  CONSTRAINT `imagens_ibfk_1` FOREIGN KEY (`lancamento_id`) REFERENCES `lancamentos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabela de Leads (CRM)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) NOT NULL,
  `mensagem` text DEFAULT NULL,
  `origem` varchar(50) DEFAULT 'Site Direto',
  `lancamento_id` int(11) DEFAULT NULL,
  `status` enum('Novo','Em Atendimento','Convertido','Arquivado') DEFAULT 'Novo',
  `valor_estimado` decimal(15,2) DEFAULT 0.00,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `lancamento_id` (`lancamento_id`),
  CONSTRAINT `leads_ibfk_1` FOREIGN KEY (`lancamento_id`) REFERENCES `lancamentos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabela de Logs de Auditoria (SaaS)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `logs_auditoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `acao` varchar(255) NOT NULL,
  `detalhes` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `logs_auditoria_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabela de Configurações
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `configuracoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chave` varchar(50) NOT NULL,
  `valor` text DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir configurações iniciais
INSERT INTO `configuracoes` (`chave`, `valor`, `descricao`) VALUES
('site_nome', 'Amaral Imóveis', 'Nome da Imobiliária'),
('whatsapp', '5511999999999', 'Número do WhatsApp para contato'),
('email_contato', 'contato@amaralimoveis.com.br', 'E-mail de contato'),
('endereco', 'Av. Brigadeiro Faria Lima, 2000 - São Paulo, SP', 'Endereço físico'),
('instagram', 'https://instagram.com/amaralimoveis', 'Link do Instagram'),
('facebook', 'https://facebook.com/amaralimoveis', 'Link do Facebook');

COMMIT;
