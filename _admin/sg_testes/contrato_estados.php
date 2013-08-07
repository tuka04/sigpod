<?php
include_once('includeAll.php');
if (!isset($_SESSION)) session_start();
//verifica login
checkLogin(6);
//processamento de requisicoes
//requisicao de systema
if(isset($_REQUEST["getSysContratoEstado"])){
	//verifica se o usuario tem permissao para administrar o sistema
	checkPermission(20);
	requireSubModule("contrato_estado");
	$sce = new SysContratoEstado();
	$arr = new ArrayObj();
	$table = $sce->toHtmlTable();
	$arr->offsetSet("tabela", $table->toString());
	$arr->offsetSet("tabelaID", $table->getVar("id"));
	$arr->offsetSet("dialogID", "dialogGerenciarEstado");
	echo $arr->toJson();
}
else if(isset($_REQUEST["saveSysContratoEstado"])){
	//verifica se o usuario tem permissao para administrar o sistema
	checkPermission(20);
	requireSubModule("contrato_estado");
	$sce = new SysContratoEstado();
	$r = new ArrayObj($sce->select("nome",$_REQUEST["nome"]));
	$ret = new ArrayObj();
	if($r->count()>0){
		$ret->offsetSet("success", false);
		$ret->offsetSet("msg", "Nome do estado \"<b>".$_REQUEST["nome"]."</b>\" já existe, por favor, escolha outro.");
		echo $ret->toJson();
		return;
	}
	$r = $sce->insert(array(NULL,$_REQUEST["nome"],$_REQUEST["motivo"],$_REQUEST["dias"],$_REQUEST["data"]));
	if(!$r){
		$ret->offsetSet("success", false);
		$ret->offsetSet("msg", "Não foi possível inserir o registro, contate o Adminitrador.");	
	}
	else{
		$ret->offsetSet("success", true);
		$ret->offsetSet("msg", "Registro editado com sucesso.");
		$ret->offsetSet("id", $r);
	}
	echo $ret->toJson();
}
else if(isset($_REQUEST["removeSysContratoEstado"])){
	//verifica se o usuario tem permissao para administrar o sistema
	checkPermission(20);
	requireSubModule("contrato_estado");
	$sce = new SysContratoEstado();
	$v = explode(",",$_REQUEST["id"]);
	$r = $sce->remove("id",$v," IN ");
	$ret = new ArrayObj();
	if(!$r){
		$ret->offsetSet("success", false);
		$ret->offsetSet("msg", "Não foi possível remover o registro, contate o Adminitrador.");
	}
	else{
		$ret->offsetSet("success", true);
		$ret->offsetSet("msg", "Registro removido com sucesso.");
		$ret->offsetSet("id", $r);
	}
	echo $ret->toJson();
}
//requisicoes do documento
if(isset($_REQUEST["editContratoEstado"])){
	if(strval($_REQUEST["motivo"])=="0")
		$_REQUEST["motivo"] = NULL;
	if(strval($_REQUEST["dias"])=="0")
		$_REQUEST["dias"] = NULL;
	if(strval($_REQUEST["data"])=="0"){
		$_REQUEST["data"] = NULL;
	}
	else{
		$data = DateTime::createFromFormat("d/m/Y", $_REQUEST["data"], new DateTimeZone("America/Sao_Paulo"));
		$_REQUEST["data"] = $data->getTimestamp();
	}
	requireSubModule("contrato_estado");
	$ret = new ArrayObj();
	//para comparacao de campos necessarios
	$sce = new SysContratoEstado();
	$sys = new ArrayObj($sce->select("id",$_REQUEST["sysContratoEstadoID"]));
	$sys = new ArrayObj($sys->offsetGet(0));
	if($sys->count()<=0){
		$ret->offsetSet("success", false);
		$ret->offsetSet("msg", "Não foi possível editar o registro, contate o Adminitrador.");
		echo $ret->toJson();
		return;
	}
	if($sys->offsetGet("data") && $_REQUEST["data"]==NULL){
		$data = DateTime::createFromFormat("d/m/Y", date("d/m/Y"), new DateTimeZone("America/Sao_Paulo"));
		$_REQUEST["data"]=$data->getTimestamp();
	}
	$valores = new ArrayObj();
	$valores->append($_REQUEST["sysContratoEstadoID"]);
	$valores->append($_REQUEST["docID"]);
	$valores->append($_REQUEST["motivo"]);
	$valores->append($_REQUEST["dias"]);
	$valores->append($_REQUEST["data"]);
	$ce = new ContratoEstado($_REQUEST["docID"]);
	$r = $ce->edit($valores->getArrayCopy());

	if(!$r){
		$ret->offsetSet("success", false);
		$ret->offsetSet("msg", "Não foi possível editar o registro, contate o Adminitrador.");
	}
	else{
		$ret->offsetSet("success", true);
		$ret->offsetSet("msg", "Registro editado com sucesso.");
	}
	echo $ret->toJson();
}
?>
