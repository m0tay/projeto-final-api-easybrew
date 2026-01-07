-- Script para adicionar triggers que geram UUID automaticamente
-- Execute este script no banco de dados esan-dsg24

USE `esan-dsg24`;

-- Trigger para tabela users
DROP TRIGGER IF EXISTS `users_before_insert`;
DELIMITER ;;
CREATE TRIGGER `users_before_insert` BEFORE INSERT ON `users`
FOR EACH ROW
BEGIN
  IF NEW.id IS NULL OR NEW.id = '' THEN
    SET NEW.id = UUID();
  END IF;
END;;
DELIMITER ;

-- Trigger para tabela beverages
DROP TRIGGER IF EXISTS `beverages_before_insert`;
DELIMITER ;;
CREATE TRIGGER `beverages_before_insert` BEFORE INSERT ON `beverages`
FOR EACH ROW
BEGIN
  IF NEW.id IS NULL OR NEW.id = '' THEN
    SET NEW.id = UUID();
  END IF;
END;;
DELIMITER ;

-- Trigger para tabela machines
DROP TRIGGER IF EXISTS `machines_before_insert`;
DELIMITER ;;
CREATE TRIGGER `machines_before_insert` BEFORE INSERT ON `machines`
FOR EACH ROW
BEGIN
  IF NEW.id IS NULL OR NEW.id = '' THEN
    SET NEW.id = UUID();
  END IF;
END;;
DELIMITER ;

-- Trigger para tabela transactions
DROP TRIGGER IF EXISTS `transactions_before_insert`;
DELIMITER ;;
CREATE TRIGGER `transactions_before_insert` BEFORE INSERT ON `transactions`
FOR EACH ROW
BEGIN
  IF NEW.id IS NULL OR NEW.id = '' THEN
    SET NEW.id = UUID();
  END IF;
END;;
DELIMITER ;

-- Verificar se os triggers foram criados
SHOW TRIGGERS WHERE `Table` IN ('users', 'beverages', 'machines', 'transactions');
