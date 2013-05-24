$(document).ready(function(){
	$(".aditivo_razao_select").change(function(event){
		var selecionado = event.currentTarget;
		if(selecionado.value == '_outro'){
			$("#"+selecionado.id).hide();
			$("#"+selecionado.id+'_outro').show();
		}
	});
	
	// bind de select de funcionário, para atualizar célula de crea automaticamente
	$(".selectFunc").each(function() {
		$(this).bind('change', function() {
			var id = $(this).attr("id");
			var opt = $("#"+id+" option:selected");
			var crea = opt.val();
			
			$(this).parent().next("td").next("td").html(crea);
		});
	});
	
	// bind no campo de empresa... caso seja trocado, apaga linha de funcionários antigos
	$("#empresaID").change(function() {
		// pega o nome da empresa selecionada
		var nome = $(this).children("option:selected").html();
		// pega id da empresa selecionada
		var id = $(this).children("option:selected").val();
		
		var html = '';
		
		// monta select de tipo
		var select = '<select id="tipoFunc0" class="" name="tipoFunc0"><option value=""> -- Selecione -- </option>';
		select += '<option value="resp">Responsável</option><option value="respTec">Responsável Técnico</option>';
		select += '<option value="eng">Engenheiro Residente</option></select>';
		
		// monta linha de novo funcionário
		html += '<tr id="trFunc0" class="c">';
		html += '<td class="c"><input type="hidden" name="novoFunc0" value="0"></td>';
		html += '<td class="c"><b>Funcionário</b>: </td>';
		html += '<td id="tdFunc0" class="c"></td>';
		html += '<td class="c"><b>CREA</b>: </td><td class="c"></td>';
		html += '<td class="c"><b>Tipo</b>: </td><td class="c" id="tdTipoFunc0">'+select+'</td>';
		html += '<td class="c"><b>ART</b>: </td>';
		html += '<td class="c"><input type="file" name="funcART0" id="funcART0" onclick="newFunc(0)"></td></tr>';
		
		// remove primeira linha
		$("#trFunc0").remove();
		
		// remove todas as outras linhas
		var i = 1;
		while ($("#trFunc"+i).length > 0) {
			$("#trFunc"+i).remove();
			i++;
		}
		
		// coloca o html gerado na tabela
		$("#tabelaEditEmpresa").append(html);
		
		// carrega por ajax os funcionários da empresa selecionada
		showEmpresaFuncionarios(id);
	});
	
	// bind de inputs de valor de recurso
	$("input").each(function() {
		// se o campo tem a classe valRec (identifica os campos de recurso)
		if ($(this).hasClass("valRec")) {
			$(this).bind('keydown', function(e) {
				// se o usuário apertar virgula, ponto, + ou -, deixa que seja adicionado
				if (e.keyCode == 188 || e.keyCode == 110 || e.keyCode == 107 || e.keyCode == 109) {
					return;
				}
				else { // senão, verifica se é número
					verificaNumero(e);
				}
			});
			
			// quando o campo perder o foco, checa o checkbox correspondente de recurso
			$(this).bind('blur', function() {
				if ($(this).val().length > 0) {
					var id = $(this).attr("id");
					id = id.split("_");
					id = id[1];
					$("input[type=checkbox][value="+id+"]").attr("checked", "checked");
				}
			});
		}
	});
});

/**
 * desativa funcionário por ajax
 * @param docID
 * @param crea
 */
function desativaFunc(docID, crea) {
	$.get('sgd.php', {
		acao: 'desativaFuncAjax',
		docID: docID,
		crea: crea
	}, function(d) {
		//d = eval(d);
		try {
			d = eval(d);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
			}
		}
		
		if (d.success == true) {
			alert("Funcionário desativado com sucesso!");
		}
		else {
			alert("Erro ao tentar desativar funcionário. Tente novamente.");
		}
	});
	
}

/**
 * Carrega funcionários da empresa por ajax
 * @param empresaID
 */
