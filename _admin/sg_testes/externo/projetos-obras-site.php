<?php
	include_once '../includeAll.php';
	includeModule('sgo');
	
	//inicialização de variaveis
	$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);
	
	//configurações da pagina HTML
	$html = showExternalTemplate();
	$tipos_input = '';
	
	//cria dinamicamente as checkboxes para os tipos
	$tipos = $bd->query("SELECT abrv, nome FROM label_obra_tipo");
	foreach ($tipos as $t) {
		$tipos_input .= geraInput("tipo_".$t['abrv'], array('name' => "tipo_".$t['abrv'], 'type' => 'checkbox', 'value' => $t['abrv'], "class" => 'tipo'))." ".$t['nome']."<br />";
	}
	$html = str_ireplace('{$tipo_checkbox}', $tipos_input, $html);
	
	//cria dinamicamente as checkboxes das caracteristicas
	$caract = $bd->query("SELECT abrv, nome FROM label_obra_caract");
	$caract_input = '';
	
	foreach ($caract as $c) {
		$caract_input .= geraInput("carct_".$c['abrv'], array('name' => "caract_".$c['abrv'], 'type' => 'checkbox', 'value' => $c['abrv'], "class" => 'caract'))." ".$c['nome']."<br />";
	}
	$html = str_ireplace('{$caract_checkbox}', $caract_input, $html);
	
	//seleciona as dimensoes para completar os valores de busca
	$area = $bd->query("SELECT dimensao FROM obra_obra WHERE dimensao IS NOT NULL GROUP BY dimensao ORDER BY dimensao");	
	if(count($area) < 2){
		$a[1] = 0;
		$a[2] = 0;
		$a[3] = 0;
		$a[4] = 0;		
	} else {
		$a[1] = $area[round(count($area)/3)]['dimensao'];
		$a[2] = $area[round(count($area)/3)+1]['dimensao'];
		$a[3] = $area[round(count($area)/3)*2]['dimensao'];
		$a[4] = $area[round(count($area)/3)*2+1]['dimensao'];
	}
	//completa os valores de busca
	$html = str_ireplace(array('{$a1}', '{$a2}', '{$a3}', '{$a4}'), $a, $html);
	
	print $html;
	
	/**
	 * cria um iframe para a visualizacao do mapa de BUSCA
	 */
	function showExternalTemplate(){
		return '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-type" content="text/html;charset=iso-8859-15" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Cache-Control" content="no-cache" />
	<meta http-equiv="Pragma-directive" content="no-cache" />
	<meta http-equiv="Cache-Directive" content="no-cache" />
	<meta http-equiv="Expires" content="1" />
	<script type="text/javascript" src="projetos-obras-site_scripts/jquery.js"></script>
	<script type="text/javascript" src="projetos-obras-site_scripts/jquery.autocomplete.js"></script>
	<link rel="stylesheet" type="text/css" href="projetos-obras-site.jquery.autocomplete.css" />
	<script type="text/javascript" src="projetos-obras-site_scripts/busca.js"></script>
	<script type="text/javascript" src="projetos-obras-site_scripts/commom.js"></script>
	<link rel="stylesheet" type="text/css" href="projetos-obras-site.css" />
	<title>Projetos e Obras</title>
</head>
<body>
  <div id="container">
    <div id="header">
      <h3> Projetos e Obras </h3>
    </div>
    <div id="conteudo">
	  <img src="projetos-obras-site_img/bg_img.png" style="z-index:-1; position:fixed; top: 0; right: 0; min-width:100%; min-height:100%; height:auto;" />
      <div class="boxRight">

        <div class="boxCont" id="c1">
          <a href="http://www.cpo.unicamp.br/">&larr; Voltar para o site da CPO.</a>
          <table id="busca" style="width: 100%;">
			<tr>
				<td style="max-width: 50%;">
					<span class="header">Filtrar por:</span>
					<form id="buscaObraForm">
						<table style="width: 100%">
							<tr class="c"><td class="c" colspan="2">
								<b>Campus:</b> (clique sobre o nome para mudar o mapa)<br />
								<table style="width: 100%">
									<tr>
										<td colspan="2" style="width: 25%; text-align: center;">
											Campinas<br />
										</td>
										<td colspan="2" style="width: 25%; text-align: center;">
											Paul&iacute;nia<br />
										</td>
										<td colspan="2" style="width: 25%; text-align: center;">
											Limeira<br />
										</td>
										<td colspan="2" style="width: 25%; text-align: center;">
											Piracicaba<br />
										</td>
									</tr>
									<tr>
										<td style="width: 10%; text-align: right;">
											<input type="checkbox" class="campus" id="campus_unicamp" name="campus_unicamp" value="unicamp" />
										</td>
										<td style="width: 15%; text-align: left;">
											<a href="javascript:void(0)" onclick="javascript:document.getElementById(\'gmapsRes\').contentWindow.focusCampus(\'unicamp\')">Unicamp</a><br />
										</td>
										<td style="width: 10%; text-align: right;">
											<input type="checkbox" class="campus" id="campus_cpqba" name="campus_cpqba" value="cpqba" />
										</td>
										<td style="width: 15%; text-align: left;">
											<a href="javascript:void(0)" onclick="javascript:document.getElementById(\'gmapsRes\').contentWindow.focusCampus(\'cpqba\')">CPQBA</a>
										</td>
										<td style="width: 10%; text-align: right;">
											<input type="checkbox" class="campus" id="campus_lim1" name="campus_lim1" value="lim1" />
										</td>
										<td style="width: 15%; text-align: left;">
											<a href="javascript:void(0)" onclick="javascript:document.getElementById(\'gmapsRes\').contentWindow.focusCampus(\'lim1\')">Campus 1</a><br />
										</td>
										<td style="width: 10%; text-align: right;">
											<input type="checkbox" class="campus" id="campus_fop" name="campus_fop" value="fop" />
										</td>
										<td style="width: 15%; text-align: left;">
											<a href="javascript:void(0)" onclick="javascript:document.getElementById(\'gmapsRes\').contentWindow.focusCampus(\'fop\')">FOP</a><br />
										</td>
									</tr>
									<tr>
										<td style="text-align: right;">
											<input type="checkbox" class="campus" id="campus_cotuca" name="campus_cotuca" value="cotuca" />
										</td>
										<td style="text-align: left;">
											<a href="javascript:void(0)" onclick="javascript:document.getElementById(\'gmapsRes\').contentWindow.focusCampus(\'cotuca\')">Cotuca</a><br />
										</td>
										<td style="text-align: right;"></td>
										<td style="text-align: left;"></td>
										<td style="text-align: right;">
											<input type="checkbox" class="campus" id="campus_fca" name="campus_fca" value="fca" />
										</td>
										<td style="text-align: left;">
											<a href="javascript:void(0)" onclick="javascript:document.getElementById(\'gmapsRes\').contentWindow.focusCampus(\'fca\')">FCA</a><br />
										</td>
										<td style="text-align: right;">
											<input type="checkbox" class="campus" id="campus_pircentro" name="campus_pircentro" value="pircentro" />
										</td>
										<td style="text-align: left;">
											<a href="javascript:void(0)" onclick="javascript:document.getElementById(\'gmapsRes\').contentWindow.focusCampus(\'pircentro\')">Centro</a><br />
										</td>
									</tr>
								</table>
							</td></tr>
							<tr class="c"><td class="c" colspan="2">
								<b>Nome da obra ou empreendimento: </b>
								<input type="text" name="nome" id="nome" size="50" maxlength="200" autocomplete="off" />
							</td></tr>
							<tr class="c"><td class="c" colspan="2">
								<b>Unidade/&Oacute;rg&atilde;o solicitante: </b>
								<input type="text" name="unOrg" id="unOrg" size="50" maxlength="200" />
							</td></tr>
							<tr class="c"><td class="c">
								<b>Caracter&iacute;stica: </b><br />
								{$caract_checkbox}
							</td></tr>
							<tr class="c"><td class="c">
								<b>Tipo: </b><br />
								{$tipo_checkbox}
							</td>
							</tr>
							<tr class="c"><td class="c" style="width:50%">
								<b>&Aacute;rea: </b> <br />
								<input type="checkbox" class="area" name="area1" id="area1" value="1" /> At&eacute; {$a1} m<sup>2</sup><br />
								<input type="checkbox" class="area" name="area2" id="area2" value="2" /> De {$a2} a {$a3} m<sup>2</sup><br />
								<input type="checkbox" class="area" name="area3" id="area3" value="3" /> Acima de {$a4} m<sup>2</sup><br />
								<input type="checkbox" class="area" name="area0" id="area0" value="0" /> N&atilde;o informado.
								<input type="hidden" id="a1" value="{$a1}" />
								<input type="hidden" id="a2" value="{$a2}" />
								<input type="hidden" id="a3" value="{$a3}" />
								<input type="hidden" id="a4" value="{$a4}" />
							</td>
							</tr>
							
							
							<tr class="c">
							<td class="c" style="text-align: center;" colspan="2">
								<input type="submit" value="Filtrar" />
							</td></tr>
						</table>
					</form>
				</td>
				<td style="width: 50%; min-width:550px;">
					Mostrar: <a href="javascript:void(0)" id="show_map" style="text-decoration: underline;" onclick="showMap()">mapa</a> <a href="javascript:void(0)" id="show_list" onclick="showList()">lista</a><br />
							 <iframe id="gmapsRes" src="projetos-obras-site_map_externo.php" scrolling="no" style="height: 95%; width: 100%; min-height: 500px; padding: 0; margin: 0; border: 0;  overflow-y: hidden; "></iframe>
					<div id="listaRes" style="display:none; height:550px; overflow-y:auto;">
					</div>
				</td>
			</tr>
		</table>
		<a href="http://www.cpo.unicamp.br/">&larr; Voltar para o site da CPO.</a>
        </div>
      </div>
    </div>
    <div id="footer">
               
    </div>
  </div>

  
</body>
</html>
		
		';
	}
?>

