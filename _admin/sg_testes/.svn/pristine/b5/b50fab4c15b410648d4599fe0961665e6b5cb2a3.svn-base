-- nome das fases

CREATE TABLE `label_fase` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `faseID` int(10) NOT NULL DEFAULT '0',
  `subfaseID` int(10) NOT NULL DEFAULT '0',
  `subfasedocID` int(10) NOT NULL DEFAULT '0',
  `nome` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

INSERT INTO `label_fase` (`id`, `faseID`, `subfaseID`, `subfasedocID`, `nome`) VALUES
(1, 1, 1, 1, 'Of&iacute;cio Unidade'),
(2, 1, 1, 2, 'Formul&aacute;rio Solicita&ccedil;&atilde;o de Obra Obra'),
(3, 1, 1, 3, 'Formul&aacute;rio de Abertura de Processo');

-- fases das obras

CREATE TABLE IF NOT EXISTS `obra_fase` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `faseID` int(10) NOT NULL,
  `subfaseID` int(10) NOT NULL,
  `subfasedocID` int(10) NOT NULL,
  `docID` int(10) NOT NULL,
  `obraID` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;