function showEmpresaFuncionarios(empresaID) {
	$.get('empresa.php', {
		acao: 'getFuncAjax',
		empresaID: empresaID
	}, function(d) {
		//d = eval(d);
		try {
			d = eval(d);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
			}
		}
		
		if (d[0].success == true) {
			// pega select gerado por ajax
			var select =  d[0].funcSelect;
			
			// coloca o select na 1a linha da tabela
			$("#tdFunc0").html(select);
			
			// renomeia o select
			$("#empresaFuncID1").attr("id", "empresaFuncID0");
			$("#empresaFuncID0").attr("name", "empresaFuncID0");
			$("#empresaFuncID0").toggleClass('selectFunc');
			
			// faz o bind para que quando o select tenha seu valor alterado, atualize a célula de crea correspondente
			$("#empresaFuncID0").bind('change', function() {
				var id = $(this).attr("id");
				var opt = $("#"+id+" option:selected");
				var crea = opt.val();
				
				$(this).parent().next("td").next("td").html(crea);
			});
			
			//doBind();
		}
	});
}

/**
 * Salva edição de empresa por ajax
 * @param docID
 */
function editEmpresa(docID) {
	$("#contrEditEmpresa").dialog({
		resizable: true,
		height: 400,
		width: 1024,
		modal: true,
		buttons: {
			"Salvar": function() {
				$("#formEditEmpresa").submit();
				
				
				$(this).dialog('close');
			},
			"Cancelar": function() { $(this).dialog('close'); }
		}
	});
}

function showCadFunc() {
	if ($("#cadFunc").length < 1) {
		var html = '<div id="cadFunc" title="Cadastrar Novo Funcion&aacute;rio">';
		html += '<b>Empresa</b>: <span id="cadFuncEmpresa"></span><br><br>';
		html += '<b>Nome</b>: <input id="nomeFunc" type="text" style="width: 60%"><br>';
		html += '<b>CREA</b>: <input id="creaFunc" type="text" style="width: 60%"><br>';
		html += '</div>';
		
		$("body").append(html);
	}
	
	var nome = $("#empresaNome", $("#contrEditEmpresa")).val();
	if (nome == undefined || nome == "") {
		nome = $("#empresaID option:selected").html();
	}
	alert(nome);
	
	var empresaID = $("#empresaID", $("#contrEditEmpresa")).val();
	alert(empresaID);
	
	$("input", $("#cadFunc")).val('');
	$("#cadFuncEmpresa", $("#cadFunc")).html(nome);
	$("#cadFunc").dialog({
		resizable: true,
		height: 200,
		width: 250,
		modal: true,
		buttons: {
			"Cadastrar": function() {
				var nome = $("#nomeFunc", $("#cadFunc")).val();
				var crea = $("#creaFunc", $("#cadFunc")).val();
				
				nome = HTMLEncode(nome);
				//alert(nome);
				
				if (nome == "" || crea == "") {
					alert("Preencha todos os campos!");
					return;
				}
				
				$(this).dialog('close');
				
				$.post('empresa.php?acao=cadFunc', {
					empresaID: empresaID,
					crea: crea,
					nome: nome
				}, function(d) {
					//alert(d);
					//d = eval(d);
					try {
						d = eval(d);
					} catch(e) {
						if (e instanceof SyntaxError) {
							alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
						}
					}
					
					if (d[0].success == true) {
						var option = '<option value="'+crea+'">'+nome+'</option>';
						//$("#empresaFuncID").append(option);
						$(".selectFunc").each(function() {
							$(this).append(option);
						});
						
						alert("Cadastrado com sucesso!");
					}
					else
						alert("Erro ao cadastrar funcionário");
				});
				
			},
			"Cancelar": function() { $(this).dialog('close'); }
		}
	});
}

