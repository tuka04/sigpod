$(document).ready(function(){
	$(".tipoDoc").change(function(e){
		if(e.target.checked == true) {
			$('#'+e.target.name+'_numero').show();
			$('#'+e.target.name+'_ano').show();
			$('#'+e.target.name+'_assunto').show();
			$('#'+e.target.name+'_obs').show();
		} else {
			$('#'+e.target.name+'_numero').hide();
			$('#'+e.target.name+'_ano').hide();
			$('#'+e.target.name+'_assunto').hide();
			$('#'+e.target.name+'_obs').hide();
		}
	});
	
	
	$("#cadForm").submit(function(e){
		var res = procura_dgen(e);
		if(!res.canSubmit){
			displayError(res);
			e.preventDefault();
			$("#submitCad").removeAttr("disabled");
			$("#submitCad").val("Enviar");
		}
			
	});
	
	$("#numero_rep").focus();
	
});

function procura_dgen(e){
	var i = 1;
	var res = {'canSubmit' : true, 'issues' : []};
	
	if($(".tipoDoc:checked").length == 0 && $("#doc"+i+"_nome").attr('checked') != 'checked') {//nenhum documento checkado
		res.canSubmit = false;
		res.issues.push({'id': 0, 'issue' : 'semdoc'});
		return res;
	}
	
	while($("#doc"+i+"_nome").length > 0){
		if($("#doc"+i+"_cb:checked").length == 0){
			i++;
			continue;
		}
		
		if($("#doc"+i+"_numero").val() == '' && $("#doc"+i+"_assunto").val() != '' && $("#doc"+i+"_cb:checked").length > 0){
			i++;
			continue;
		}
		
		if($("#doc"+i+"_assunto").val() == '') {
			res.canSubmit = false;
			res.issues.push({'id': i, 'issue' : 'assunto'});
			i++;
			continue;
		}

		//alert($("#doc"+i+"_cb").attr('checked'))
		if($("#doc"+i+"_cb:checked").length > 0) {
			$.ajax({url: "sgd_busca.php?tipoBusca=repBusca",
				data: {
				'numero' : $("#doc"+i+"_numero").val(), 
				'unOrg' : $("#empresa_rep").val(),
				'tipo' : $("#doc"+i+"_nome").html(), 
				'ano':$("#doc"+i+"_ano").val()
				},
				async : false,
				type : 'post'
			}).done( function(d){
				try {
					var data = eval(d);
				} catch(e) {
					if (e instanceof SyntaxError) {
						alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
					}
				}
				
				if (data[0].error == true) {
					alert("Erro encontrado. Parâmetros insuficientes.");
					e.preventDefault();
				}
				
				if(data[0].duplicata == true) {
					res.canSubmit = false;
					res.issues.push({'id': i, 'issue' : 'duplicata'});
				}
			});
		}
		
		i++;
	}
	
	return res;
}

function preenche_rep(dados){
	if(!dados.processo || !dados.obras || !dados.empresa) {
		return {'canSubmit' : false, 'issues' : [{'id' : 0, 'issue' : 'invalidcontrato'}]};
	}
	$("#num_proc_rep").val(html_entity_decode(dados.processo));
	$("#obra_rep").val(html_entity_decode(dados.obras));
	$("#empresa_rep").val(html_entity_decode(dados.empresa));
	return {'canSubmit' : true, 'issues' : []};
}

function displayError(ret) {
	 var errorLabel = {
		'assunto' : 'O documento {$docName} n&atilde;o tem assunto. Por favor, complete este campo.',
		'duplicata' : 'O documento {$docName} {$docNum}/{$docAno} j&aacute; est&aacute; cadastrado.',
		'invalidcontrato' : 'O numero do contrato &eacute; inv&aacute;lido. Verifique o n&uacute;mero digitado e tente novamente',
		'semdoc' : 'Pelo menos um documento deve ser cadastrado.'
	}
	
	var errorHTML = '';
	
	var tr = $(".doc_tr").filter(function(){
		return $(this).css('background-color') == '#FFDDDD';
	})
	
	$.each(tr, function(i,element){
		$("#"+element.id).css('background-color','');
	});
	
	$.each(ret.issues,function(){
		errorHTML += errorLabel[this.issue]+'<br />';
		
		if(errorHTML.indexOf('{$docName}') && this.id > 0)
			errorHTML = errorHTML.replace('{$docName}',$("#doc"+this.id+"_nome").html());
		
		if(errorHTML.indexOf('{$docNum}') && this.id > 0)
			errorHTML = errorHTML.replace('{$docNum}',$("#doc"+this.id+"_numero").val());
		
		if(errorHTML.indexOf('{$docAno}') && this.id > 0)
			errorHTML = errorHTML.replace('{$docAno}',$("#doc"+this.id+"_ano").val());
		
		if(this.id > 0)
			$("#doc"+this.id+"_tr").css('background-color','#FFDDDD');
	});
	
	$("#cadRepError").html(errorHTML);
	$("#cadRepError").dialog({
		title: "Erro ao cadastrar documentos",
		modal: true,
		buttons: {"OK" : function(){
			$(this).dialog("close");
	}}
		
	})
	
}
