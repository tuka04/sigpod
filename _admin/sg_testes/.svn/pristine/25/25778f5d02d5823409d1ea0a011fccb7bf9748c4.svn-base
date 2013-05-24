$(document).ready(function(){
	
	$(".hid").hide();
	
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
	
	$("#numero_pr_un").addClass("right");
	$("#numero_pr_num").addClass("right");
	
	// clausula especial (EM TESTE) para verificacao de processos parecidos
	$("#unOrgProc").blur(function() {
		//alert("q");
		var digito = $("#numero_pr_un").val();
		var tipo = $("#numero_pr_tipo").val();
		var central = $("#numero_pr_num").val();
		var ano = $("#numero_pr_ano").val();
		var unOrgProc = $("#unOrgProc").val(); 
		
		if (digito == null || tipo == null || central == null || ano == null || unOrgProc.length <= 0) return;
		
		if ($(".alert").length > 0 && $(".alert").attr("style") != "display: none; ") return;
		
		$.get("sgd.php", {
			'acao': 'parecido',
			'unOrgProc': unOrgProc,
			'digito': digito,
			'tipo': tipo,
			'central': central,
			'ano': ano
		}, function(d) {
			//var d = eval(d);
			try {
				d = eval(d);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message + " Retorno: " + d);
				}
			}
			if (d.length > 0) {
				if ($("#alert").length <= 0) {
					var novoDiv = '<div id="alert" title="Processos Parecidos" style="display: none; ">';
				}
				else {
					var novoDiv = "";
				}
				novoDiv += '<center><span style="color: red; font-weight: bold">Aten&ccedil;&atilde;o</span>: Os seguintes processos possuem a mesma Unidade de Proced&ecirc;ncia e n&uacute;mero parecido com este que voc&ecirc; est&aacute; tentando cadastrar. ';
				novoDiv += 'Por favor, verifique se este documento já não está cadastrado no sistema antes de prosseguir.</center><br /><br />';
				
				novoDiv += '<table width="100%"><tr><td class="cc"><b>n° Doc.</b></td><td class="cc"><b>Tipo/Número</b></td><td class="cc"><b>Unidade/&Oacute;rg&atilde;o Interessado</b></td><td class="cc"><b>Assunto</b></td></tr>';
				for (var i = 0; i < d.length; i++) {
					novoDiv += '<tr class="c"><td class="cc">'+d[i].id+'</td><td class="cc">';
					novoDiv += '<a onclick="window.open(\'sgd.php?acao=ver&amp;docID=' +d[i].id+ '&amp;novaJanela=1\',\'doc\',\'width=\'+screen.width*newWinWidth+\',height=\'+screen.height*newWinHeight+\',scrollbars=yes,resizable=yes\').focus()">';
					novoDiv += "Processo " + d[i].numeroComp;
					novoDiv += '</a></td><td class="cc">'+d[i].emitente+'</td><td class="cc">'+d[i].assunto+'</td>';				
					novoDiv += '</tr>';
				}

				novoDiv += '</table>';
				//novoDiv += '<br /><br /><center><a onclick="fechaDiv(\'#alert\')">Fechar aviso.</a></center></div>';
				novoDiv += '</div>';
				if ($("#alert").length <= 0) {
					$(".hid").before(novoDiv);
				}
				else {
					$("#alert").html(novoDiv);
				}
				
				$("#alert").dialog({ 
					resizable: false,
					height: 500,
					width: 700,
					modal: true,
					beforeClose: function() {
						fechaDiv("#alert");
						$("#unOrgInt").focus();
					},
					buttons: {
						"OK, Obrigado!": function() { $(this).dialog('close'); fechaDiv('#alert'); $("#unOrgInt").focus(); }
					}
				});
				
				bloqueiaCampos(true);
				$("#alert").show();
				$("#alert").focus();
			}
		});
	});
	/* fim da secao especial de tratamento de cadastro de procesos */
	
	$("#buscaForm").submit(function(submit){
		submit.preventDefault();
		var q = '';
		//TODO var q = 
		var campos = $("#camposBusca").val();
		var campo = campos.split(",");
		var i = 0, exit = false;
		
		if($("#numero_dgen").val() == "" && $("#labelID").val() == 7){
			$("#numero_dgen").val("SIGPOD");
		}
		
		for(i = 0 ; i < campo.length ; i++){
			var cpval = $("#"+campo[i]).val();
			if(cpval == ''){
				if ((campo[i] == "numero_pr_un") && ($("#numero_pr_tipo").val() == "F")) {
					continue;
				}
				limpaCampos();
				alert("Todos os campos devem ser preenchidos!");
				exit = true;
				return;
			}
			else {
				if (!verificaData()) return;
			}
			q += campo[i]+"="+cpval+"|";
		};//close each
		$(".alert").hide();
		if(!exit)
			doBuscaProc(q);
	});//close submit

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
		
		$("input", $("#cadForm")).each(function() {
			if ($(this).hasClass("hasDatepicker") && $(this).hasClass("obrigatorio")) {
				//alert($(this).attr("maxlength") + " - " + );
				/*if ($(this).attr("maxlength") == undefined || $(this).attr("maxlength") == "") {
					alert($(this).attr("maxlength"));
					return;
				}*/
				if ($(this).attr("maxlength") > $(this).val().length) {
					alert("Preencha os campos de data no formato correto. dd/mm/aaaa");
					submit.preventDefault();
					return;
				}
			}
		});
		
		//copiando os campos de busca para o form de envio
		$.each(campo,function(i){
			//alert(campo[i] + " " + $("#_"+campo[i]).has("option").length)
			if ($("#"+campo[i]).has("option").length <= 0) {
				//alert(campo[i]);
				$("#_"+campo[i]).val($("#"+campo[i]).val());
				//alert($("#"+campo[i]).val())
				//alert($("#_"+campo[i]).val())
			}
			else {
				$("#_"+campo[i]).val($("#"+campo[i]+" option:selected").val());
				//alert('qqweqweq')
			}
		});//close each
		

		$("#submitCad").attr('disabled','disabled');
		$("#submitCad").val('Salvando... Aguarde.');
		setInterval(function(){ reEnableButton("#submitCad")},30000);
		//alert($("#unOrgProc").val());
	});//closes submit
 });//close document.ready
 
 function doBuscaProc(q){
	$("#buscabut").val("Buscando...");
	$("#buscabut").attr("disabled","disabled");

	limpaCampos();
	
	$.get("sgd_busca.php",{'tipoBusca':'cadSearch', "tabela": $("#tabBD").val(),"campos": $("#camposBusca").val(), "labelID": $("#labelID").val(), 'valores': escape(q) } ,function(d){//busca
		//alert(d);
		//var data = eval(d);
		try {
			var data = eval(d);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
			}
		}
	
		$("#buscabut").removeAttr("disabled");
		$("#buscabut").val("Consultar");
	
		//clausula especial para REP
		if($("#tabBD").val() == 'doc_rep'){
			var res = preenche_rep(data[0]);//rep.js
			
			if(res.canSubmit == false){
				displayError(res);//rep.js
				return;
			}
			
			data = new Array();
		}
		
		if(data.length > 0){
			$("#id").val(data[0].id);
			
			$("a#addObraLink").hide();
			$("a#addDocLink").hide();
			$("a#addEmpresaLink").hide();
			$("#arq1").hide();
			
			copiaCamposBusca();			
			
			if (data[0].anexado == 1) {
				hideDesp();
			}
			if (data[0].arquivado == 1) {
				hideDesp();
			}
			if (data[0].solicitante != "") {
				hideDesp();
			} 
			if (data[0].despachavel == 0) {
				hideDesp();
			}

			
			/*if((data[0].anexado || !data[0].despachavel || data[0].arquivado) || data[0].solicitante != "") {

				hideDesp();
			}*/
			
			if (data[0].solicitante != "") {
				$("#despacho").html("Despachado para "+data[0].solicNome+" automaticamente atendendo solicitação.");

				$("#para option[value='"+data[0].solicArea+"']").attr("selected", "selected");
				$("#subp").html('<option id="'+data[0].solicID+'" nome="'+data[0].solicID+'" value="'+data[0].solicID+'">'+data[0].solicNome+'</option>');
				$("#subp").show();
				$("#camposDespacho").append('<input type="hidden" name="solicitante" id="solicitante" value="'+data[0].solicNome+'">');
				$("#camposDespacho").hide();
				
				if ($("#despLabel").length <= 0) $("#camposDespacho").before(' <span id="despLabel">'+data[0].solicNome+"<br /><br /></span>");
				else $("#despLabel").html(data[0].solicNome+"<br /><br />");
				
				$("#despAlerta").html('<span style="color: red; font-weight: bold">AVISO</span>: '+$("#solicitante").val()+' havia solicitado este documento. Favor entregar este documento a ele/ela.');
				$("#despAlerta").dialog({ 
					resizable: false,
					height: 200,
					width: 350,
					modal: true,
					buttons: { "OK": function() { $(this).dialog('close'); } }
				});
			}
			
			//completar campos
			completa_campos(data[0]);
			
			//completar arquivos anexos
			completa("arqs",data[0]);
			
			//completar documentos
			//completa("docs",data[0]);
			
			//completar obra
			//completa("obra",data[0]);
			
			//completar empresas
			//completa("empresa",data[0]);
			
			//completar os campos
			completa("hist",data[0]);
		}
		$(".retirar").hide();
		$(".hid").slideDown("");
	});//close get
} 

