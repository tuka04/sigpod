<?php
	/**
	 * @version 1.0 20/3/2012 
	 * @package geral
	 * @author Vitor Morelatti
	 * @desc interface usada para módulo de pessoas
	 */

	/**
	 * Template Férias
	 */
	function getInterfaceFerias() {
		global $conf;
		//$html = '<link rel="stylesheet" type="text/css" href="css/jquery.ui.datepicker.css" />';
		$html = "";		
		$html .= '<h2>{$header}</h2>';
		$html .= '<h3>&Uacute;ltimas f&eacute;rias:</h3>';
		
		$html .= '<script type="text/javascript" src="scripts/jquery.tablesorter.min.js?r={$randNum}"></script>';
		$html .= '<script type="text/javascript" src="scripts/jquery-ui-1.8.18.custom.min.js?r={$randNum}"></script>';
		$html .= '<script type="text/javascript" src="scripts/jquery.ui.datepicker-pt-BR.js?r={$randNum}"></script>';
		$html .= '<script type="text/javascript" src="scripts/sgp_ferias.js?r={$randNum}"></script>';
		$html .= '<table id="tableFerias" style="width: 100%;"><thead><th class="c">Data In&iacute;cio</th><th class="c">Data Fim</th><th class="c">Quantidade Dias</th><th class="c">Gerente Imediato</th><th class="c">Tipo de Licen&ccedil;a</th><td class="c"><center><b>A&ccedil;&otilde;es</b></center></td>';
		$html .= '</thead><tbody>{$conteudo}';
		$html .= '</tbody></table><br />';
		
		$html .= '<h3>Novas F&eacute;rias</h3>';
		$html .= '<form accept-charset="'.$conf['charset'].'" id="feriasForm" name="feriasForm" action="sgp.php?acao=salvaFerias" method="post" enctype="multipart/form-data">';
		$html .= '<table style="width: 100%;">';
		$html .= '<tr class="c"><td class="c" width="15%"><b>F&eacute;rias para</b>:</td><td class="c"><input type="hidden" value="{$userID}" name="userID" id="userID">{$usuario}</td></tr>';
		$html .= '<tr class="c"><td class="c" width="15%"><b>Data de In&iacute;cio</b>:</td><td class="c"><input type="text" name="dataIni" id="dataIni" class="obrigatorio" style="width: 10%">*</td></tr>';
		$html .= '<tr class="c"><td class="c" width="15%"><b>Dura&ccedil;&atilde;o</b>: (em dias)</td><td class="c"><input type="text" name="duracao" id="duracao" class="obrigatorio" size="4" style="width: 10%">*</td></tr>';
		$html .= '<tr class="c"><td class="c" width="15%"><b>Gerente Imediato</b>:</td><td class="c"><input type="hidden" name="gerente" id="gerente" value="{$gerenteID}">{$gerenteNome}</td></tr>';
		$html .= '<tr class="c"><td class="c" width="15%"><b>Tipo de Licen&ccedil;a</b>:</td><td class="c">{$selectTipo}</td></tr>';
		$html .= '<tr class="c"><td class="c" colspan="2"><input type="submit" value="Enviar" /></td></tr>';
		$html .= '</table></form>';
		
		$html .= '<script type="text/javascript">$(document).ready(function() { $("#dataIni").datepicker({ dateFormat: "dd/mm/yy", regional: "pt-BR", showOtherMonths: true, selectOtherMonths: true }); $("#dataIni").datepicker($.datepicker.regional[\'pt-BR\']); });</script>';
		return $html;
	}
	
	/**
	 * Template de Gerenciar time
	 */
	function getInterfaceTime() {
		$html = "";
		$html .= '<script type="text/javascript" src="scripts/jquery-ui-1.8.18.custom.min.js?r={$randNum}"></script>';
		
		$html .= '<h2>{$titulo}</h2><br />';
		
		$html .= '{$div_times}';
		
		return $html;
	}
	
	function getDivTimes() {
		$retorno = array('template' => '<div id="{$nomeDiv}" class="times">{$conteudo_times}</div>',
						 'header' => '<h3><a href="javascript:void(0);" id="{$id_header}">{$header}</a></h3>',
						 'conteudo' => '<div>{$divContent}</div>');
		
		return $retorno;
	}
?>