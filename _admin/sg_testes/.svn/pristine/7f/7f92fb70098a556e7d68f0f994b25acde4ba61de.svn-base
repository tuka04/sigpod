$(document).ready(function() {
	// ao clicar no botão de adicionar, remove as opções do select multi-line de usuarios fora e adiciona ao 
	// select de equipe
	$("#adicionar").click(function() {
		var users = $("#usuarios :checked");
		
		if (users.length <= 0) return;
		
		$.each(users, function() {
			$("#equipe").append('<option value="'+$(this).val()+'" name="'+$(this).attr('name')+'">'+$(this).attr('name')+'</option>');
			
			$(this).remove();
		});
		
	});
	
	// remove usuarios da equipe e adiciona as opções no select de usuarios fora
	$("#remover").click(function() {
		var users = $("#equipe :checked");
		
		if (users.length <= 0) return;
		
		$.each(users, function() {
			$("#usuarios").append('<option value="'+$(this).val()+'" name="'+$(this).attr('name')+'">'+$(this).attr('name')+'</option>');
			
			$(this).remove();
		});
		
	});
	
	
	// trata o submit e retorna feedback
	$("#editEquipe").submit(function(e) {
		var urlVars = getUrlVars();
		var users = $("#equipe option");
		
		var equipe = '';
		
		if (users.length <= 0) equipe = '0';
		
		$.each(users, function() {
			if (equipe.length > 0)
				equipe += ',';
			
			equipe += $(this).val();
		});
		
		$.post('sgo.php?acao=salvaEquipe&empreendID='+urlVars['empreendID'], {
			equipe: equipe
		}, function(d) {
			//d = eval(d);
			try {
				d = eval(d);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message + " Retorno: " + d);
				}
			}
			
			if (d[0].success == true) {
				$("#equipeAlert").attr('title', 'Sucesso!');
				$("#equipeAlert").html('<center>Equipe alterada com sucesso!</center>');
				$("#equipeAlert").dialog({ 
					resizable: false,
					height: 150,
					width: 250,
					modal: true,
					buttons: {
						"OK": function() { $(this).dialog('close'); window.location.reload(); }
					}
				});
				
			}
			else {
				$("#equipeAlert").attr('title', 'Erro');
				$("#equipeAlert").html('<center>Falha ao tentar alterar a equipe. Por favor, tente novamente.</center>');
				$("#equipeAlert").dialog({ 
					resizable: false,
					height: 150,
					width: 250,
					modal: true,
					buttons: {
						"OK": function() { $(this).dialog('close'); window.location.reload(); }
					}
				});
			}
			
		});
		
		e.preventDefault();
	});
	
});