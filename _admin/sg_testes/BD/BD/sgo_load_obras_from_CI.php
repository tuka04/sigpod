<?php
include_once '../includeAll.php';
includeModule('sgo');

$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);

$fp = fopen("obras_cad.csv", "r");

while (($linha = fgets($fp)) !== false) {//leitura da linha
	//separacao dos campos
	$linha = explode(";", $linha);
	
	//tratamento do nome
	$nome = explode("- ", $linha[1], 2);
	if (substr($nome[1],0,1) == ' ')
		$nome[1] = substr($nome[1], 1);
	$linha[1]  = $nome[1];
	
	//tratamento tipo de obra
	if (substr($linha[6],0,1) == ' ')
		$linha[6] = substr($linha[6], 1);
	if (substr($linha[6],-1,1) == ' ')
		$linha[6] = substr($linha[6], 0, -1);
	if($linha[6] == 'OBRA NOVA') {
		$linha[6] = 'nova';
	} elseif ($linha[6] == 'REFORMA') {
		$linha[6] = 'ref';
	} elseif ($linha[6] == 'AMPLIAÇÃO') {
		$linha[6] = 'ampl';
	} elseif ($linha[6] == 'REFORMA E AMPLIAÇÃO') {
		$linha[6] = 'ampl_ref';
	}
	
	//tratamento elevador
	if (strpos($linha[7], "N") !== false) {
		$linha[7] = 0;
	} elseif (strpos($linha[7], "S") !== false) {
		$linha[7] = 1;
	} else {
		$linha[7] == 'NULL';
	} 
	
	//tratamento area
	$linha[5] = str_ireplace(array("."," ",",00","-"), array("","","","NULL"), $linha[5]);
	$linha[5] = str_ireplace(",", ".", $linha[5]);
	
	//tratamento responsavel
	$linha[3] = str_ireplace(" ", "", $linha[3]);
	$responsavel = array(
		"ADRIANA"	=>	6	,
		"ALEXANDRE"	=>	7	,
		"CACÁ"		=>	12	,
		"CPO"		=>	0	,
		"EDILENE"	=>	1	,
		"EDUARDO"	=>	14	,
		"FERNANDO"	=>	20	,
		"FLÁVIA"	=>	22	,
		"GABRIELA"	=>	24	,
		"GISELE"	=>	0	,
		"JAMAL"		=>	2	,
		"MARCOS"	=>	36	,
		"TEODORA"	=>	39	,
		"TOMAZ"		=>	49	,
		"YOSHIO"	=>	5
	);
	$linha[3] = $responsavel[$linha[3]];
	
	//tratamento estado
	$estado = array(
	"AGUARD OS"		=>	1,
	"DESENV PROJ"	=>	2,
	"EXEC OBRA"		=>	3,
	"LICIT OBRA"	=>	4,
	"LICIT PROJ"	=>	5,
	"OBRA CONCL"	=>	6,
	"PLANEJ"		=>	7,
	"PROJ CONCL- AGUARD DECISÃO UNIDADE"	=>	8,
	"PROJ CONCL- ENCAMINH PARA A PREFEIT"	=>	9,
	"SUSPENSO- AGUARD DECISÃO UNIDADE"		=>	10
	);
	$linha[4] = $estado[$linha[4]];
	
	//tratamento unidadeOrg
	if ($linha[2] == 'PREFEITURA')  $linha[2] = '01.14.00.00.00.00';
	elseif ($linha[2] == 'CEPETRO') $linha[2] = '01.02.04.24.00.00';
	elseif ($linha[2] == 'CEPAGRI') $linha[2] = '01.02.04.05.00.00';
	elseif ($linha[2] == 'EDITORA') $linha[2] = '01.01.21.00.00.00';
	elseif ($linha[2] == 'COMVEST') $linha[2] = '01.04.01.00.00.00';
	elseif ($linha[2] == 'HEMOCENTRO') $linha[2] = '32.00.00.00.00.00';
	elseif ($linha[2] == 'REITORIA') $linha[2] = '01.00.00.00.00.00';
	else {
		$res = $bd->query("SELECT id FROM unidades WHERE sigla = '{$linha[2]}'");
		if(count($res)){
			$linha[2] = $res[0]['id'];
		}
	}
	
	//tratamento nome
	$linha[1] = htmlentities($linha[1],ENT_QUOTES);
	
	$bd->query("INSERT INTO obra_cad (id, nome, unOrg, responsavelID, estadoID, dimensao, dimensaoUn, tipo, elevador)
				VALUES ({$linha[0]}, '{$linha[1]}', '{$linha[2]}', {$linha[3]}, {$linha[4]}, {$linha[5]}, 'm', '{$linha[6]}', {$linha[7]} )");
	print_r($linha[1]);
	print('<br />');
}

?>