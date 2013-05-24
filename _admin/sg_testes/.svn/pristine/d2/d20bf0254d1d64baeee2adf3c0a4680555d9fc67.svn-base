$(document).ready(function() {
	$("#tableFerias").tablesorter({ sortList: [[0,0]], dateFormat: 'uk' });
	
	$("#feriasForm").submit(function(e) {
		var obrigatorios = $('input.obrigatorio:text[value=""]');
	
		//verificacao dos campos obrigatorios
		if(obrigatorios[0] != undefined){
			alert("Há campos obrigatórios não preenchidos. Por favor, preencha-os e envie novamente.");
			e.preventDefault();
		}
		else {
			var dataIni = $("#dataIni").val();
			var partesData = dataIni.split("/");
			if (partesData.length != 3) {
				alert("A data está com formato inválido.");
				e.preventDefault();
			}
			else if (partesData[0].length != 2 || partesData[1].length != 2 || partesData[2].length != 4) {
				alert("A data está com formato inválido.");
				e.preventDefault();
			}
		
			else if ($("#duracao").val() == '0') {
				alert("Suas férias devem durar mais de zero dias!");
				e.preventDefault();
			}
		}
		
	});
	
	$("#duracao").bind('keydown', function(e) {
		verificaNumero(e);
	});
});

function delFerias(id) {
	if (id == undefined || id == null) {
		alert("Problema com a identificação destas férias. Por favor, informe seu administrador de sistema.");
		return;
	}
	else {
		$.get('sgp.php', {
			acao: 'delFeriasAjax',
			id: id
		}, function(d) {
			//d = eval(d);
			try {
				d = eval(d);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
				}
			}
			
			if(d[0].success == true) {
				$("#link"+id).closest('tr').hide();
			}
			else {
				$("#link"+id).html("Falha ao deletar!");
			}
		});
	}
}