-- Script de Criação do Banco de Dados - Natal Solidário
-- DBMS: MySQL / MariaDB

CREATE DATABASE IF NOT EXISTS `natal_solidario` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `natal_solidario`;

-- 1. Usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `senha_hash` VARCHAR(255) NOT NULL,
  `perfil` ENUM('admin', 'subadmin', 'coordenador', 'coleta', 'turma') NOT NULL DEFAULT 'coleta',
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_usuario_perfil` (`perfil`),
  INDEX `idx_usuario_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Permissoes Subadmin
CREATE TABLE IF NOT EXISTS `permissoes_subadmin` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NOT NULL,
  `modulo` VARCHAR(50) NOT NULL,
  `acao` ENUM('criar', 'ler', 'editar', 'deletar') NOT NULL,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uk_user_modulo_acao` (`usuario_id`, `modulo`, `acao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Turmas
CREATE TABLE IF NOT EXISTS `turmas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(100) NOT NULL,
  `ano_modulo` VARCHAR(50) NOT NULL,
  `lider_nome` VARCHAR(100) NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Usuarios Turmas (Mapeamento)
CREATE TABLE IF NOT EXISTS `usuarios_turmas` (
  `usuario_id` INT NOT NULL,
  `turma_id` INT NOT NULL,
  PRIMARY KEY (`usuario_id`, `turma_id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`turma_id`) REFERENCES `turmas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Lotes Produtos
CREATE TABLE IF NOT EXISTS `lotes_produtos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `codigo_lote` VARCHAR(50) NOT NULL UNIQUE,
  `turma_id` INT NOT NULL,
  `usuario_registro_id` INT NOT NULL,
  `categoria` ENUM('roupa', 'brinquedo', 'alimento', 'higiene', 'outros') NOT NULL,
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`turma_id`) REFERENCES `turmas`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`usuario_registro_id`) REFERENCES `usuarios`(`id`) ON DELETE RESTRICT,
  INDEX `idx_lote_turma` (`turma_id`),
  INDEX `idx_lote_categoria` (`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Produtos Roupas
CREATE TABLE IF NOT EXISTS `produtos_roupas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `lote_id` INT NOT NULL,
  `quantidade` INT NOT NULL DEFAULT 0,
  `qualidade` ENUM('boa', 'ruim') NOT NULL DEFAULT 'boa',
  `faixa_etaria` ENUM('bebe', 'crianca', 'adolescente', 'adulto') NOT NULL,
  FOREIGN KEY (`lote_id`) REFERENCES `lotes_produtos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Produtos Brinquedos
CREATE TABLE IF NOT EXISTS `produtos_brinquedos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `lote_id` INT NOT NULL,
  `quantidade` INT NOT NULL DEFAULT 0,
  `faixa_etaria` ENUM('0-4', '5+') NOT NULL,
  FOREIGN KEY (`lote_id`) REFERENCES `lotes_produtos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Produtos Alimentos
CREATE TABLE IF NOT EXISTS `produtos_alimentos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `lote_id` INT NOT NULL,
  `quantidade` INT NOT NULL DEFAULT 0,
  `tipo_alimento` ENUM('nao_perecivel', 'perecivel', 'bebida', 'outros') NOT NULL,
  `data_validade` DATE NULL,
  `qualidade` ENUM('boa', 'ruim') NOT NULL DEFAULT 'boa',
  FOREIGN KEY (`lote_id`) REFERENCES `lotes_produtos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Produtos Higiene
CREATE TABLE IF NOT EXISTS `produtos_higiene` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `lote_id` INT NOT NULL,
  `quantidade` INT NOT NULL DEFAULT 0,
  `descricao` VARCHAR(255) NOT NULL,
  FOREIGN KEY (`lote_id`) REFERENCES `lotes_produtos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Movimentacoes Estoque
CREATE TABLE IF NOT EXISTS `movimentacoes_estoque` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `lote_id` INT NOT NULL,
  `tipo` ENUM('entrada', 'saida') NOT NULL,
  `quantidade` INT NOT NULL,
  `motivo` VARCHAR(255) NULL,
  `usuario_id` INT NOT NULL,
  `data_movimentacao` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`lote_id`) REFERENCES `lotes_produtos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Lotes Rifas
CREATE TABLE IF NOT EXISTS `lotes_rifas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `turma_id` INT NOT NULL,
  `lider_nome` VARCHAR(100) NOT NULL,
  `quantidade_entregue` INT NOT NULL DEFAULT 0,
  `valor_unitario` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `data_entrega` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `data_prevista_prestacao` DATE NULL,
  `status` ENUM('disponivel', 'entregue', 'em_andamento', 'aguardando_prestacao', 'prestacao_realizada', 'com_divergencia', 'em_atraso', 'finalizado', 'cancelado') NOT NULL DEFAULT 'disponivel',
  `usuario_entrega_id` INT NOT NULL,
  FOREIGN KEY (`turma_id`) REFERENCES `turmas`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`usuario_entrega_id`) REFERENCES `usuarios`(`id`) ON DELETE RESTRICT,
  INDEX `idx_rifa_status` (`status`),
  INDEX `idx_rifa_turma` (`turma_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Prestacao Rifas
CREATE TABLE IF NOT EXISTS `prestacao_rifas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `lote_rifa_id` INT NOT NULL UNIQUE,
  `quantidade_vendida` INT NOT NULL DEFAULT 0,
  `quantidade_devolvida` INT NOT NULL DEFAULT 0,
  `quantidade_perdida` INT NOT NULL DEFAULT 0,
  `valor_esperado` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `valor_entregue` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `diferenca` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `data_prestacao` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `usuario_recebimento_id` INT NOT NULL,
  `observacoes` TEXT NULL,
  FOREIGN KEY (`lote_rifa_id`) REFERENCES `lotes_rifas`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`usuario_recebimento_id`) REFERENCES `usuarios`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Patrocinadores
