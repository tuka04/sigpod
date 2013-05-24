$(document).ready(function(){	
	// bind especial para preencher com zeros a esquerda no cadastro de numero de processo
	$("#ref_pr_num").bind('keydown', function(e) {
		verificaNumero(e);
		if (e.keyCode == 13) { // usuario pressionou enter (13) no input do numero de processo
			preencheZeros('ref_pr_num');
			preencheZeros('ref_pr_un');
		}
	}); // closes bind
	
	// script para verificacao de mudanca de numero_pr_tipo
	$("#ref_pr_tipo").change(function () {
		if ($("#ref_pr_tipo option:selected").val() == "F") {
			$("#ref_pr_num").attr('maxLength', '7');
			preencheZeros('ref_pr_num');
			$("#ref_pr_un").hide();
			$("#ref_pr_un").val("FU"); // padrao funcamp
		}
		else {
			$("#ref_pr_num").attr('maxLength', '5');
			if ($("#ref_pr_num").val().length > 5) $("#ref_pr_num").val("");
			if ($("#ref_pr_un").val() == "FU") $("#ref_pr_un").val("");
			$("#ref_pr_un").show();
		}
	});
	
	// clausula especial para verificacao de digitos numericos em numero_pr_un
	$("#ref_pr_un").bind('keydown', function(e) {
		verificaNumero(e);
	});

	// clausula especial para verificacao de digitos numericos
	$("#ref_pr_ano2").bind('keydown', function(e) {
		verificaNumero(e);
	});
	

});//close document.ready

function referDoc(nome, target) {
	var numPR = nome.split(" ");
	//var partes = numPR[1].split(" ");
	var digito = numPR[1];
	var pedacos = numPR[2].split("-");
	var tipo = pedacos[0];
	var central = pedacos[1];
	var ano = pedacos[2];
	
	$("#ref_pr_tipo option[value='"+tipo+"']").attr("selected", "selected");
	if (tipo == "F") { 
		$("#ref_pr_un").hide();
		$("#ref_pr_num").attr('maxLength', '7');
		$("#ref_pr_un").val("");
		$("#ref_pr_num").val("");
	}
	else {
		$("#ref_pr_un").show();
		$("#ref_pr_num").attr('maxLength', '5');
		$("#ref_pr_un").val("");
		$("#ref_pr_num").val("");
	}
	
	$("#"+target).val(digito + " " + numPR[2]);
	$("#ref_pr_un").val(digito);
	$("#ref_pr_num").val(central);
	$("#ref_pr_ano").val(ano);
	
	if ($("#ref_pr_ano1 option").filter("[value='"+ano+"']").length > 0) {
		$("#ref_pr_ano1 option").filter("[value='"+ano+"']").attr("selected", "selected");
	}
	else {
		$("#ref_pr_ano1").hide();
		$("#ref_pr_anooutroAno").attr("selected", "selected");
		$("#ref_pr_ano2").val(ano);
		$("#ref_pr_ano2").show();
	}
}