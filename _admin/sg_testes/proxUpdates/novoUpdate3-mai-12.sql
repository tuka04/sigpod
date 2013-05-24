UPDATE  `label_campo` SET  `editarAcao` =  '3' WHERE  `label_campo`.`id` =46;

UPDATE  `label_campo` SET  `editarAcao` =  '3' WHERE  `label_campo`.`id` =3;

UPDATE  `label_campo` SET  `editarAcao` =  '3' WHERE  `label_campo`.`id` =18;

UPDATE  `label_campo` SET  `editarAcao` =  '3' WHERE  `label_campo`.`id` =17;

UPDATE  `label_campo` SET  `editarAcao` =  '3' WHERE  `label_campo`.`id` =62;

UPDATE  `label_campo` SET  `editarAcao` =  '3' WHERE  `label_campo`.`id` =54;

UPDATE  `label_campo` SET  `editarAcao` =  '3' WHERE  `label_campo`.`id` =15;

UPDATE  `label_campo` SET  `editarAcao` =  '3' WHERE  `label_campo`.`id` =61;

UPDATE  `label_campo` SET  `editarAcao` =  '3' WHERE  `label_campo`.`id` =16;

ALTER TABLE  `obra_empreendimento` ADD  `nomeBusca` VARCHAR( 250 ) NULL AFTER  `nome`;

ALTER TABLE  `obra_obra` ADD  `nomeBusca` VARCHAR( 250 ) NULL AFTER  `nome`;

ALTER TABLE  `sg_testes`.`obra_obra` DROP INDEX  `cod` , ADD UNIQUE  `cod` (  `cod` ,  `nomeBusca` );

CREATE INDEX nomeBusca ON obra_empreendimento (id, nomeBusca);
ALTER TABLE  `obra_etapa` ADD  `enabled` INT( 1 ) NOT NULL