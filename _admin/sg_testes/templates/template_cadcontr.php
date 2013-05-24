<!-- <script type="text/javascript" src="scripts/jquery.autocomplete.js?r={$randNum}"></script>-->
<script type="text/javascript" src="scripts/busca_doc_cad.js?r={$randNum}"></script>
<link rel="stylesheet" type="text/css" href="css/jquery.autocomplete.css">

<center>
<form accept-charset="{$charset}" id="buscaForm" action="" method="post">
{$campos_busca}
<input type="submit" id="buscabut" value="Consultar" />
</form>
</center>

<div class="hid">
<br />
<form accept-charset="{$charset}" id="cadForm" action="sgo.php?acao=cadContr" method="post" enctype="multipart/form-data">
<table width="100%">
<tr>
	<td colspan="2">{$emitente}</td>
</tr>
<tr>
	<td width="50%">{$campos}</td>
	<td width="50%">{$campos_dir}</td>
</tr>
<tr><td colspan="2"></td></tr>
<tr>
	<td colspan="2">
		{$empresa_interface}
	</td>
</tr>
<tr><td colspan="2"></td></tr>
<tr><td colspan="2" align="center"><input type="submit" id="submitCad" value="Enviar"></td></tr>
</table>
</form>
</div>

<div id="cadEmpresa" style="display: none;" title="Nova Empresa">
<center><h3>Nova Empresa</h3></center>
<table width="100%" border="0">
	<tr>
		<td><b>Nome da Empresa:</b> <input id="nome" name="nome" size="50" maxlength="500" /></td>
	</tr>
	<tr>
		<td><b>CNPJ:</b> <input id="cnpj" name="cnpj" size="50" maxlength="18" /></td>
	</tr>
	<tr>
		<td><b>Endereço:</b> <input id="endereco" name="endereco" size="60" /></td>
	</tr>
	<tr>
		<td><b>Complemento:</b><input id="complemento" name="complemento" size="55"></td>
	</tr>
	<tr>
		<td><b>Cidade:</b> <input id="cidade" name="cidade" size="22" " maxlength="100" /> 
		<b>Estado:</b> <input id="estado" name="estado" size="3" maxlength="2" /> 
		<b>CEP:</b> <input id="cep" name="cep" size="10" maxlength="20" /></td>
	</tr>
	<tr>
		<td><b>Telefone:</b> <input id="telefone" name="telefone" size="15" maxlength="12" />
		<b>Fax:</b> <input id="fax" name="fax" size="15" maxlength="12" /></td>
	</tr>
	<tr>
		<td><b>e-mail:</b> <input id="email" name="email" size="30" maxlength="100" /></td>
	</tr>
</table>
</div>