function newFunc(numero) {
	var linha = '';
	
	//var linkCadFunc = $("#linkCadFunc"+numero).clone(true);
	//linkCadFunc.attr("id", "linkCadFunc"+(numero+1));
	
	if ($("#empresaFuncID"+numero).length < 1) {
		return;
	}

	var select = $("#empresaFuncID"+numero).clone(true);
	select.attr("id", "empresaFuncID"+(numero+1));
	select.attr("name", "empresaFuncID"+(numero+1));
	
	var selectTipo = $("#tipoFunc"+numero).clone(true);
	//alert(selectTipo.id);
	selectTipo.attr("id", "tipoFunc"+(numero+1));
	selectTipo.attr("name", "tipoFunc"+(numero+1));
	//alert(selectTipo.id);
	
	linha += '<tr id="trFunc'+(numero+1)+'" class="c">';
	linha += '<td class="c"><input type="hidden" name="novoFunc'+(numero+1)+'" value="0"></td>';
	linha += '<td class="c"><b>Funcionário</b>: </td>';
	linha += '<td id="tdFunc'+(numero+1)+'" class="c"></td>';
	linha += '<td class="c"><b>CREA</b>: </td>';
	linha += '<td class="c"></td>';
	linha += '<td class="c"><b>Tipo</b>: </td>';
	linha += '<td class="c" id="tdTipoFunc'+(numero+1)+'"></td>';
	linha += '<td class="c"><b>ART</b>: </td>';
	linha += '<td class="c">';
	linha += '<input type="file" name="funcART'+(numero+1)+'" id="funcART'+(numero+1)+'" onclick="newFunc('+(numero+1)+')"></td>';
	
	linha += '</tr>';
	
	$("#funcART"+numero).removeAttr("onclick");
	
	//$("#tabelaEmp").append(html);
	//$("#tabelaEditEmpresa").append(linha);
	$("#trFunc"+numero).after(linha);
	
	select.appendTo("#tdFunc"+(numero+1));
	selectTipo.appendTo("#tdTipoFunc"+(numero+1));
	
	select.children("option:selected").removeAttr("selected");
	selectTipo.children("option:selected").removeAttr("selected");
	
	//alert($("#tdTipoFunc"+(numero+1)).length)
	//$("#tdFunc"+(numero+1)).append('<br/><a onclick="showCadFunc('+empresaID+', \''+nome+'\')">[Cadastrar Funcion&aacute;rio]</a>');
	//$("#tdFunc"+(numero+1)).append('<br/>');
	//linkCadFunc.appendTo("#tdFunc"+(numero+1));
	
	//alert(linha)
}

function editContrVal(campoNome){	
	$("#"+campoNome+"_val").hide();
	$("#"+campoNome+"_edit").show();
	$("#"+campoNome+"_link").val("Salvar");
	$("#"+campoNome+"_form").attr("action","javascript:saveContrVal('"+campoNome+"')");
}

function saveContrVal(campoNome){
	var valor;
	
	if (campoNome == 'valorMaoObra' || campoNome == 'valorProj' || campoNome == 'valorMaterial')
		valor = escape($("#"+campoNome).val().replace(".","").replace(",","."));
	else
		valor = escape($("#"+campoNome).val());
	//alert(valor);
	
	if (campoNome == "dataReuniao") {
		var dataAssinatura = $("#dataAssinatura").val();
		var dataPartes = dataAssinatura.split("/");
		
		var reuniaoPartes = valor.split("/");
		
		var dateSign = new Date(dataPartes[2], dataPartes[1], dataPartes[0], 0, 0, 0, 0);
		var dataMeet = new Date(reuniaoPartes[2], reuniaoPartes[1], reuniaoPartes[0], 0, 0, 0, 0);
		
		if (dateSign > dataMeet) {
			if (!confirm("A data de reunião escolhida é anterior a data de assinatura do contrato. Deseja realmente prosseguir?")) {
				return;
			}
		}
	}
	else if (campoNome == "dataAssinatura") {
		var dataAssinatura = valor;
		var dataPartes = dataAssinatura.split("/");
		
		var reuniaoPartes = $("#dataReuniao").val().split("/");
		
		var dateSign = new Date(dataPartes[2], dataPartes[1], dataPartes[0], 0, 0, 0, 0);
		var dataMeet = new Date(reuniaoPartes[2], reuniaoPartes[1], reuniaoPartes[0], 0, 0, 0, 0);
		
		if (dateSign > dataMeet) {
			if (!confirm("A data de assinatura escolhida é posterior a data de reunião do contrato. Deseja realmente prosseguir?")) {
				return;
			}
		}
	}
	
	
	$.post('sgd.php?acao=edit&docID='+$("#docID").html()+'&campo='+campoNome,{
		newVal : valor
	},function(d){
		//var fb = eval(d);
		try {
			fb = eval(d);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
			}
		}
		
		if(fb[0].success == 'true'){
			var href = 'javascript:editContrVal(\''+campoNome+'\')';
			var msg = 'Salvo!';
			if (campoNome == 'valorMaoObra' || campoNome == 'valorProj' || campoNome == 'valorMaterial')
				$("#"+campoNome+"_val").html(unescape(valor).replace(".",","));
			else
				$("#"+campoNome+"_val").html(unescape(valor));
			$("#"+campoNome+"_edit").hide();
			$("#"+campoNome+"_val").show();
			
			if (fb[0].extra != undefined && fb[0].extra != null) {
				if (fb[0].extra == 'reload') {
					window.location.reload();
				}
				else {
					$("#"+fb[0].extra+"_val").html(unescape(fb[0].extraVal));
				}
			}
			
			if (campoNome == 'valorMaoObra' || campoNome == 'valorProj' || campoNome == 'valorMaterial'){
				window.location.reload();
			}
			
		} else {
			var href = 'javascript:saveContrVal(\''+campoNome+'\')';
			var msg = 'Erro. Tentar Novamente.';
		}
		
		$("#"+campoNome+"_link").val(msg);
		//$("#"+campoNome+"_form").action("href",href);
		$("#"+campoNome+"_form").attr("action", href);
	});
	
}

