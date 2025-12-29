-- Adiciona campo avatar à tabela users
-- Execute este script na base de dados de produção

ALTER TABLE `users` 
ADD COLUMN `avatar` VARCHAR(255) NULL DEFAULT NULL AFTER `is_active`;

-- Comentário: campo para armazenar o nome do ficheiro do avatar do utilizador
