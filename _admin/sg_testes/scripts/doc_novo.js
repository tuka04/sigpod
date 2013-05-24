//var salvo = false;

$(document).ready(function(){
	
	$("#novoForm").submit(function(submit){
		var obrigatorios = $('input.obrigatorio:text[value=""]');
		
		//verificacao dos campos obrigatorios
		if(obrigatorios[0] != undefined){
			alert("Há campos obrigatórios não preenchidos. Por favor, preencha-os e envie novamente.");
			submit.preventDefault();
		} else {
			$("#submitNovo").attr('disabled','disabled');
			$("#submitNovo").val('Salvando... Aguarde');
			setInterval(function(){ reEnableButton("#submitNovo")},30000);
		}
		
	});//closes submit
	
	//$('input:text[style==""]').attr("style", "width: 95%;");
	$("input:not([style])").each(function() {
		if ($(this).attr("type") != "submit" && $(this).attr("type") != "radio" && $(this).attr("type") != "checkbox") {
			$(this).attr("style", "width: 80%");
		}
	});	
	
	$("#tipoProc").change(function(e){
		if($("#tipoProc").val() == 'contrProj' || $("#tipoProc").val() == 'contrObr')
			$("#obraSAP_input").removeAttr('disabled');
	});
	
	$("#obraSAP_input").autocomplete({
		source: function(request, response) { 
			$.get("sgo_busca.php", {
				'tipoBusca' : 'obraMiniBusca',
				'string': htmlentities($("#obraSAP_input").val())
			}, function(data) {
				try {
					data = eval(data);
				} catch(e) {
					if (e instanceof SyntaxError) {
						alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
					}
				}
				
				response($.map( data, function( item ) {
					if(item.obraNome != null && item.obraID != null)
	                    return {
	                        label: html_entity_decode(item.obraNome),
	                        vvalue: item.obraID
	                    }
                }));
			});
		},
		minLength: 3,
		autoFocus: true,
		select: function(event, ui){
			if($("#obraSAP_"+ui.item.vvalue).length == 0){
				if($("#obraSAP").val() == '')
					var obras = [];
				else
					var obras = JSON.parse($("#obraSAP").val());
				obras.push({'obraID' : ui.item.vvalue, 'obraNome' : ui.item.label});
				$("#obraSAP").val(JSON.stringify(obras));
				$("#obraSAP_display").append('<span id="obraSAP_'+ui.item.vvalue+'">'+ui.item.label+'<a href="javascript:obraSAP_remover('+ui.item.vvalue+');">[excluir]<br />');
			}
			
		},
		close: function(){
				$("#obraSAP_input").val('');
			}
	});
	
	/*$("#link_preview").closest("form").submit(function(e) {
		if ($("#link_preview").length > 0) {
			salvo = true;
		}
	});
	
	$(window).on('beforeunload', function() {
		if (salvo == false)
			return 'Você ainda não salvou este documento. Caso você queira salvar o documento, clique em "Permanecer na página" e depois em "Enviar".';
	});*/
});//close document.ready

function obraSAP_remover(obraID){
	$("#obraSAP_"+obraID).remove();
	
	var obras = JSON.parse($("#obraSAP").val());
	for(var i in obras){
		if(obras[i]['obraID'] == obraID){
			obras.splice(i,1);
		}
	}
	$("#obraSAP").val(JSON.stringify(obras));
}