CREATE TABLE IF NOT EXISTS `patrocinadores` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(150) NOT NULL,
  `tipo` ENUM('dinheiro', 'cestas', 'produtos', 'outros') NOT NULL DEFAULT 'dinheiro',
  `valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `descricao` TEXT NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Financeiro Movimentacoes
CREATE TABLE IF NOT EXISTS `financeiro_movimentacoes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tipo` ENUM('entrada', 'saida') NOT NULL,
  `origem_destino` VARCHAR(150) NOT NULL,
  `descricao` TEXT NULL,
  `valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `lote_rifa_id` INT NULL,
  `patrocinador_id` INT NULL,
  `data_movimentacao` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `usuario_id` INT NOT NULL,
  FOREIGN KEY (`lote_rifa_id`) REFERENCES `lotes_rifas`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`patrocinador_id`) REFERENCES `patrocinadores`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Familias
CREATE TABLE IF NOT EXISTS `familias` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome_responsavel` VARCHAR(150) NOT NULL,
  `quantidade_membros` INT NOT NULL DEFAULT 1,
  `quantidade_filhos` INT NOT NULL DEFAULT 0,
  `endereco` TEXT NOT NULL,
  `status_entrega` ENUM('pendente', 'entregue') NOT NULL DEFAULT 'pendente',
  `data_entrega` DATETIME NULL,
  `usuario_entrega_id` INT NULL,
  FOREIGN KEY (`usuario_entrega_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Configuracoes Sistema
CREATE TABLE IF NOT EXISTS `configuracoes_sistema` (
  `chave` VARCHAR(100) PRIMARY KEY,
  `valor` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DADOS INICIAIS (SEED)

-- Configurações padrão
INSERT INTO `configuracoes_sistema` (`chave`, `valor`) VALUES
('percentual_escola', '50'),
('percentual_turma', '50'),
('pontos_alimento_kilo', '10'),
('pontos_roupas_lote', '15'),
('pontos_brinquedo_lote', '20'),
('pontos_rifa_vendida', '5')
ON DUPLICATE KEY UPDATE `valor` = VALUES(`valor`);

-- Usuário Administrador Padrão (Senha: admin123)
-- Hash gerado via password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha_hash`, `perfil`, `ativo`) VALUES
(1, 'Administrador Diretor', 'admin@natal.local', '$2y$10$wN92C3r0b5Xw2C2X4R3NzeP9e.9zL3/V4N2Y/8G0q0O7k0e0O0m0.', 'admin', 1)
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Turmas iniciais de exemplo
INSERT INTO `turmas` (`id`, `nome`, `ano_modulo`, `lider_nome`, `ativo`) VALUES
(1, '3º Ano A - Informática', '3º Ano', 'João Pedro', 1),
(2, '3º Ano B - Administração', '3º Ano', 'Maria Clara', 1),
(3, '2º Ano A - Edificações', '2º Ano', 'Lucas Silva', 1)
ON DUPLICATE KEY UPDATE `id` = `id`;
