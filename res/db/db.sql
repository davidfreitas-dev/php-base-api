-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Tempo de geração: 21/01/2024 às 17:59
-- Versão do servidor: 8.0.35
-- Versão do PHP: 8.2.13

create database php_base_api_db;

use php_base_api_db;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `php_base_api_db`
--

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_users_delete`(`piduser` INT)
BEGIN
    
  DECLARE vidperson INT;
  
  SET FOREIGN_KEY_CHECKS = 0;
	
	SELECT idperson INTO vidperson
  FROM tb_users
  WHERE iduser = piduser;
  
	DELETE FROM tb_persons WHERE idperson = vidperson;    
  DELETE FROM tb_userslogs WHERE iduser = piduser;
  DELETE FROM tb_userspasswordsrecoveries WHERE iduser = piduser;
  DELETE FROM tb_users WHERE iduser = piduser;
  
  SET FOREIGN_KEY_CHECKS = 1;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_users_create`(`pdesperson` VARCHAR(64), `pdeslogin` VARCHAR(64), `pdespassword` VARCHAR(256), `pdesemail` VARCHAR(128), `pnrphone` VARCHAR(15), `pnrcpf` VARCHAR(15), `pinadmin` TINYINT)
BEGIN
  
  DECLARE vidperson INT;
    
  INSERT INTO tb_persons (desperson, desemail, nrphone, nrcpf)
  VALUES(pdesperson, pdesemail, pnrphone, pnrcpf);
  
  SET vidperson = LAST_INSERT_ID();
  
  INSERT INTO tb_users (idperson, deslogin, despassword, inadmin)
  VALUES(vidperson, pdeslogin, pdespassword, pinadmin);
  
  SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = LAST_INSERT_ID();
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_users_update`(`piduser` INT, `pdesperson` VARCHAR(64), `pdeslogin` VARCHAR(64), `pdespassword` VARCHAR(256), `pdesemail` VARCHAR(128), `pnrphone` VARCHAR(15), `pnrcpf` VARCHAR(15), `pinadmin` TINYINT)
BEGIN
  
  DECLARE vidperson INT;
    
  SELECT idperson INTO vidperson
  FROM tb_users
  WHERE iduser = piduser;
  
  UPDATE tb_persons
  SET desperson = pdesperson,
      desemail = pdesemail,
      nrphone = pnrphone,
      nrcpf = nrcpf
  WHERE idperson = vidperson;
    
  UPDATE tb_users
  SET deslogin = pdeslogin,
      despassword = pdespassword,
      inadmin = pinadmin
  WHERE iduser = piduser;
    
  SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = piduser;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_userspasswordsrecoveries_create`(`piduser` INT, `pdesip` VARCHAR(45))
BEGIN
  
  INSERT INTO tb_userspasswordsrecoveries (iduser, desip)
  VALUES(piduser, pdesip);
  
  SELECT * FROM tb_userspasswordsrecoveries
  WHERE idrecovery = LAST_INSERT_ID();
    
END$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_persons`
--

CREATE TABLE `tb_persons` (
  `idperson` int NOT NULL,
  `desperson` varchar(64) NOT NULL,
  `desemail` varchar(128) NOT NULL,
  `nrphone` varchar(15) DEFAULT NULL,
  `nrcpf` varchar(15) DEFAULT NULL,
  `dtregister` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `tb_persons`
--

INSERT INTO `tb_persons` (`idperson`, `desperson`, `desemail`, `nrphone`, `nrcpf`, `dtregister`) VALUES
(1, 'Admin', 'admin@email.com', NULL, NULL, '2024-01-01 00:00:00'),
(2, 'User', 'user@email.com', NULL, NULL, '2024-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_users`
--

CREATE TABLE `tb_users` (
  `iduser` int NOT NULL,
  `idperson` int NOT NULL,
  `deslogin` varchar(64) NOT NULL,
  `despassword` varchar(256) NOT NULL,
  `inadmin` tinyint NOT NULL DEFAULT '0',
  `dtregister` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `tb_users`
--

INSERT INTO `tb_users` (`iduser`, `idperson`, `deslogin`, `despassword`, `inadmin`, `dtregister`) VALUES
(1, 1, 'admin', '$2y$12$YlooCyNvyTji8bPRcrfNfOKnVMmZA9ViM2A3IpFjmrpIbp5ovNmga', 1, '2021-08-11 17:32:22'),
(2, 2, 'user', '$2y$12$Hl0YFyxxuJ8c/TPpoShRZ.0r3PTP24mL7Q3iTBvC70Reou1dp4ZS6', 0, '2021-08-11 17:32:22');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_userslogs`
--

CREATE TABLE `tb_userslogs` (
  `idlog` int NOT NULL,
  `iduser` int NOT NULL,
  `deslog` varchar(128) NOT NULL,
  `desip` varchar(45) NOT NULL,
  `desuseragent` varchar(128) NOT NULL,
  `dessessionid` varchar(64) NOT NULL,
  `desurl` varchar(128) NOT NULL,
  `dtregister` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_userspasswordsrecoveries`
--

CREATE TABLE `tb_userspasswordsrecoveries` (
  `idrecovery` int NOT NULL,
  `iduser` int NOT NULL,
  `desip` varchar(45) NOT NULL,
  `dtrecovery` datetime DEFAULT NULL,
  `dtregister` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `tb_persons`
--
ALTER TABLE `tb_persons`
  ADD PRIMARY KEY (`idperson`);

--
-- Índices de tabela `tb_users`
--
ALTER TABLE `tb_users`
  ADD PRIMARY KEY (`iduser`),
  ADD KEY `FK_users_persons_idx` (`idperson`);

--
-- Índices de tabela `tb_userslogs`
--
ALTER TABLE `tb_userslogs`
  ADD PRIMARY KEY (`idlog`),
  ADD KEY `fk_userslogs_users_idx` (`iduser`);

--
-- Índices de tabela `tb_userspasswordsrecoveries`
--
ALTER TABLE `tb_userspasswordsrecoveries`
  ADD PRIMARY KEY (`idrecovery`),
  ADD KEY `fk_userspasswordsrecoveries_users_idx` (`iduser`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `tb_persons`
--
ALTER TABLE `tb_persons`
  MODIFY `idperson` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `tb_users`
--
ALTER TABLE `tb_users`
  MODIFY `iduser` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `tb_userslogs`
--
ALTER TABLE `tb_userslogs`
  MODIFY `idlog` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_userspasswordsrecoveries`
--
ALTER TABLE `tb_userspasswordsrecoveries`
  MODIFY `idrecovery` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `tb_users`
--
ALTER TABLE `tb_users`
  ADD CONSTRAINT `fk_users_persons` FOREIGN KEY (`idperson`) REFERENCES `tb_persons` (`idperson`);

--
-- Restrições para tabelas `tb_userslogs`
--
ALTER TABLE `tb_userslogs`
  ADD CONSTRAINT `fk_userslogs_users` FOREIGN KEY (`iduser`) REFERENCES `tb_users` (`iduser`);

--
-- Restrições para tabelas `tb_userspasswordsrecoveries`
--
ALTER TABLE `tb_userspasswordsrecoveries`
  ADD CONSTRAINT `fk_userspasswordsrecoveries_users` FOREIGN KEY (`iduser`) REFERENCES `tb_users` (`iduser`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
