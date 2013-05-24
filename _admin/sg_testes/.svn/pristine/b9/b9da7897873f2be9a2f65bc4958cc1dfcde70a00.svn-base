$(document).ready(function(){//funcao que pula para proximo campo ao digitarmos o 3o caracter
	$("#cod1").keyup(function(){
		if($("#cod1").val().length == 3){
			$("#cod2").focus();
		}
	});
	
	$("#cod2").keyup(function(){//funcao que pula para proximo campo ao digitarmos o 2o caracter
		if($("#cod2").val().length == 2){
			$("#cod3").focus();
		}
	});
});

function editarBug(idx){
	var buffer;
	//mantem a descricvao atual e cria uma area de texto para comentar a descricao
	buffer = $("#de"+idx).html();
	$("#de"+idx).html('<span id="dea'+idx+'">'+buffer+'</span><br /><textarea id="dei'+idx+'" cols=50 rows=5"></textarea>');
	//coloca a caixa de selecao para os estado
	buffer = $("#es"+idx).html();
	$("#es"+idx).html('<select id="esi'+idx+'"><option name="receb">Recebido</option><option name="anali">Analisando</option><option name="fim">Finalizado</option></select>');
	$("#esi"+idx).val(buffer);//coloca o estado atual como o selecionado
	//substitui o botao de editar pelo de salvar
	$("#ad"+idx).html('<a href="#l'+idx+'" onclick="salvaBug('+idx+')"><b>Salvar</b></a>');
}

function salvaBug(idx){
	var descrAnt = $("#dea"+idx).html();//le os dados da descricao anterior
	var descr = $("#dei"+idx).val();//le os dados da descricao
	$("#dei"+idx).attr("disabled","disabled");//desabilita o campo de descricao
	var estado = $("#esi"+idx).val();//le os dados de estado
	$("#esi"+idx).attr("disabled","disabled");//desabilita o campo de selecao de estado
	$("#ad"+idx).html('Salvando...');
	
	//faz requisicao em ajax para salvar a edicao
	$.get("report_bug.php?acao=editar&descr="+descr+"&estado="+estado+"&id="+idx,function(d){
		if(d == 'true'){
			//caso a atualizacao seja bem sucedida, retira os campos de edicao e 
			$("#de"+idx).html('<span id="dea'+idx+'">'+descrAnt+"<br /><br />Atualizado por voc&ecirc;:<br />"+descr+'</span>');
			$("#es"+idx).html(estado);
			$("#ad"+idx).html('<b>Editado!</b><br /><a href="#l'+idx+'" onclick="editarBug('+idx+')">Editar novamente</a>');
		
		} else {
			//se houver falha, avisa o acontecido e libera os campos para edicao. cria link para reenviar os dados
			$("#ad"+idx).html('<b>Erro!</b><br /><a href="#l'+idx+'" onclick="salvaBug('+idx+')"><b>Tentar novamente</b></a>');
			/*$("#esi"+idx).attr("disabled","");
			$("#dei"+idx).attr("disabled","");*/
			$("#esi"+idx).removeAttr("disabled");
			$("#dei"+idx).removeAttr("disabled");
		}
	});
}