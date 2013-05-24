 CREATE TABLE IF NOT EXISTS `obra_equipe` (
  `empreendID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  PRIMARY KEY (`empreendID`,`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 
 CREATE TABLE IF NOT EXISTS `chat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from` varchar(255) NOT NULL DEFAULT '',
  `to` varchar(255) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `sent` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `recd` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

 
 ALTER TABLE  `chat` ADD UNIQUE (`id` ,`to` ,`recd`);
 
 CREATE TABLE IF NOT EXISTS `chat_status` (
  `username` varchar(30) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '0',
  `data` int(11) NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `obra_mensagem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuarioID` int(11) NOT NULL,
  `empreendID` int(11) NOT NULL,
  `replyTo` int(11) NOT NULL DEFAULT '0',
  `data` int(11) NOT NULL,
  `assunto` text NOT NULL,
  `conteudo` text,
  `anexos` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Respostas` (`id`,`replyTo`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE  `obra_etapa` ADD  `empreendID` INT( 10 ) NOT NULL DEFAULT  '0' AFTER  `ObraID`;

ALTER TABLE  `obra_fase` ADD  `etapatipoID` INT( 10 ) NOT NULL DEFAULT  '0';

ALTER TABLE  `obra_fase` CHANGE  `subfasedocID`  `fasedocID` INT( 10 ) NOT NULL;

ALTER TABLE  `label_obra_fase` CHANGE  `faseID`  `etapaID` INT( 10 ) NOT NULL;
ALTER TABLE  `label_obra_fase` CHANGE  `subfaseID`  `faseID` INT( 10 ) NOT NULL;
ALTER TABLE  `label_obra_fase` CHANGE  `subfasedocID`  `fasedocID` INT( 10 ) NOT NULL;

ALTER TABLE  `obra_obra` ADD  `observacoes` TEXT NOT NULL;

CREATE TABLE IF NOT EXISTS `label_etapa_estado` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `label` varchar(25) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

INSERT INTO `label_etapa_estado` (`id`, `label`) VALUES
(0, 'Desconhecido'),
(1, 'Iniciado'),
(2, 'Finalizado');

 ALTER TABLE `obra_fase` DROP `subfaseID`;
 
 UPDATE  `label_obra_etapa` SET  `nome` =  'Planejamento' WHERE  `label_obra_etapa`.`id` =1;
 UPDATE  `label_obra_etapa` SET  `nome` =  'Projeto' WHERE  `label_obra_etapa`.`id` =2;
 UPDATE  `label_obra_etapa` SET  `nome` =  'Execu&ccedil;&atilde;o' WHERE  `label_obra_etapa`.`id` =3;
------------------





UPDATE  `label_doc` SET  `campos` =  'emitenteMEMO,numeroMEMO,anoE,destMEMO,assunto,conteudo,anexos' WHERE  `label_doc`.`id` =6;

ALTER TABLE  `doc_memo` CHANGE  `nomeDest`  `destMEMO` VARCHAR( 150 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT  '';

INSERT INTO  `sg_testes`.`label_campo` (
`id` ,
`nome` ,
`label` ,
`tipo` ,
`attr` ,
`extra` ,
`verAcao` ,
`editarAcao`
)
VALUES (
NULL ,  'ref',  'Ref',  'input',  'type="text" size="50" maxlength=""',  '',  '0',  '0'
);

UPDATE  `sg_testes`.`label_doc` SET  `campos` =  'emitenteMEMO,numeroMEMO,anoE,destMEMO,ref,assunto,conteudo,anexos' WHERE  `label_doc`.`id` =6;
ALTER TABLE  `doc_memo` ADD  `ref` TEXT NULL DEFAULT NULL AFTER  `emitenteMEMO`;
INSERT INTO  `sg_testes`.`label_campo` (
`id` ,
`nome` ,
`label` ,
`tipo` ,
`attr` ,
`extra` ,
`verAcao` ,
`editarAcao`
)
VALUES (
NULL ,  'destMEMO',  'Destinat&aacute;rio',  'input',  'type="text" size="34"',  '',  '0',  '0'
);

