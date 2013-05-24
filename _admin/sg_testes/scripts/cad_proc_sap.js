$(document).ready(function(){
	/* secao especial para tratamento de cadastro de processos */
	// bind especial para preencher com zeros a esquerda no cadastro de numero de processo
	$("#numero_pr_num").bind('keydown', function(e) {
		verificaNumero(e);
		if (e.keyCode == 13) { // usuario pressionou enter (13) no input do numero de processo
			preencheZeros('numero_pr_num');
			preencheZeros('numero_pr_un');
		}
	}); // closes bind
	
	// script para verificacao de mudanca de numero_pr_tipo
	$("#numero_pr_tipo").change(function () {
		if ($("#numero_pr_tipo option:selected").val() == "F") {
			$("#numero_pr_num").attr('maxLength', '7');
			preencheZeros('numero_pr_num');
			$("#numero_pr_un").hide();
			$("#numero_pr_un").val("FU"); // padrao funcamp
		}
		else {
			$("#numero_pr_num").attr('maxLength', '5');
			if ($("#numero_pr_num").val().length > 5) $("#numero_pr_num").val("");
			if ($("#numero_pr_un").val() == "FU") $("#numero_pr_un").val("");
			$("#numero_pr_un").show();
		}
	});
	
	// clausula especial para verificacao de digitos numericos em numero_pr_un
	$("#numero_pr_un").bind('keydown', function(e) {
		verificaNumero(e);
	});

	// clausula especial para verificacao de digitos numericos
	$("#numero_pr_ano2").bind('keydown', function(e) {
		verificaNumero(e);
	});
	/* fim da secao especial de tratamento de cadastro de procesos */

	$("#cadForm").submit(function(submit){
		var campos = $("#camposBusca").val();
		var campo = campos.split(",");
		var i;
		var obrigatorios = $('input.obrigatorio:text[value=""]');
		
		//verificacao dos campos obrigatorios
		if(obrigatorios[0] != undefined){
			alert("Há campos obrigatórios não preenchidos. Por favor, preencha-os e envie novamente.");
			submit.preventDefault();
		}
		// verifica se a data e' uma data valida
		if (!verificaData()) submit.preventDefault();
		
		// preenchendo com zeros o numero do processo
		preencheZeros('numero_pr_un');
		preencheZeros('numero_pr_num');
		if (($("#numero_pr_un").val() == "") || ($("#numero_pr_num").val() == "")) {
			alert("Número do processo não preenchido. Por favor, preencha-o e envie novamente.");
			submit.preventDefault();
		}
		
		//copiando os campos de busca para o form de envio
		$.each(campo,function(i){
			$("#_"+campo[i]).val($("#"+campo[i]).val());
		});//close each
		copiaCamposBusca();
		//alert($("#unOrgProc").val());
	});//closes submit
 });//close document.ready

function copiaCamposBusca(){//na ocasiao do encio do formulario de busca, copia os campos para o formulario de cadastro para passar para as proximas paginas
	var camposBusca = $("#camposBusca").val();
	var campoNome = camposBusca.split(',');
	var i = 0;
	$.each(campoNome,function(){
		$("#_"+campoNome[i]).val($("#"+campoNome[i]).val());
		//$("#"+campoNome[i]).val("");
		i++;
	});
}

function verificaData() {
	//alert($("#numero_pr_ano1").val());
	if ($("#numero_pr_ano1").val() == "Outro") {
		var data = (new Date).getFullYear()-5;
		if ($("#numero_pr_ano2").val().length != 4) { // verifica se o valor do campo possui necessariamente 4 digitos
			alert("Ano deve conter 4 digitos!");
			$("#numero_pr_ano2").val("");
			return false;
		}
		else if ($("#numero_pr_ano2").val() >= data) {
			alert("Ano deve ser menor que "+data+".");
			$("#numero_pr_ano2").val("");
			return false;
		}
		else if ($("#numero_pr_ano2").val() < 1965) {
			alert("Ano deve ser maior que 1965.");
			$("#numero_pr_ano2").val("");
			return false;
		}
	}
	return true;
}