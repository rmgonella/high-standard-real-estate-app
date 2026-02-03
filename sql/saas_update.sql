-- Update para Nível SaaS Ultimate
-- Adicionando tabelas de auditoria e métricas

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

-- Adicionando campos extras para controle SaaS em leads
ALTER TABLE `leads` ADD COLUMN `origem` varchar(50) DEFAULT 'Site Direto' AFTER `mensagem`;
ALTER TABLE `leads` ADD COLUMN `valor_estimado` decimal(15,2) DEFAULT 0.00 AFTER `status`;

-- Adicionando campo de nível de acesso para usuários
ALTER TABLE `usuarios` ADD COLUMN `nivel` enum('Admin','Moderador') DEFAULT 'Admin' AFTER `senha`;
ALTER TABLE `usuarios` ADD COLUMN `ultimo_login` timestamp NULL DEFAULT NULL AFTER `criado_em`;
