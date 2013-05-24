$(document).ready(function(){
	$("#c3").hide();
	$("#c4").hide();
	$("#c5").hide();
	$("#c6").hide();
	$("#c7").hide();
	$("#c8").hide();
	$("#c9").hide();
	$("#c10").hide();
	$("#c11").hide();
	
	$("#resumo_link").click(function(){
		showSecaoObra(1);
	});
	
	$("#docs_link").click(function(){
		showSecaoObra(2);
	});
	
	$("#recursos_link").click(function(){
		showSecaoObra(3);
	});
	
	$("#livro_link").click(function(){
		showSecaoObra(4);
	});
	
	$("#questoes_link").click(function(){
		showSecaoObra(5);
	});
	
	$("#contratos_link").click(function(){
		showSecaoObra(6);
	});
	
	$("#medicoes_link").click(function(){
		showSecaoObra(7);
	});
	
	$("#mensagens_link").click(function(){
		showSecaoObra(8);
	});
	
	$("#historico_link").click(function() {
		showSecaoObra(9);
	});
	
	/*$("#resumo_link").click(function(){
		showSecaoObra(1);
	});
	
	$("#detalhes_link").click(function(){
		showSecaoObra(2);
	});
	
	$("#etapas_link").click(function(){
		showSecaoObra(4);
	});
	
	$("#recursos_link").click(function(){
		showSecaoObra(3);
	});
	
	$("#historico_link").click(function(){
		showSecaoObra(5);
	})*/
	
});

function showSecaoObra(sec) {
	$("#c2").hide();
	$("#c3").hide();
	$("#c4").hide();
	$("#c5").hide();
	$("#c6").hide();
	$("#c7").hide();
	$("#c8").hide();
	$("#c9").hide();
	$("#c10").hide();
	$("#c11").hide();
	
	if(sec == 1) {
		$("#c2").slideDown();
	} else if(sec == 2) {
		$("#c3").slideDown();
	} else if(sec == 3) {
		$("#c4").slideDown();
	} else if(sec == 4) {
		$("#c5").slideDown();
	}else if(sec == 5) {
		$("#c6").slideDown();
	}else if(sec == 6) {
		$("#c7").slideDown();
	}else if(sec == 7) {
		$("#c8").slideDown();
	}else if(sec == 8) {
		$("#c9").slideDown();
	}else if(sec == 9) {
		$("#c11").slideDown();
	}
}

function addEtapa(){
	
	$("#addEtapaTable").show();
	$(".addEtapaLink").hide();
	showSecaoObra(4);
}

function addRecurso(primeiro){
	$("#noRecRow").hide();
	$("#addRecTable").show();
	$(".addRecLink").hide();
	showSecaoObra(3);
}

function salvaRec(id) {
	if(id == 0){
		if($("#valor").val() == '' && $("#origem").val() == '' && $("#prazo").val() == '')
			return;
		
		$.get('sgo.php',{
			'acao'      : 'salvaRec',
			'obraID'    : $("#obraID").val(),
			'rec_valor' : $("#valor").val(),
			'rec_origem': escape($("#origem").val()),
			'rec_prazo' : $("#prazo").val()
		}, function(fb){
			//var fb = eval(fb);
			try {
				fb = eval(fb);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
				}
			}
			
			if(fb[0].success == true) {
				
				$("#recTable").append('<tr class="c"><td class="c">R$ '+ $("#valor").val() +'</td><td class="c">'+ $("#origem").val() +'</td><td class="c">'+ $("#prazo").val() +'</td><td class="c"></td></tr>')
				$("#valor").val('');
				$("#origem").val('');
				$("#prazo").val('');
			} else {
				$("#salvaRec").html('Erro. Tentar novamente');
			}
		});
	}
}

function salvaEtapa(id) {
	if(id == 0) {
		if($("#tipoEtapa").val() == '' || $("#respEtapa").val() == '' || $("#procEtapa").val() == '') {
			return;
		}
		$("#noEtapaRow").hide();
		$("#salvaEtapa").val("Adicionando Etapa...");
		
		$.get('sgo.php',{
			'acao'   : 'salvaEtapa',
			'obraID' : $("#obraID").val(),
			'tipoID' : $("#tipoEtapa").val(),
			'respID' : $("#respEtapa").val(),
			'procID' : $("#procEtapa").val()
		}, function(fb) {
			//var fb = eval(fb);
			try {
				fb = eval(fb);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
				}
			}
			
			if(fb[0].success == true) {
				$("#addEtapaTable").hide();
				$(".addEtapaLink").show();
				$("#etapasTable").append('<tr class="c"><td class="c">'+ $("option:selected").html() +'</td><td class="c"><a href="javascript:void(0);" onclick="window.open(\'sgd.php?acao=ver&amp;docID='+ $("#procEtapa").val() +'&novaJanela=1\',\'detalheEtapa\',\'width=\'+screen.width*newWinWidth+\',height=\'+screen.height*newWinHeight+\',scrollbars=yes,resizable=yes\').focus()">'+ $("#detalhe"+$("#procEtapa").val()).html() +'</a></td><td class="c"><a href="javascript:void(0)" onclick="window.open(\'sgo.php?acao=verEtapa&amp;etapaID='+ fb[0].etapaID +'\',\'detalheEtapa\',\'width=\'+screen.width*newWinWidth+\',height=\'+screen.height*newWinHeight+\',scrollbars=yes,resizable=yes\')">Ver detalhes</a></td></tr>');
				$("#salvaEtapa").val("Adicionar");
			} else {
				$("#salvaEtapa").val("Erro! Tentar Novamente");
			}
		});
	}
}

function showBuscarMapa(){
	$("#selMap").show();
}

function selectPlace(lat, lng){
	$("#latObra").val(Math.round(lat*1000000)/1000000);
	$("#lngObra").val(Math.round(lng*1000000)/1000000);
}

function showEtapaDet(id) {
	$("#det"+id).show();
}