function showEditObra(docID) {
	$("#divEditObras").dialog({
		resizable: true,
		height: 400,
		width: 450,
		modal: true,
		buttons: {
			"OK": function() {
				var obras = new Array();
				$("input[name='inclObra[]']:checked", $("#divEditObras")).each(function() {
					obras.push($(this).val());
				});
				//showOrigemRecForm(procID, tipoProc, obras, guardachuva);
				salvaEditObras(docID, obras);
				$(this).dialog('close');
			},
			"Cancelar": function() { $(this).dialog('close'); }
		}
	});
}

function salvaEditObras(docID, obras) {
	$.post('sgo.php?acao=editContrObra', {
		docID: docID,
		obras: obras
	}, function(d) {
		//d = eval(d);
		try {
			d = eval(d);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
			}
		}
		
		if (d[0].success == true) {
			alert("Dados salvos com sucesso!");
		}
		else {
			alert("Erro ao salvar edição de Obras. Tente novamente.");
		}
		
		window.location.reload();
	});
}

function showEditRecurso(procID) {
	$("#divEditRec").dialog({
		resizable: true,
		height: 400,
		width: 450,
		modal: true,
		buttons: {
			"OK": function() {
				var recursos = new Array();
				$("input[name='inclRec[]']:checked", $("#divEditRec")).each(function() {
					var item = new Object;
					item.id = $(this).val();
					item.valor = $("#valRec_"+$(this).val(), $("#divEditRec")).val();
					//alert(item.id)
					//alert(item.valor)
					recursos.push(item);
				});
				
				$(this).dialog('close');
				salvaEditRecursos(procID, recursos);
			},
			"Cancelar": function() { $(this).dialog('close'); }
		}
	});
}

function salvaEditRecursos(docID, recursos) {
	$.post('sgo.php?acao=editContrRec', {
		docID: docID,
		recursos: JSON.stringify(recursos)
	}, function(d) {
		//d = eval(d);
		try {
			d = eval(d);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
			}
		}
		
		if (d[0].success == true) {
			alert("Dados salvos com sucesso!");
		}
		else {
			alert("Erro ao salvar edição de Recursos. Tente novamente.");
		}
		
		window.location.reload();
	});
}

//mostra campo de cadastro de aditivo
function show_aditivar_campo(nome_campo){
	$("#aditivar_dialog").dialog({
		height: 250,
		width: 500,
		modal: true,
		buttons : {
			"Salvar" : function(){
				do_aditivar_campo(nome_campo);
			},
			"Cancelar" : function(){
				close_aditivo_dialog();
			}
		}
	});
}

