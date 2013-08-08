<?php
/**
 * @deprecated
 */
function showBuscaEmprForm(){
	return '
	<script type="text/javascript" src="scripts/empresa.js"></script>
	<center><b>Nome da Empresa:</b> <input id="nome" type="text"  size=30/> <input type="button" id="buscaEmpr" value="Buscar" /></center>';
}

/**
 * @deprecated
 */
 
 // cria formulário de cadastro de empresa
function showFormCadEmpr() {
	return '
	<b>Cadastrar Empresa:</b><br /><br />
	<form id="cadEmprForm"><table width="100%" border="0">
	<tr><td><b>Nome da Empresa:</b> <input id="nomecad" name="nomecad" size="50"></td></tr>
	<tr><td><b>Endereço:</b> <input id="end" name="end" size="60"></td></tr>
	<tr><td><b>Complemento:</b><input id="compl" name="compl" size="55"></td></tr>
	<tr><td><b>Cidade:</b> <input id="cid" name="cid" size="22"> <b>Estado:</b> <input id="est" name="est" size="2"> <b>CEP:</b> <input id="cep" name="cep" size="10"></td></tr>
	<tr><td><b>Telefone:</b> <input id="tel" name="tel" size="15"> <b>e-mail:</b> <input id="email" name="email" size="30"></td></tr>
	<tr><td><center><input type="submit" value="Cadastrar" /></center></td></tr>
	</table></form>';
}
?>