function hideDesp(){//bloqueia o despacho caso o documento esteja com outra pessoa ou anexado.
	$("#para").hide();
	$("#despacho").html("N&atilde;o &eacute; poss&iacute;vel despachar esse documento pois ele est&aacute; arquivado ou anexado a um outro documento ou voc&ecirc; n&atilde;o tem privil&eacute;gios suficientes para realizar esta a&ccedil;&atilde;o.");
	$("#despacho").attr("disabled","disabled");
	$("#submitCad").attr("disabled","disabled");
	$("#rrNumReceb").attr("disabled","disabled");
	$("#rrAnoReceb").attr("disabled","disabled");
	$("#unOrgReceb").attr("disabled","disabled");
}
 
function limpaCampos(){// 'reseta o formulario para repreenchimento
	//alert('q')
	var campos = $("#camposGerais").val();
	var campoID = campos.split(",");
	$.each(campoID,function(i){
		if($("#"+campoID[i]).attr('type') == 'checkbox' || $("#"+campoID[i]).attr('type') == 'radio') {
			$("#"+campoID[i]).removeAttr('checked');
		}
		else if($("#"+campoID[i]).attr('type') == 'text')
			$("#"+campoID[i]).val('');
		else
			$("#"+campoID[i]).val("nenhum");
	});

	$("input").each(function() {
		if (!$(this).hasClass('skipErase'))
			$(this).removeAttr("disabled");
	});
	$("textarea").removeAttr('disabled');
	$("textarea").html("");
	$("#para").show();
	//$("#despacho").html("Digite a instru&ccedil;&atilde;o aqui.");
	$("#despacho").html("");
	$("#despacho").removeAttr("disabled");
	$("div#arqs").html("");
	$("div#docsAnexos").html("");
	$("div#obrasAnexas").html("");
	$("div#emprAnexas").html("");
	$("div#docsAnexosNomes").html("");
	$("div#obrasAnexasNomes").html("");
	$("div#emprAnexasNomes").html("");
	$("div#hist").html("");
	$("a#addDocLink").show();
	$("a#addObraLink").show();
	$("a#addEmpresaLink").show();
	$("#arq1").show();
	$("#id").val("0");
	$("#docsAnexos").val("");
	$("select").removeAttr("disabled");
	$("#camposDespacho").show();
	if ($("#despLabel").length > 0) $("#despLabel").html("");
}

