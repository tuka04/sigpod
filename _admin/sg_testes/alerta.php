<?php

include_once('includeAll.php');
requireSubModule(array("alerta","frontend"));
if(isset($_REQUEST["removeAlerta"])){
	$a = new Alerta(false);
	echo json_encode($a->removeAlerta());
}
else if(isset($_REQUEST["getUsersName"])){
	echo json_encode(getUsersName());
}
else if(isset($_REQUEST["salvarGerenciaAlertas"])){
	echo json_encode(salvarGerenciaAlertas());
}
else if(isset($_REQUEST["removerGerenciaAlertas"])){
	echo json_encode(array("msg"=>removerGerenciaAlertas()));
}

function getDialog(){
	$dialog = new HtmlTag("div", "dialogAlerta", "");
	$span = new HtmlTag("span", "", "");
	$remover = new HtmlTag("a", "rmAlerta", "","[Remover Alerta]");
	$remover->setAttr(array("href","onclick"), array("#","javascript:removeAlerta();"));
	$span->setVar("content", "Para todos os documentos selecionados: ".$remover->toString());
	$dialog->setChildren($span);
	$dialog->setStyle("display", "none");
	$alerta = new Alerta();
	$dialog->setChildren($alerta->getTable());
	return $dialog->toString();
}

function getUsersName(){
	$u = new UsuarioAlerta();
	$s = $u->selectUsuarios("nomeCompl","%".$_REQUEST["nome"]."%"," LIKE ");
	$r = new ArrayObj();
	foreach ($s as $v){
		$aux = new ArrayObj();
		$aux->offsetSet("id", $v["id"]);
		$aux->offsetSet("nome", ($v["nomeCompl"]));
		$r->append($aux->getArrayCopy());
	}
	return $r->getArrayCopy();
}

function salvarGerenciaAlertas(){
	$u = new UsuarioAlerta();
	return $u->insert(array($_REQUEST["id"]));
}

function removerGerenciaAlertas(){
	$u = new UsuarioAlerta();
	return $u->remove("id",explode(",",$_REQUEST["id"])," IN ");
}
?>