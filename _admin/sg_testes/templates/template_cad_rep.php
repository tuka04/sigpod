<script type="text/javascript" src="scripts/commom.js?r={$randNum}"></script>
<script type="text/javascript" src="scripts/busca_doc_cad.js?r={$randNum}"></script>

<center>
<form accept-charset="{$charset}" id="buscaForm" action="" method="post">
{$campos_busca}
<input type="submit" id="buscabut" value="Consultar" />
</form>
</center>

<div class="hid">
<br />
<form accept-charset="{$charset}" id="cadForm" action="sgd.php?acao=salvar&novaJanela={$nova_janela}" method="post" enctype="multipart/form-data">
<table width="100%">
<tr><td>{$emitente}</td></tr>
<tr><td>{$campos}</td></tr>
<tr><td colspan="2" align="center"><input type="submit" id="submitCad" value="Cadastrar"></td></tr>
</table>
</form>
</div>