//aditiva um campo
function do_aditivar_campo(campo_nome) {
	//deve ser especificada uma razão para aditivar
	if(($("#aditivar_razao").val() == '_outro' && $("#aditivar_razao_outro").val() == '') || $("#aditivar_razao").val() == ''){
		alert("Por favor, selecione um motivo ou selecione Outros e especifique um motivo");
		return;
	}
	//se a opcao escolhida for outro, deve ser salvo o campo texto
	if($("#aditivar_razao").val() == '_outro'){
		var razao = $("#aditivar_razao_outro").val();
	} else {
		//senao, salva o texto do select
		var razao = $("#aditivar_razao").children("option[selected=selected]").html();
	}
	
	//envia os dados para serem salvos
	$.post('sgo.php?acao=aditivar_contrato',
			{campo:campo_nome,
			 contratoID:$("#docID").html(),
			 valor:$("#aditivar_valor").val(),
			 motivo: escape(razao)
			},
			function(data){
				//interpreta o resultado do salvamento
				data = JSON.parse(data);
				//monta a linha de aditivo
				if(data[0].success) {
					$("#"+campo_nome+"_aditivos_div").append('<br /><span id="aditivo_valor_'+data[0].aditivoID+'">'+$("#aditivar_valor").val()+'</span> (<span id="aditivo_valor_porcentagem_'+data[0].aditivoID+'">'+data[0].novaPorcentagem+'</span> %) (Motivo: <span id="aditivo_motivo_'+data[0].aditivoID+'">'+razao+'</span>) <a href="javascript:void(0)" onclick="javascript:show_editar_aditivo('+data[0].aditivoID+',\''+campo_nome+'\')">[Editar]</a>');
					$("#"+campo_nome+'_total_aditivos').html(data[0].novoValor);
					$("#"+campo_nome+'_total_aditivos_porcentagem').html(data[0].novoTotalPorcentagem);
					//fecha o dialog
					close_aditivo_dialog();
				} else {
					//se houve falha, alerta
					alert("Falha ao adicionar Aditivo: "+data[0].errorFeedback);
				}
			});
}

//limpa os campos de aditivar um contrato
function close_aditivo_dialog(){
	$("#aditivar_dialog").dialog("close");
	$("#aditivar_valor").val('');
	$("#aditivar_razao_outro").val('');
	$("#aditivar_razao_outro").hide();
	$("#aditivar_razao").val('');
	$("#aditivar_razao").show();
}

//mostra dialog para editar um recurso inserido no sistema
function show_editar_aditivo(aditivo_id,campo_nome){
	//se eh editar, mostra o campo de texto
	$("#aditivar_razao_outro").show();
	$("#aditivar_razao").hide();
	//preenche os valores
	$("#aditivar_valor").val($("#aditivo_valor_"+aditivo_id).html());
	$("#aditivar_razao_outro").val($("#aditivo_motivo_"+aditivo_id).html());
	//mostra o dialog de aditivo
	$("#aditivar_dialog").dialog({
		height: 250,
		width: 500,
		modal: true,
		buttons : {
			"Salvar" : function(){
				do_editar_aditivo(aditivo_id,campo_nome);
			},
			"Cancelar" : function(){
				close_aditivo_dialog();
			}
		}
	});
}

//realiza a acao de editar um aditivo existente
function do_editar_aditivo(aditivo_id,campo_nome){
	//verifica se ainda ha motivo do aditivo
	if($("#aditivar_razao_outro").val() == ''){
		alert("Por favor, especifique um motivo");
		return;
	}
	//envia os novos dados do aditivo
	$.post('sgo.php?acao=editar_aditivo', {
			 contratoID:$("#docID").html(),
			 campo: campo_nome,
			 aditivoID: aditivo_id,
			 valor: $("#aditivar_valor").val(),
			 motivo: escape($("#aditivar_razao_outro").val())
			},
			function(data){
				//converte os dados de retorno do servidor
				data = JSON.parse(data);
				//se foi bem sucedido
				if(data[0].success) {
					//atualiza os dados
					$("#aditivo_valor_"+aditivo_id).html($("#aditivar_valor").val());
					$("#aditivo_motivo_"+aditivo_id).html($("#aditivar_razao_outro").val());
					//atualiza porcentagem e total de aditivos
					$("#aditivo_valor_porcentagem_"+aditivo_id).html(data[0].novaPorcentagem);
					$("#"+campo_nome+'_total_aditivos').html(data[0].novoValor);
					$("#"+campo_nome+'_total_aditivos_porcentagem').html(data[0].novoTotalPorcentagem);
					//fecha o dialogo
					close_aditivo_dialog();
				} else {
					//se flaha, mostra alert
					alert("Falha ao adicionar Aditivo: "+data[0].errorFeedback);
				}
			});
}