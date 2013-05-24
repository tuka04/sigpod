<?php
function quadroNovidades_template() {
	return array(
		'quadro' => '
		<script type="text/javascript" src="scripts/quadroNovidades.js?r={$randNum}"></script>
		<div id="news" style="border: 1px solid #BE1010; padding: 5px; margin: 5px 10% 5px 10%;">
		<center><span class="header">Novidades do SiGPOD!</span></center>
		{$novidades}
		
		<center><input id="closeNovidadesBtn" type="button" name="closeNovidades" value="Fechar" onclick="javascript:closeNovidades()" /><br />
		<input id="closeNovidadesCbx" type="checkbox" checked="checked"/> Somente mostrar as novidades na pr&oacute;xima atualiza&ccedil;&atilde;o.</center>
		</div>',
		'linha_novidade' => '<b>{$data}</b> - {$texto}</br>'
	);
}
?>