function copiaCamposBusca(){//na ocasiao do encio do formulario de busca, copia os campos para o formulario de cadastro para passar para as proximas paginas
	//alert("oi")
	var camposBusca = $("#camposBusca").val();
	var campoNome = camposBusca.split(',');
	var i = 0;
	$.each(campoNome,function(){
		$("#_"+campoNome[i]).val($("#"+campoNome[i]).val());
		i++;
	});
}

 //completa os campos do formulario com os dados lidos em ajax
function completa_campos(data){	
	var campos = $("#camposGerais").val();
	var campoID = campos.split(",");
	var i = 0;
	$.each(campoID,function(){
		if($("#"+campoID[i]).attr('type') == 'checkbox'){
			if(data[campoID[i]] == 1){
				$("#"+campoID[i]).attr('checked','checked');
			}
		} else {
			$("#"+campoID[i]).val(html_entity_decode(data[campoID[i]]));
		}
		$("#"+campoID[i]).attr("disabled","disabled");
		i++;
	});
}

function bloqueiaCampos(bloquear) {
	var campos = $("#camposGerais").val();
	var campoID = campos.split(",");
	var i = 0;

	$.each(campoID,function(){
		if (bloquear == true) $("#"+campoID[i]).attr("disabled","disabled");
		else $("#"+campoID[i]).removeAttr("disabled");
		i++;
	});

	if (bloquear == true) $("#submitCad").attr("disabled","disabled");
	else $("#submitCad").removeAttr("disabled");
	
}

