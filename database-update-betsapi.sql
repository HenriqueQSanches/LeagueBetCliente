-- ============================================
-- LEAGUEBET - UPDATE DATABASE FOR BETSAPI
-- ============================================
-- Este script adiciona as colunas necessárias
-- para integração com a BetsAPI
-- ============================================

-- Adiciona coluna api_id na tabela sis_jogos
ALTER TABLE `sis_jogos` 
ADD COLUMN `api_id` VARCHAR(50) NULL DEFAULT NULL COMMENT 'ID do evento na BetsAPI' AFTER `id`,
ADD UNIQUE INDEX `idx_api_id` (`api_id`);

-- Adiciona colunas de controle de atualização
ALTER TABLE `sis_jogos` 
ADD COLUMN `ao_vivo` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Jogo está ao vivo' AFTER `hora`,
ADD COLUMN `placar_casa` INT NULL DEFAULT NULL COMMENT 'Placar time casa' AFTER `ao_vivo`,
ADD COLUMN `placar_fora` INT NULL DEFAULT NULL COMMENT 'Placar time fora' AFTER `placar_casa`,
ADD COLUMN `created_at` DATETIME NULL DEFAULT NULL COMMENT 'Data de criação' AFTER `cotacoes`,
ADD COLUMN `updated_at` DATETIME NULL DEFAULT NULL COMMENT 'Última atualização' AFTER `created_at`;

-- Adiciona coluna codigo na tabela sis_campeonatos (se não existir)
ALTER TABLE `sis_campeonatos` 
ADD COLUMN `codigo` VARCHAR(10) NULL DEFAULT NULL COMMENT 'Código do país (ISO)' AFTER `id`;

-- Adiciona coluna api_id na tabela sis_campeonatos
ALTER TABLE `sis_campeonatos` 
ADD COLUMN `api_id` INT NULL DEFAULT NULL COMMENT 'ID do campeonato na BetsAPI' AFTER `id`,
ADD UNIQUE INDEX `idx_api_id_campeonato` (`api_id`);

-- Atualiza jogos existentes com timestamps
UPDATE `sis_jogos` SET 
    `created_at` = NOW(),
    `updated_at` = NOW()
WHERE `created_at` IS NULL;

-- ============================================
-- ÍNDICES PARA MELHOR PERFORMANCE
-- ============================================

-- Índice para buscar jogos por data
CREATE INDEX `idx_data_hora` ON `sis_jogos` (`data`, `hora`);

-- Índice para buscar jogos ao vivo
CREATE INDEX `idx_ao_vivo` ON `sis_jogos` (`ao_vivo`);

-- Índice para buscar por campeonato
CREATE INDEX `idx_campeonato` ON `sis_jogos` (`campeonato`);

-- Índice para buscar por país (se a coluna existir)
-- CREATE INDEX `idx_pais` ON `sis_jogos` (`pais`);

-- ============================================
-- TABELA DE LOG DE SINCRONIZAÇÃO (OPCIONAL)
-- ============================================

CREATE TABLE IF NOT EXISTS `betsapi_sync_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `tipo` VARCHAR(20) NOT NULL COMMENT 'upcoming, inplay, results',
  `total_eventos` INT NOT NULL DEFAULT 0,
  `eventos_novos` INT NOT NULL DEFAULT 0,
  `eventos_atualizados` INT NOT NULL DEFAULT 0,
  `eventos_erro` INT NOT NULL DEFAULT 0,
  `tempo_execucao` DECIMAL(10,2) NULL COMMENT 'Tempo em segundos',
  `mensagem` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Log de sincronizações com BetsAPI';

-- ============================================
-- CONFIGURAÇÕES DA BETSAPI
-- ============================================

CREATE TABLE IF NOT EXISTS `betsapi_config` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `chave` VARCHAR(50) NOT NULL,
  `valor` TEXT NULL,
  `descricao` VARCHAR(255) NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Configurações da BetsAPI';

-- Insere configurações padrão
INSERT INTO `betsapi_config` (`chave`, `valor`, `descricao`) VALUES
('api_token', '237782-BXpZQecPXZnfW9', 'Token de autenticação da BetsAPI'),
('sync_interval', '300', 'Intervalo de sincronização em segundos (300 = 5 minutos)'),
('sync_enabled', '1', 'Sincronização automática habilitada (1=sim, 0=não)'),
('sport_id', '1', 'ID do esporte (1=Futebol, 18=Basquete)'),
('days_ahead', '3', 'Quantos dias futuros buscar jogos'),
('clean_old_days', '1', 'Remover jogos finalizados há quantos dias')
ON DUPLICATE KEY UPDATE `valor` = VALUES(`valor`);

-- ============================================
-- ATUALIZAÇÃO CONCLUÍDA
-- ============================================

SELECT '✅ Database atualizado com sucesso para BetsAPI!' AS status;

