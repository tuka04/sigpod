$(document).ready(function() {
	$("#tableMsgs").tablesorter({ dateFormat: 'uk' });
	
	$("#newMsg").hide();
	
	$("#novaMsgForm").submit(function(e) {
		enviaMsg(e);
	});
});

function abreMsg(id) {
	// exibe a mensagem
	if (id == undefined || id == "")
		return;
		
	$("#m"+id).toggle();
}

function novaMsg() {
	$("#newMsg").attr("title", "Nova mensagem");
	// zerando os valores dos campos, caso haja algum valor neles != do padrão
	$("#replyTo").val(0);
	$("#conteudo").val("");
	$("#assunto").val("");
	// focus para o assunto
	$("#assunto").focus();
	$("#assunto").removeAttr('disabled');
	// limpa os arquivos já selecionados para upload, se houver algum
	$("#fileUpCell").html('<div id="arqs"></div><input type="file" name="arq1" id="arq1" onclick="showInputFile(2)">');
	
	// abre dialog
	$("#newMsg").dialog({
		resizable: true,
		height: 500,
		width: 650,
		modal: true,
		buttons: {
			"Enviar": function() { $("#novaMsgForm").submit(); },
			"Cancelar": function() { $(this).dialog('close'); }
		}
	});
}

function enviaMsg(e) {
	var assunto = $("#assunto", $("#newMsg")).val();

	// verifica se o assunto está preenchido
	if (assunto.length <= 0) {
		alert("Assunto é obrigatório.");
		//return;
		e.preventDefault();
	}
}

function resp(id) {
	$("#newMsg").attr("title", "Responder mensagem");
	$("#assunto", $("#newMsg")).val("RE: " + $("#assunto"+id).html());
	$("#assunto").focus();
	$("#replyTo").val(id);
	// assunto não pode ficar desabilitado: se não o valor não é passado 
	//$("#assunto").attr('disabled','disabled');
	
	$("#newMsg").dialog({
		resizable: true,
		height: 500,
		width: 650,
		modal: true,
		buttons: {
			"Enviar": function() { $("#novaMsgForm").submit(); },
			"Cancelar": function() { $(this).dialog('close'); }
		}
	});
}