function fechaDiv(div) {
	$(div).hide();
	bloqueiaCampos(false);
}

//completa os campos genericos de anexo
function completa(tipo,data){
	var dado = data[tipo];
	var linha = '';
	
	$("div#"+tipo).html('');//limpa o campo
	if(dado.length == 0){//verifica se há anexo
		$("div#"+tipo).append("Nenhum adicionado.");
		return;
	}

	var i = 0;
	var acao;
	$.each(dado,function(){
		if(tipo == "obra"){
			//linha = "Obra "+dado[i].id+": "+dado[i].nome+'(Ver detalhes Pend) <input type="hidden" name="obra'+i+'" value="'+dado[i].id+'" /><br />';
			//TODO passando IDs de obra pra prox pag (vide doc)
		}
		if(tipo == "docs"){
			newDocLink(dado[i].id,dado[i].nome,'docsAnexos','<br>');
		}
		if(tipo == "empresa"){
			//linha = '<input type="hidden" name="emp'+i+'" value="'+dado[i].id+'" />';
			//TODO passando IDs de empresa pra prox pag (vide doc)
		}
		if(tipo == "hist"){
			if(dado[i].tipo == 'obs') {
				acao = 'Adicionou observa&ccedil&atilde;o ao documento: '+dado[i].despacho;
			} else if(dado[i].tipo == 'saida') {
				acao = 'Despachou o documento para '+dado[i].unidade+':'+dado[i].despacho;
			} else if(dado[i].tipo == 'entrada') {
				acao = 'Recebeu o documento de '+dado[i].unidade+'('+dado[i].despacho+')';
			} else if(dado[i].tipo == 'despIntern') {
				acao = 'Despachou o documento para '+dado[i].unidade+': '+dado[i].despacho;
			} else if(dado[i].tipo == 'criacao') {
				acao = 'Criou este documento';
			} else if(dado[i].tipo == 'solicArq') {
				acao = 'Solicitou o arquivamento deste documento.';
			} else if(dado[i].tipo == 'solicDesarq') {
				acao = 'Solicitou o desarquivamento deste documento.';
			} else if(dado[i].tipo == 'solicProt') {
				acao = 'Solicitou este documento externamente.';
			} else if(dado[i].tipo == 'solic') {
				acao = 'Solicitou este documento.';
			} else if(dado[i].tipo == 'arq') {
				acao = 'Arquivou este documento.';
			} else if(dado[i].tipo == 'desarq') {
				acao = 'Desarquivou este documento.';
			}
			
			linha = "Em "+dado[i].data+" por "+dado[i].username+": "+acao+"<br />";
		}
		if(tipo == "arqs"){
			if(dado.length == 1 && dado[1] == '')
				linha = "Nenhum arquivo anexado.";
			linha = '<a href="files/'+dado[i]+'">'+dado[i]+'</a><br />';
		}
		
		linha += "";
		$("div#"+tipo).append(linha);
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