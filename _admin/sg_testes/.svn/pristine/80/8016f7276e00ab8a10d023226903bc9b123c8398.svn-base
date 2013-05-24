$(document).ready(function(){
	$("#buscaEmpr").click(function(){
		doBuscaEmpr();
	});
	
	$("#cadEmprForm").submit(function(event){
		event.preventDefault();
		doCadEmpr();
	});
	
	$("#novaBusca").click(function(){
		doNovaBuscaEmpr();
	});
});

function doBuscaEmpr(){
	$.get("empresa.php?acao=doBusca&q="+$("#nome").val(),function(d){
		//var data = eval(d);
		try {
			data = eval(d);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
			}
		}
		
		if(data.size == 0){
			$("#resBusca").html("<b>Nenhuma empresa encontrada</b>");
			return;
		}
		
		$("#resBusca").html('<table id="tableRes" width="100%" border="0"><tr><td class="cc"><b>Nome</b></td><td class="cc"><b>Endere&ccedil;o</b></td><td class="cc"><b>A&ccedil;&atilde;o</b></td></tr></table>');
		
		$.each(data,function(i){
			var acao = '<a href="#" onclick="doAddEmpr('+data[i].id+',\''+data[i].nome+'\')">Adicionar</a>';
			$("#tableRes").append('<tr class="c"><td class="cc">'+data[i].nome+'</td><td class="cc">'+data[i].endereco+'</td><td class="cc">'+acao+"</td></tr>");
		});//close each
	});
	
	$("#c1").slideToggle();
	$("#c2").slideToggle();
	$("#c3").slideToggle();
	$("#c4").slideToggle();
}
function doAddEmpr(id,nome){
	window.opener.newEmprLink(id,nome);
	alert("Empresa adicionada ao documento com sucesso.");
	window.close();
}

function doCadEmpr(){
	var nome = $("#nomecad").val();
	var data = $("#nomecad").val()+'|'+$("#end").val()+'|'+$("#compl").val()+'|'+$("#cid").val()+'|'+$("#est").val()+'|'+$("#cep").val()+'|'+$("#tel").val()+'|'+$("#email").val();
	window.opener.newEmprCadLink(data,nome);
	alert("Empresa cadastrada com sucesso e adicionada ao documento.");
	window.close();
}

function doNovaBuscaEmpr(){
	$("#c1").slideToggle();
	$("#c2").slideToggle();
	$("#c3").slideToggle();
	$("#c4").slideToggle();
}