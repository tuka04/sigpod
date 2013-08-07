<?php

class LabelDoc extends DAO{
	
	const Tabela = "label_doc";
	
	public static $campos = array("id","nome","nomeAbrv",
									"campos","emitente","numeroComp",
									"cadAcaoID","novoAcaoID","verAcaoID",
									"despAcaoID","tabBD","campoIndice",
									"campoBusca","acoes","buscavel",
									"atribObra","obra","empresa",
									"docAnexo","docResp","formulario","template");
	public function __construct(){
		parent::__construct(self::Tabela, self::$campos);
	} 
}
?>