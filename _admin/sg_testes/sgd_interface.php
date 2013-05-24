<?php

function showAtribuirObraTemplate() {
	global $conf;
	return array(
	'template' => '
		{$obraAtual}<br />
		
		{$table_sugestoes}
		Buscar obra para atribui&ccedil;&atilde;o:<br />
		{$empreendMiniBusca}',
	'table_sugestoes' => '<table style="width: 100%" id="table_sugestoes">
		<tr><td class="c" colspan="2">Sugest&otilde;es de obras baseadas na unidade:</td></tr>
		{$tr_sugestoes}
		</table><input type="hidden" id="guardachuva" value="{$guardachuva}">',
	'sem_obra' => '<span id="obraAtual" style="font-weight: bold"><span id="sem_obras">Este documento n&atilde;o est&aacute; relacionado a nenhuma obra.</span><br /></span>',
	'com_obra' => '<span id="obraAtual" style="font-weight: bold">Este documento est&aacute; relacionado &agrave;(s) obras(s):<br /> </span>',
	'obra_link' => '<a href="javascript:void(0)" onclick="javascript:window.open(\'sgo.php?acao=verObra&amp;obraID={$obra_id}\',\'obra_det\',\'width=\'+screen.width*'.$conf["newWindowWidth"].'+\',height=\'+screen.height*'.$conf["newWindowHeight"].'+\',scrollbars=yes,resizable=yes\').focus()" id="link_obra_{$obra_id}">{$obra_nome}</a> <a href="javascript:void(0)" onclick="javascript:atribObra({$obra_id},1)" id="desfazer_obra_{$obra_id}">[desfazer]<br /></a> ',
	'pai_obra' => '<span id="obraAtual" style="font-weight: bold">Este documento est&aacute; anexado a um outro documento que est&aacute; relacionado ao empreendimento: <a href="javascript:void(0)" onclick="javascript:window.open(\'sgo.php?acao=verObra&amp;obraID={$obra_id}\',\'obra_det\',\'width=\'+screen.width*'.$conf["newWindowWidth"].'+\',height=\'+screen.height*'.$conf["newWindowHeight"].'+\',scrollbars=yes,resizable=yes\').focus()">{$obra_nome}</a></span>'); 
	
}


function showAtribuirEmpreendTemplate() {
	global $conf;
	return array('template' => '
		{$obraAtual}<br /><br />
		
		{$table_sugestoes}
		<br />
		{$empreendMiniBusca}',
	'table_sugestoes' => '<table style="width: 100%" id="table_sugestoes">
		<tr><td class="c" colspan="2">Sugest&otilde;es de empreendimento baseadas na unidade:</td></tr>
		{$tr_sugestoes}
		</table><input type="hidden" id="guardachuva" value="{$guardachuva}">',
	'sem_obra' => '<span id="obraAtual" style="font-weight: bold">Este documento n&atilde;o est&aacute; relacionado a nenhum empreendimento.</span>',
	'com_obra' => '<span id="obraAtual" style="font-weight: bold">Este documento est&aacute; relacionado ao empreendimento: <a href="javascript:void(0)" onclick="javascript:window.open(\'sgo.php?acao=verEmpreend&amp;empreendID={$obra_id}\',\'obra_det\',\'width=\'+screen.width*'.$conf["newWindowWidth"].'+\',height=\'+screen.height*'.$conf["newWindowHeight"].'+\',scrollbars=yes,resizable=yes\').focus()">{$obra_nome}</a> <a href="javascript:void(0)" onclick="javascript:atribEmpreend({$obra_id},\'{$obra_nome}\',1)">[desfazer]</a></span>',
	'obra_header' => '<span id="obraAtual" style="font-weight: bold">Este documento est&aacute; relacionado ao(s) empreendimento(s):',
	'obra_mini' => '<a href="javascript:void(0)" onclick="javascript:window.open(\'sgo.php?acao=verEmpreend&amp;empreendID={$obra_id}\',\'obra_det\',\'width=\'+screen.width*'.$conf["newWindowWidth"].'+\',height=\'+screen.height*'.$conf["newWindowHeight"].'+\',scrollbars=yes,resizable=yes\').focus()">{$obra_nome}</a> <a href="javascript:void(0)" onclick="javascript:atribEmpreend({$obra_id},\'{$obra_nome}\',1)">[desfazer]</a>',
	'pai_obra' => '<span id="obraAtual" style="font-weight: bold">Este documento est&aacute; anexado a um outro documento que est&aacute; relacionado ao empreendimento: <a href="javascript:void(0)" onclick="javascript:window.open(\'sgo.php?acao=verEmpreend&amp;empreendID={$obra_id}\',\'obra_det\',\'width=\'+screen.width*'.$conf["newWindowWidth"].'+\',height=\'+screen.height*'.$conf["newWindowHeight"].'+\',scrollbars=yes,resizable=yes\').focus()">{$obra_nome}</a></span>'); 
}

function showObraMiniBuscaTemplate(){
	global $conf;
	return array('template' => '
	<div id="obraMiniBusca">
		<script type="text/javascript" src="scripts/empreendMiniBusca.js?r={$randNum}"></script>
		<form accept-charset="'.$conf['charset'].'" id="obraMiniBuscaForm" action="javascript:obraMiniBusca()" method="post">
		<input type="text" id="empreendMiniBuscaInput" style="width: 85%" /><input id="obraMiniBuscaSubmit" type="submit" value="OK" />
		</form>
		<div style="width: 100%" id="obraMiniBuscaResults"> </div>
	</div>
	');
}

function showEmpreendMiniBuscaTemplate(){
	global $conf;
	return array('template' => '
	<div id="empreendMiniBusca">
		<script type="text/javascript" src="scripts/empreendMiniBusca.js?r={$randNum}"></script>
		{$texto}<br />
		<form accept-charset="'.$conf['charset'].'" id="empreendMiniBuscaForm" action="javascript:searchMiniBusca(\'empreendMiniBusca\')" method="post">
		<input type="text" id="empreendMiniBuscaInput" style="width: 85%" /><input id="empreendMiniBuscaSubmit" type="submit" value="OK" />
		</form>
		<br /><br />Busca por Processo: 
		<form accept-charset="'.$conf['charset'].'" id="processoMiniBuscaForm" action="javascript:searchMiniBusca(\'processoMiniBusca\')" method="post">
		<input type="text" id="processoMiniBuscaInput" style="width: 85%" /><input id="processoMiniBuscaSubmit" type="submit" value="OK" />
		</form>
		<div style="width: 100%" id="empreendMiniBuscaResults"> </div>
	</div>
	');
}
?>