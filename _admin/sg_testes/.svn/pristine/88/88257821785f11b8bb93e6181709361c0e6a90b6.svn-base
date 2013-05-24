
/*=============================================
  FUNCOES PARA GERENCIAMENTO DE FORMULARIOS
=============================================*/
function closeDialog(){//fecha todos os dialogos
	$(".dialog").hide();
}

function showDialog(id){//abre um dialogo
	$("#"+id).show();
}

function showInputFile(i){//mostra outro campo para anexo de arquivo
	$("#arq"+(i-1)).attr("onclick",";");
	if(i <= 20)
		$("#fileUpCell").append('<br /><input type="file" id="arq'+i+'" name="arq'+i+'" onclick="showInputFile('+(i+1)+')" />');
	else
		alert("O limite mбximo de arquivos a serem anexados por vez й de 20. Para anexar mais, salve o documento e utilize a funзгo Anexar Arqivo");
}


function html_entity_decode(str) {
	var ta=document.createElement("textarea");
	ta.innerHTML=str.replace(/</g,"&lt;").replace(/>/g,"&gt;");
	return ta.value;
}

function convertFromHTML(a){
	a = a + '';
	a = a.replace("&aacute;","б");
	a = a.replace("&atilde;","г");
	a = a.replace("&agrave;","а");
	a = a.replace("&acirc;","в");
	a = a.replace("&eacute;","й");
	a = a.replace("&egrave;","и");
	a = a.replace("&ecirc;","к");
	a = a.replace("&iacute;","н");
	a = a.replace("&igrave;","м");
	a = a.replace("&oacute;","у");
	a = a.replace("&ograve;","т");
	a = a.replace("&ocirc;","ф");
	a = a.replace("&otilde;","х");
	a = a.replace("&uacute;","ъ");
	a = a.replace("&uuml;","ь");
	a = a.replace("&ccedil;","з");
	a = a.replace("&Aacute;;","Б");
	a = a.replace("&Atilde;","Г");
	a = a.replace("&Agrave;","А");
	a = a.replace("&Acirc;","В");
	a = a.replace("&Eacute;","Й");
	a = a.replace("&Egrave;","И");
	a = a.replace("&Ecirc;","К");
	a = a.replace("&Iacute;","Н");
	a = a.replace("&Igrave;","М");
	a = a.replace("&Oacute;","У");
	a = a.replace("&Ograve;","Т");
	a = a.replace("&Ocirc;","Ф");
	a = a.replace("&Otilde;","Х");
	a = a.replace("&Uacute;","Ъ");
	a = a.replace("&Uuml;","Ь");
	a = a.replace("&Ccedil;","З");
	return a;
}

/*function verificaPadrao(valor,campo){
	var expr;
	if(campo == "numero_pr"){
		expr = /01 P-[0-9]{5}-[0-9]{4}/;
	}
	return expr.test(valor);
}*/

/*=================================
FUNCOES DE GERENCIAMENTO DE JANELAS
==================================*/

function getUrlVars(){
	// Get variaveis por JS;
	var vars = [], pedaco;
	if(window.location.href.indexOf('#') == -1){
		var inteira = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
	} else{
		var inteira = window.location.href.slice(window.location.href.indexOf('?') + 1, window.location.href.indexOf('#')).split('&');
	}
	for (var i = 0; i < inteira.length; i++){
		pedaco = inteira[i].split('=');
		pedaco[1] = unescape(pedaco[1]);
		vars.push(pedaco[0]);
		vars[pedaco[0]] = pedaco[1];
	}
	return vars;
}

/*=========================================
FUNCOES PARA GERENCIAMENTO DE TABELAS/LINKS
=========================================*/

function showDesp(i){
	$('#desp'+i).slideDown();
}

function newEmprCadLink(data,nome){
	$.get('empresa.php?acao=cad&data='+data,function(suc){
		if(suc == '0'){
			alert("Erro ao cadastrar empresa");
		}else{
			id = suc;
			newEmprLink(id,nome);
		}
	});

}

function newEmprLink(id,nome){//monta link para empresa anexa
	if($("#emprAnexas").val().length == 0)
		$("#empresa").html("");
	$("#empresa").append('&nbsp;&nbsp;'+newWinLink('empresa.php?acao=ver&id='+id,'empresa'+id,950,650,nome)+'&nbsp;&nbsp;,');
	$("#emprAnexas").val($("#emprAnexas").val()+id+',');
}

function newDocLink(id,nome,target,sep){//monta um link de documento anexo
	if($("#"+target).val().length == 0)
		$("#docs").html("");
	$("#"+target+"Nomes").append(newWinLink('sgd.php?acao=ver&docID='+id,'detalhe'+id,950,650,nome)+newDelBut(id,target,'<br>')+'<br>');
	if($("#"+target).val().length == 0){
		$("#"+target).val($("#"+target).val()+id);
	} else {
		$("#"+target).val($("#"+target).val()+','+id);
	}

	if(target == 'ofir' || target == 'saa') {
		validateDoc(target);
	}
}

function newDelBut(id,target,sep){//cria o link para remocao do documento
	return ' <a href="javascript:void(0)" class="retirar" onclick="delDoc('+id+',\''+target+'\',\''+sep+'\')">[Retirar]</a>';
}

function delDoc(id,target,sep){//faz a remocao do documento
	var ids = $("#"+target).val().split(',');
	var nomes = $("#"+target+"Nomes").html().split(sep);
	var i = 0, idsNovos = '', nomesNovos = '';
	for(i = 0 ; i < ids.length ; i++){//varre o array de documentos e ignora o elemento a ser excluido
		if(ids[i] != id){
			idsNovos += ids[i] + ',';
			nomesNovos += nomes[i] + sep;
		}
	}
	$("#"+target).val(idsNovos.slice(0,idsNovos.length - 1));
	$("#"+target+"Nomes").html(nomesNovos);
}

function newWinLink(url,name,w,h,label){//monta o link para abrir em nova janela
	return '<a href="javascript:void(0)" id="'+name+'" onclick="window.open(\''+url+'\',\''+name+'\',\'width='+w+',height='+h+',scrollbars=yes,resizable=yes\')">'+label+'</a>';
}














function escolherDoc(target){
	window.open('sgd.php?acao=busca_mini&onclick=adicionarCampo&max=1&target='+target,'addDoc','width=750,height=550,scrollbars=yes,resizable=yes');
	$('#'+target+'Nomes').html('Documento Selecionado:');
	$('#'+target).val('');
	if(target == 'ofir') validateObra();
}

function newDocInNewWindow(tipo,target,acao){
	window.open('sgd.php?acao='+acao+'_mini&tipoDoc='+tipo+'&targetInput='+target+'&desp=off', 'cadMini', 'width=750,height=550,scrollbars=yes,resizable=yes');
	$('#'+target+'Nomes').html('Documento Selecionado:');
	$('#'+target).val('');
}

function updateDocID(target,value){
	$("#"+target+"Names").html("Documento "+value+" selecionado.");
	$("#"+target).val(value);
}