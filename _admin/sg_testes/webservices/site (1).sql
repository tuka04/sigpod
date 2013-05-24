-- phpMyAdmin SQL Dump
-- version 3.5.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 09, 2012 at 09:59 PM
-- Server version: 5.5.27-log
-- PHP Version: 5.4.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `site`
--

-- --------------------------------------------------------

--
-- Table structure for table `contents`
--

CREATE TABLE IF NOT EXISTS `contents` (
  `id` int(10) NOT NULL,
  `name` varchar(50) CHARACTER SET latin1 NOT NULL,
  `type` varchar(50) CHARACTER SET latin1 NOT NULL,
  `title` varchar(60) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `parent_name` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `contents`
--

INSERT INTO `contents` (`id`, `name`, `type`, `title`, `parent_name`) VALUES
(8, 'area-das-empresas', 'area-das-empresas', '&Aacute;rea das Empresas', NULL),
(7, 'contato', 'static', 'Contato', NULL),
(12, 'equipe', 'static', 'Equipe', 'institucional'),
(5, 'formularios', 'static', 'Formul&aacute;rios e Documentos', NULL),
(3, 'fotos-historicas', 'galeria', 'Fotos Hist&oacute;ricas', NULL),
(10, 'historia', 'static', 'Hist&oacute;ria', 'institucional'),
(1, 'institucional', 'static_menu', 'Institucional', NULL),
(6, 'links', 'static', 'Links &Uacute;teis', NULL),
(11, 'missao', 'static', 'Miss&atilde;o', 'institucional'),
(4, 'noticias', 'noticias', 'Not&iacute;cias', NULL),
(2, 'projetos-obras', 'projetos-obras', 'Projetos e Obras', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `galerias`
--

CREATE TABLE IF NOT EXISTS `galerias` (
  `nome` varchar(100) NOT NULL,
  `nome_secao` varchar(100) NOT NULL,
  `titulo` varchar(250) NOT NULL,
  `descricao` varchar(500) DEFAULT NULL,
  `diretorio` varchar(100) NOT NULL,
  `capa` varchar(100) DEFAULT NULL,
  `ord` int(5) NOT NULL,
  PRIMARY KEY (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Guarda as galerias de foto';

--
-- Dumping data for table `galerias`
--

INSERT INTO `galerias` (`nome`, `nome_secao`, `titulo`, `descricao`, `diretorio`, `capa`, `ord`) VALUES
('cb', 'fotos-historicas', 'Ciclo Básico', NULL, 'cb', NULL, 7),
('cemib', 'fotos-historicas', 'Cemib', NULL, 'cemib', NULL, 5),
('cotil', 'fotos-historicas', 'Colégio Técnico de Limeira', NULL, 'cotil', NULL, 9),
('COTUCA', 'fotos-historicas', 'Colégio Técnico de Campinas', NULL, 'COTUCA', NULL, 8),
('CT', 'fotos-historicas', 'Centro de Tecnologia', NULL, 'CT', 'CT-13.jpg', 3),
('dga', 'fotos-historicas', 'Diretoria Geral de Administração (DGA)', NULL, 'dga', NULL, 10),
('ENG_BAS', 'fotos-historicas', 'Engenharia Básica', NULL, 'ENG_BAS', NULL, 11),
('Entrada_UNICAMP', 'fotos-historicas', 'Entrada da Unicamp', NULL, 'Entrada_UNICAMP', NULL, 3),
('FEA', 'fotos-historicas', 'Faculdade de Engenharia de Alimentos', NULL, 'FEA', NULL, 13),
('FEEC', 'fotos-historicas', 'Faculdade de Engenharia Elétrica e de Computação', NULL, 'FEEC', NULL, 14),
('fef', 'fotos-historicas', 'Faculdade de Educação Física', NULL, 'fef', NULL, 12),
('FOP', 'fotos-historicas', 'Faculdade de Odontologia de Piracicaba', NULL, 'FOP', NULL, 15),
('fotos_aereas', 'fotos-historicas', 'Fotos Aéreas', NULL, 'fotos_aereas', NULL, 2),
('hc', 'fotos-historicas', 'Hospital das Clínicas', NULL, 'hc', NULL, 16),
('hc_tratamento_esgoto_eletrolitico', 'fotos-historicas', 'HC (Tratamento de Esgoto por Processo Eletrolítico)', NULL, 'hc_tratamento_esgoto_eletrolitico', NULL, 17),
('ib', 'fotos-historicas', 'Instituto de Biologia', NULL, 'ib', NULL, 18),
('ifch-iel', 'fotos-historicas', 'IFCH (Instituto de Estudos da Linguagem)', NULL, 'ifch-iel', NULL, 19),
('ifgw', 'fotos-historicas', 'Instituto de Física "Gleb Wataghin"', NULL, 'ifgw', NULL, 20),
('IFGW_ANT_INST_MATEM', 'fotos-historicas', 'IFGW (Antigo Instituto de Matemática)', NULL, 'IFGW_ANT_INST_MATEM', NULL, 22),
('IFGW_Lab_Plasma', 'fotos-historicas', 'IFGW (Laboratório de Plasma)', NULL, 'IFGW_Lab_Plasma', NULL, 21),
('IG-Biblioteca', 'fotos-historicas', 'Instituto de Geociências (biblioteca)', NULL, 'IG-Biblioteca', NULL, 23),
('imecc', 'fotos-historicas', 'Instituto de Matemática, Estatística e Computação Científica', NULL, 'imecc', NULL, 24),
('iq', 'fotos-historicas', 'Instituto de Química', NULL, 'iq', NULL, 25),
('posto_gasolina', 'fotos-historicas', 'Posto de Gasolina', NULL, 'posto_gasolina', NULL, 26),
('primeira_marcenaria', 'fotos-historicas', 'Primeira Marcenaria', NULL, 'primeira_marcenaria', NULL, 27),
('primeiros_estudos', 'fotos-historicas', 'Primeiros Estudos do Campus', NULL, 'primeiros_estudos', NULL, 1),
('reitoria', 'fotos-historicas', 'Reitoria', NULL, 'reitoria', NULL, 28),
('reitoria_praca', 'fotos-historicas', 'Reitoria (Praça)', NULL, 'reitoria_praca', NULL, 29),
('restaurante1_atual_reitoria_vi', 'fotos-historicas', 'Restaurante 1 (atual Reitoria VI)', NULL, 'restaurante1_atual_reitoria_vi', NULL, 30),
('ru', 'fotos-historicas', 'Restaurante Universitário', NULL, 'ru', NULL, 31),
('terraplenagem_campus', 'fotos-historicas', 'Pavimentação e Terraplenagem do Campus', NULL, 'terraplenagem_campus', NULL, 4);

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE IF NOT EXISTS `menus` (
  `sectionName` varchar(100) CHARACTER SET latin1 NOT NULL,
  `fullName` varchar(150) CHARACTER SET latin1 NOT NULL,
  `order` int(5) NOT NULL,
  `type` varchar(50) CHARACTER SET latin1 NOT NULL,
  `controllerName` varchar(75) CHARACTER SET latin1 NOT NULL,
  `actionName` varchar(75) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`sectionName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`sectionName`, `fullName`, `order`, `type`, `controllerName`, `actionName`) VALUES
('area-das-empresas', '&Aacute;rea das Empresas', 9, 'main', 'areaempresas', 'index'),
('contato', 'Contato', 8, 'main', 'contents', ''),
('equipe', 'Equipe', 3, 'submenu', 'contents', ''),
('formularios', 'Formul&aacute;rios', 6, 'main', 'contents', ''),
('fotos-historicas', 'Fotos Hist&oacute;ricas', 4, 'main', 'galerias', 'index'),
('historia', 'Hist&oacute;ria', 1, 'submenu', 'contents', ''),
('index', 'In&iacute;cio', 1, 'main', '', ''),
('institucional', 'Institucional', 2, 'main', 'contents', ''),
('links', 'Links', 7, 'main', 'contents', ''),
('missao', 'Miss&atilde;o', 2, 'submenu', 'contents', ''),
('noticias', 'Not&iacute;cias', 5, 'main', 'noticias', ''),
('projetos-obras', 'Projetos e Obras', 3, 'main', 'projetos_obras', '');

-- --------------------------------------------------------

--
-- Table structure for table `noticias`
--

CREATE TABLE IF NOT EXISTS `noticias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(500) CHARACTER SET latin1 NOT NULL,
  `url` varchar(500) CHARACTER SET latin1 NOT NULL,
  `data` int(11) NOT NULL,
  `ordem` int(3) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ordem` (`ordem`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Guarda as noticias da pagina' AUTO_INCREMENT=10 ;

--
-- Dumping data for table `noticias`
--

INSERT INTO `noticias` (`id`, `titulo`, `url`, `data`, `ordem`) VALUES
(1, 'Paviartes I recebe R$ 1,2 mi em readequações na infraestrutura', 'http://www.unicamp.br/unicamp/divulgacao/2012/03/17/paviartes-i-recebe-r-12-mi-em-readequacoes-na-infraestrutura', 1349772400, 1),
(2, 'Faculdade de Educação Física  inaugura laboratório com infraestrutura de ponta', 'http://www.unicamp.br/unicamp/noticias/2012/04/11/fef-inaugura-laborat%C3%B3rio-com-infraestrutura-de-ponta', 1349772400, 2),
(3, 'Centro de Vivência e restaurante são entregues à comunidade interna', 'http://www.unicamp.br/unicamp/noticias/2012/04/09/restaurante-e-centro-de-viv%C3%AAncia-s%C3%A3o-inaugurados', 1349782400, 3),
(4, 'Triênio rende projeção internacional, indicadores acadêmicos expressivos e significativo incremento de área física', 'http://www.unicamp.br/unicamp/ju/523/tri%C3%AAnio-rende-proje%C3%A7%C3%A3o-internacional-indicadores-acad%C3%AAmicos-expressivos-e-significativo', 1349792400, 4),
(5, 'Conselho Universitário aprova aquisição de área vizinha à Unicamp.', 'http://www.unicamp.br/unicamp/noticias/2012/06/29/conselho-universit%C3%A1rio-aprova-aquisi%C3%A7%C3%A3o-de-%C3%A1rea-vizinha', 1349802400, 5),
(6, 'FCA inaugura complexo com 50 laboratórios', 'http://www.unicamp.br/unicamp/noticias/2012/09/28/fca-inaugura-complexo-com-50-laboratorios', 1349812400, 6),
(7, 'Espaço de Apoio ao Ensino e Aprendizagem (EA)2 ganha novo espaço no Básico 1', 'http://www.unicamp.br/unicamp/noticias/2012/10/22/ea2-ganha-novo-espaco-no-basico-1', 1349772400, 7),
(8, 'Obras qualificam espaços de ensino', 'http://www.unicamp.br/unicamp/ju/541/obras-qualificam-espacos-de-ensino', 1349782400, 8),
(9, 'Aberto e Público - Requalificação da praça do Ciclo Básico', 'http://www.unicamp.br/unicamp/ju/543/aberto-e-publico', 1349783400, 9);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
ALTER TABLE  `noticias` ADD  `visivel` INT( 1 ) NOT NULL DEFAULT  '1';