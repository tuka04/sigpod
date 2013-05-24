<script type="text/javascript" src="scripts/commom.js?r={$randNum}"></script>
<script type="text/javascript" src="scripts/doc_novo.js?r={$randNum}"></script>

<br />
<form accept-charset="{$charset}" id="novoForm" action="sgd.php?acao=salvar&novaJanela={$nova_janela}" method="post" enctype="multipart/form-data">
<table width="100%">
<tr><td width="100%">
	{$emitente}
</td>
</tr>
<tr><td width="100%">
	{$campos}
</td>
</tr>
<tr><td colspan="2"><b>Anexar Arquivo:</b></td></tr>
<tr><td colspan="2">{$anexarArq}</td></tr>
<tr><td colspan="2" id="label_despacho"></td></tr>
<tr><td colspan="2">{$despacho}</td></tr>
<tr><td colspan="2" align="center"><input id="submitNovo" type="submit" value="Enviar"></td></tr>
</table>
</form>