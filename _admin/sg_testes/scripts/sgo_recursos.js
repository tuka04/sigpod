$(document).ready(function(){
	setDatepicker(".datepicker");
});

function setDatepicker(selector){
	$(selector).datepicker({ dateFormat: "dd/mm/yy" , minDate: 0, maxDate: "31/12/2037", yearRange: "2012:2027", regional: "pt-BR"});
}

function rec_edit(recID) {
	var temp; //colocar impit no interface e controlar hide/show aqui
	temp = $("#montante_"+recID).html();
	$("#montante_"+recID).html('<input type="text" size="8" id="montante_edit_'+recID+'" value="'+temp+'" />');
	temp = $("#origem_"+recID).html();
	$("#origem_"+recID).html('<input type="text" size="20" id="origem_edit_'+recID+'" value="'+temp+'" />');
	temp = $("#prazo_"+recID).html();
	$("#prazo_"+recID).html('<input type="text" size="10" id="prazo_edit_'+recID+'" value="'+temp+'" />');
	setDatepicker("#prazo_edit_"+recID);
	temp = $("#justif_"+recID).html();
	$("#justif_"+recID).html('<textarea id="justif_edit_'+recID+'" cols="75" rows="5">'+temp+'</textarea>');
	
	$("#editar_"+recID).html('[salvar]');
	$("#editar_"+recID).attr('onclick','salvaRec(0,'+recID+')');
}

function novoRecurso() {
	//esconder a linha de 'sem recursos'
	if($("#noRecRow").length > 0)
		$("#noRecRow").hide();
	
	$("#novoRecRow").show();
}

function salvaRec(empreendID, recID) {
	if(empreendID == recID && recID == 0) {
		alert("Erro ao salvar recurso: empreendID == recID == 0");
		return;
	}
	
	if(empreendID == 0){ //salvar edicao em um recurso
		var rec = {
			'valor': $("#montante_edit_"+recID).val(),
			'prazo': $("#prazo_edit_"+recID).val(),
			'origem': escape($("#origem_edit_"+recID).val()),
			'justif' : escape($("#justif_edit_"+recID).val())
		};
	}
	
	if(recID == 0){ //salvar novo recurso
		if($("#novoRec_montante").val() == '') {
			alert("O novo recurso deve ter um montante!");
			return;
		}
	
		var rec = {
			'valor': $("#novoRec_montante").val(),
			'prazo': $("#novoRec_prazo").val(),
			'origem': escape($("#novoRec_origem").val()),
			'justif' : escape($("#novoRec_justif").val())
		};
	}

	$.get("sgo.php",
		{
			'acao': "salvaRec",		
			'recID': recID,
			'empreendID': empreendID,
			'obraID': 0,
			'rec_justif': rec.justif,
			'rec_valor' : rec.valor,
			'rec_prazo' : rec.prazo,
			'rec_origem': rec.origem
		},
		function(d) {
			//var returnData = eval(d);
			try {
				returnData = eval(d);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
				}
			}
			
			if(returnData[0].success == true) {
				if(recID == 0){
					//esconder e limpar os campos
					$("#novoRec_montante").val('');
					$("#novoRec_prazo").val('');
					$("#novoRec_origem").val('');
					$("#novoRec_justif").val('');
					$("#novoRecRow").hide();
					
					$("#recTable").append(returnData[0].html);
					
					//inserir a linha que acabou de ser gravada na tabela
				} else {
					$("#montante_"+recID).html(returnData[0].valor);
					$("#origem_"+recID).html(unescape(rec.origem));
					$("#prazo_"+recID).html(rec.prazo);
					$("#justif_"+recID).html(unescape(rec.justif));
					$("#responsavel_"+recID).html(returnData[0].newResponsavelName);
					$("#dataModif_"+recID).html(returnData[0].lastModDate);
					
					$("#editar_"+recID).html('[salvo!]');
					$("#editar_"+recID).attr('onclick','rec_edit('+recID+')');
				} 
				
				
				
			} else {
				alert(returnData[0].errorFeedback);
			}
		}
	);
}