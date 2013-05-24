$(document).ready(function() {
	var context = $("#cadForm", $("#c3"));
	
	$("input[type=text]:not([style])", context).each(function() {
		$(this).attr("style", "width: 60%");
	});
	
	/*
	 * Binds de formatação de campos 
	 */
	
	$("#numeroContr").attr("maxlength", "5");
	
	$("input").each(function() {
		if ($(this).hasClass("valRec")) {
			$(this).bind('keydown', function(e) {
				if (e.keyCode == 188 || e.keyCode == 110 || e.keyCode == 107 || e.keyCode == 109) {
					return;
				}
				else {
					verificaNumero(e);
				}
			});
			
			$(this).bind('blur', function() {
				//alert($(this).length + " - " + $(this).val())
				if ($(this).val().length > 0) {
					var id = $(this).attr("id");
					id = id.split("_");
					id = id[1];
					$("input[type=checkbox][value="+id+"]").attr("checked", "checked");
				}
			});
		}
	});
	
	
	/*
	 * Binds de estilos dos campos que são "soma" de outros
	 */
	
	$("#vigenciaContr").bind('keydown', function(e) {
		if (e.keyCode == 9) return;
		e.preventDefault();
	});
	
	$("#valorTotal").bind('keydown', function(e) {
		if (e.keyCode == 9) return;
		e.preventDefault();
	});
	
	$("#dataTermino").bind('keydown', function(e) {
		if (e.keyCode == 9) return;
		e.preventDefault();
	});
	
	$("#vigenciaContr").attr("class", "noedit");
	$("#valorTotal").attr("class", "noedit");
	$("#dataTermino").attr("class", "noedit");
	$("#inicioProjObra").attr("class", "noedit");
	//$("#prazoContr").attr("class", "noedit");
	
	$(".noedit").each(function() {
		$(this).focus(function() {
			$(this).blur();
			
			var numJump = 1;
			if ($(this).attr("id") == 'vigenciaContr') {
				numJump = 2;
			}
			
			$(":input:eq(" + ($(":input").index(this) + numJump) + ")").focus();
		});
	});
	
	/*
	 * Binds de cálculo dos valores dos campos
	 */ 
	
	$("#prazoContr").bind('blur', function(e) {
		calculaVigencia();
	});
	
	$("#dataAssinatura").bind('blur', function(e) {
		validaReuniao();
		calculaVigencia();
	});
	
	$("#dataReuniao").bind('blur', function(e) {
		validaReuniao();
		//alert('oe')
		calculaVigencia();
		//calculaInicio();
	});
	
	$("#valorProj").bind('blur', function(e) {
		calculaValorContr($(this));
	});
	
	$("#valorMaoObra").bind('blur', function(e) {
		calculaValorContr($(this));
	});
	
	$("#valorMaterial").bind('blur', function(e) {
		calculaValorContr($(this));
	});

	$("#inicioProjObra").bind('blur', function(e) {
		calculaTermino();
	});
	
	$("#prazoProjObra").bind('blur', function(e) {
		calculaTermino();
	});
	
	$("#prazoProjObra").bind('keydown', function(e) {
		if (e.keyCode == 13) {
			calculaTermino();
		}
	});
	
	/*
	 * Binds de campos de empresa
	 */
	
	$("#empresaID").change(function() {
		limpaEmpresa();
		
		var empresaID = $("#empresaID option:selected").val();
		if (empresaID == undefined || empresaID == 0)
			return;
		
		var nome = $("#empresaID option:selected").html();
		showEmpresaFuncionarios(empresaID, nome);
	});
	
	$("#cnpj").keyup(function(){
		
		var v = $("#cnpj").val();
		
		v = v.replace(/[^0-9]/g,""); 
		
		var i, vn="";
		for(i=0 ; i<v.length ; i++){
			if(i == 2 || i == 5)
				vn += '.';
			if(i == 8)
				vn += '/';
			if(i == 12)
				vn += '-';
			if(i > 14)
				break;
			vn += v[i];
		}				
		$("#cnpj").val(vn);
	});
	
	
	$(".novaEmpresa").bind('click', function() {
		var contexto = $("#cadEmpresa");
		
		//$("input", contexto).val('');
		
		$("#cadEmpresa").dialog({
			resizable: true,
			height: 400,
			width: 450,
			modal: true,
			buttons: {
				"Cadastrar": function() {
					// pega valores dos campos
					var nome = htmlentities($("#nome", contexto).val());
					var cnpj = htmlentities($("#cnpj").val());
					var endereco = htmlentities($("#endereco", contexto).val());
					var complemento = htmlentities($("#complemento", contexto).val());
					var cidade = htmlentities($("#cidade", contexto).val());
					var estado = htmlentities($("#estado", contexto).val());
					var cep = htmlentities($("#cep", contexto).val());
					var telefone = htmlentities($("#telefone", contexto).val());
					var fax = htmlentities($("#fax", contexto).val());
					var email = htmlentities($("#email", contexto).val());
					
					// validação dos campos
					if (nome == "" || endereco == "" || complemento == "" || cidade == "" || estado == "" || 
						cep == "" || telefone == "" || email == "" || fax == "") {
						alert("Preencha todos os campos!");
						return;
					}
					
					// valida cnpj
					if(!valida_cnpj(cnpj.replace(/[^0-9]/g,""))) {
						alert("CNPJ invalido ou nao preenchido!");
						return;
					}

					// envia dados
					$.post('empresa.php?acao=cadEmpresa', {
						nome: escape(nome),
						cnpj: escape(cnpj),
						endereco: escape(endereco),
						complemento: escape(complemento),
						cidade: escape(cidade),
						estado: escape(estado),
						cep: escape(cep),
						telefone: escape(telefone),
						fax: escape(fax),
						email: escape(email)
					}, function(d) {
						try {
							d = eval(d);
						} catch(e) {
							if (e instanceof SyntaxError) {
								alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message + " Retorno: "+d);
							}
						}
						
						if (d[0].success == true) {
							var option = '<option value="'+d[0].id+'">'+nome+'</option>';
							$("#empresaID").append(option);
							$("input", contexto).val('');
							alert("Empresa cadastrada com sucesso!");
						}
						else {
							alert("Falha no cadastro da empresa. Tente novamente.");
							return;
						}
					});
					$(this).dialog('close');
					
				},
				"Cancelar": function() { $(this).dialog('close'); }
			}
		});
	});
	
});

function showEmpresaFuncionarios(empresaID, nome) {
	$.ajax({
		url: 'empresa.php',
		async: false,
		data: {
			acao: 'getFuncAjax',
			empresaID: empresaID
		},
		success: function(d) {
			//d = eval(d);
			try {
				d = eval(d);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
				}
			}
			
			if (d[0].success == true) {
				var select =  d[0].funcSelect;
				
				select += '<br/><a id="linkCadFunc1" onclick="showCadFunc('+empresaID+', \''+nome+'\')">[Cadastrar Funcion&aacute;rio]</a>';
				
				$("#tdFunc1").html(select);
				
				$("#empresaFuncID1").toggleClass('selectFunc');
				
				doBind(1);
			}
		}
	});
}

function doBind(numero) {
	$("#empresaFuncID"+numero).bind('change', function() {
		var id = $(this).attr("id");
		var opt = $("#"+id+" option:selected");
		var crea = opt.val();
		
		$(this).parent().next("td").next("td").html(crea);
	});
}

function newFunc(numero) {
	//alert(numero)
	var linha = '';
	
	var linkCadFunc = $("#linkCadFunc"+numero).clone(true);
	linkCadFunc.attr("id", "linkCadFunc"+(numero+1));
	
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
	
	linha += '<tr class="c">';
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
	
	//alert($("#funcART"+numero).length)
	$("#funcART"+numero).removeAttr("onclick");
	
	//$("#tabelaEmp").append(html);
	$("#tabelaEmpr").append(linha);
	
	select.appendTo("#tdFunc"+(numero+1));
	selectTipo.appendTo("#tdTipoFunc"+(numero+1));
	
	select.children("option:selected").removeAttr("selected");
	selectTipo.children("option:selected").removeAttr("selected");
	
	//alert($("#tdTipoFunc"+(numero+1)).length)
	//$("#tdFunc"+(numero+1)).append('<br/><a onclick="showCadFunc('+empresaID+', \''+nome+'\')">[Cadastrar Funcion&aacute;rio]</a>');
	$("#tdFunc"+(numero+1)).append('<br/>');
	linkCadFunc.appendTo("#tdFunc"+(numero+1));
	
	//alert(linha)
}

function limpaEmpresa() {
	var selectTipo = $("#tipoFunc1").clone(true);
	$("#tabelaEmpr").html('');
	
	var linha = '';
	
	linha += '<tr class="c">';
	linha += '<td class="c"><b>Funcionário</b>: </td>';
	linha += '<td id="tdFunc1" class="c"></td>';
	linha += '<td class="c"><b>CREA</b>: </td>';
	linha += '<td class="c"></td>';
	linha += '<td class="c"><b>Tipo</b>: </td>';
	linha += '<td class="c" id="tdTipoFunc1"></td>';
	linha += '<td class="c"><b>ART</b>: </td>';
	linha += '<td class="c"><input type="file" name="funcART1" id="funcART1" onclick="newFunc(1)"></td>';
	
	linha += '</tr>';
	
	$("#tabelaEmpr").html(linha);
	selectTipo.appendTo("#tdTipoFunc1");
	
}

function showCadFunc(empresaID, nome) {
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
				
				//nome = HTMLEncode(nome);
				nome = escape(nome);
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
						var option = '<option value="'+crea+'">'+unescape(nome)+'</option>';
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

function showNovoContrato() {
	$("#linkNovoContrato").attr("onclick", "hideNovoContrato()");
	$("#linkNovoContrato").html("[Esconder]");
	$("#novoContrato").slideDown();
}

function hideNovoContrato() {
	$("#linkNovoContrato").attr("onclick", "showNovoContrato()");
	$("#linkNovoContrato").html("[Novo Contrato]");
	$("#novoContrato").slideUp();
}

function loadInfoGuardachuva(procID) {
	$.ajax({
		type: "GET",
		async: false,
		url: "sgo.php?acao=getProcObras&docID="+procID,
		success: function(d) {
			//d = eval(d);
			try {
				d = eval(d);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
				}
			}
			
			if (d.length <= 0) {
				$("#divInclObraGuardaChuva").html('<tr><td><b>Erro ao carregar Obras de processo Guarda-chuva.</b></td></tr>');
				$("#contrOrigemRecursosGuardaChuva").html('<tr><td><b>Erro ao carregar Finanças de processo Guarda-chuva.</b></td></tr>');
			}
			else {
				var tabelaObra = '';
				var tabelaRec = '';
				
				
				tabelaObra += '<center><h3>O contrato está vinculado às obras: </h3></center><table width="100%">';
				tabelaRec += '<center><h3>Especifique a Origem dos Recursos: </h3></center><table width="100%">';
				
				for (var i = 0; i < d[0].empreend.length; i++) {
					var eID = d[0].empreend[i].id;
					var nome = d[0].empreend[i].nome;
					
					tabelaObra += '<tr><td>Empreendimento <b>'+nome+'</b><br /></td></tr>';
					tabelaRec += '<tr class="c"><td colspan="2">Empreendimento <b>'+nome+'</b><br /></td></tr>';
					
					for (var j = 0; j < d[0].obras[eID].length; j++) {
						var obra = d[0].obras[eID][j];
						var obraID = obra.id;
						var obraNome = obra.nome;
						tabelaObra += '<tr class="c"><td class="c"><input type="checkbox" name="inclObra[]" value="'+obraID+'"> '+obraNome+'</td></tr>';
					}
					
					for (var j = 0; j < d[0].financas[eID].length; j++) {
						var rec = d[0].financas[eID][j];
						var recID = rec.id;
						var recOrigem = rec.origem;
						tabelaRec += '<tr class="c"><td class="c" style="width: 200px;">';
						tabelaRec += '<input type="checkbox" name="inclRec[]" value="'+recID+'" /> '+recOrigem+'</td>';
						tabelaRec += '<td class="c" style="width: 200px;">';
						tabelaRec += '<input id="valRec_'+recID+'" class="valRec" /></td>';
						tabelaRec += '</tr>';
					}
					
					tabelaObra += '<tr><td><br /><!-- filler--></td></tr>';
					tabelaRec += '<tr><td colspan="2"><br /><!-- filler--></td></tr>';
				}
				
				tabelaObra += '</table>';
				tabelaRec += '</table>';
				
				$('#divInclObraGuardaChuva').html(tabelaObra);
				$('#contrOrigemRecursosGuardaChuva').html(tabelaRec);
			}
		}
	});
}

function showInclObraForm(procID, tipoProc, guardachuva, unidade) {
	//alert(procID)
	$("#inclObras").val('');
	$("#inclRecursos").val('');
	
	$("input[name='inclObra[]']").each(function() {
		$(this).removeAttr("checked");
	});
	
	var div = "divInclObra";
	if (guardachuva != undefined && guardachuva == 1) {
		loadInfoGuardachuva(procID);
		div = "divInclObraGuardaChuva";
	}
	
	$("#"+div).dialog({
		resizable: true,
		height: 400,
		width: 450,
		modal: true,
		buttons: {
			"OK": function() {
				var obras = new Array();
				$("input[name='inclObra[]']:checked", $("#"+div)).each(function() {
					obras.push($(this).val());
				});
				showOrigemRecForm(procID, tipoProc, obras, guardachuva, unidade);
				$(this).dialog('close');
			},
			"Cancelar": function() { $(this).dialog('close'); }
		}
	});
}

function showOrigemRecForm(procID, tipoProc, obras, guardachuva, unidade) {
	///alert(procID)
	$("#inclObras").val('');
	$("#inclRecursos").val('');
	
	$("input[name='inclRec[]']").each(function() {
		$(this).removeAttr("checked");
		$("#valRec_"+$(this).val()).val('');
	});

	var div = "contrOrigemRecursos";
	if (guardachuva != undefined && guardachuva == 1) {
		if (obras == undefined || obras == 'undefined')
			loadInfoGuardachuva(procID);
		div = "contrOrigemRecursosGuardaChuva";
	}
	
	$("#"+div).dialog({
		resizable: true,
		height: 400,
		width: 450,
		modal: true,
		buttons: {
			"OK": function() {
				var recursos = new Array();
				$("input[name='inclRec[]']:checked", $("#"+div)).each(function() {
					var item = new Object;
					item.id = $(this).val();
					item.valor = $("#valRec_"+item.id, $("#"+div)).val();
					recursos.push(item);
					/*alert(item.id)
					alert(item.valor)
					alert($("#valRec_"+item.id, $("#"+div)).length)*/
				});
				showContrForm(procID, tipoProc, obras, recursos, unidade);
				$(this).dialog('close');
			},
			"Cancelar": function() { $(this).dialog('close'); }
		}
	});
}

/**
 * Exibe o formulário de cadastro de contrato
 * @param int procID
 */
function showContrForm(procID, tipoProc, obras, recursos, unidade) {
	//alert(procID)
	$("#inclObras").val('');
	$("#inclRecursos").val('');
	
	if (procID == undefined)
		return;
	
	$("#tipoProc").val(tipoProc);
	
	$(".hid").hide();
	
	// esconde todos as outras seções, exceto menu superior
	$("div.boxCont").each(function() {
		if ($(this).attr("id") != null && $(this).attr("id") != "c1")
			$(this).hide();
	});
	
	$("#valorProj").removeAttr("disabled");
	$("#valorMaoObra").removeAttr("disabled");
	$("#valorMaterial").removeAttr("disabled");
	
	// seta o valor do id do processo
	$("#numProcContr").val(procID);
	//alert($("#numProcContr").val())
	$("#numProcContr").hide();
	$("#numProcContr").prev("b").hide();
	
	// seta o valor da unidade
	$("#unOrg").val(unidade);
	//alert($("#unOrg").val())
	$("#unOrg").hide();
	$("#unOrg").prev("b").hide();
	
	if (tipoProc == 'contrProj') {
		$("#valorProj").removeClass('skipErase');
		$("#valorProj").removeAttr("disabled");
		$("#valorProj").val('');
		
		/*$("#valorMaoObra").val(0);*/
		$("#valorMaoObra").addClass('skipErase');
		$("#valorMaterial").addClass('skipErase');
		$("#valorMaoObra").attr("disabled", "disabled");
		$("#valorMaterial").attr("disabled", "disabled");
		$("#valorMaoObra").val('');
		$("#valorMaterial").val('');
	}
	else {
		$("#valorMaoObra").removeClass('skipErase');
		$("#valorMaoObra").removeAttr("disabled");
		$("#valorMaoObra").val('');
		$("#valorMaterial").removeClass('skipErase');
		$("#valorMaterial").removeAttr("disabled");
		$("#valorMaterial").val('');
		
		$("#valorProj").addClass('skipErase');
		$("#valorProj").attr("disabled", "disabled");
		$("#valorProj").val('');
		
	}
	
	//hideContrCad();
	
	//alert(obras)
	//alert(recursos)
	if (obras == null || obras == undefined || obras == "" || obras.length <= 0) {
		$("#inclObras").val('');
	}
	else {
		$("#inclObras").val(obras.toString());
	}
	//console.log(recursos); 
	$("#inclRecursos").val(JSON.stringify(recursos));
	
	//hideNovoContrato();
	
	// exibe seção
	$("#c3").slideDown();
	
	// inicializa autosave se ele estiver incluso
	//alert($.isFunction(openForm))
	if ($.isFunction(openForm)) {
		openForm("cadForm");
	}
	
	//alert($("#numProcContr").val())
}

/*function hideContrCad() {
	$("#contrIncludeObra").hide();
	$("#contrOrigemRecursos").hide();
	$("#inclObraSubmit").removeAttr("onclick");
	$("#inclRecursosSubmit").removeAttr("onclick");
}*/

function calculaVigencia() {
	/*
	 * Esta função calcula a vigência do contrato.
	 * Lembre-se que ao calcular datas com javascript, infelizmente
	 * meses são indexados à partir do zero
	 * e timestamp é tratado em milisegundos, e não em segundos
	 */
	var dataAssinatura = $("#dataAssinatura").val();
	var dataReuniao = $("#dataReuniao").val();
	var prazoContr = $("#prazoContr").val();
	
	if (dataAssinatura == undefined || dataAssinatura == "") {
		if (dataReuniao == undefined || dataReuniao == "") {
			$("#vigenciaContr").val("");
			return;	
		}
	}
	if (prazoContr == undefined || prazoContr == "") {
		$("#vigenciaContr").val("");
		return;
	}
	
	if (dataReuniao == undefined || dataReuniao == "") { 
		var dataArray1 = dataAssinatura.split("/");
	}
	else {
		var dataArray1 = dataReuniao.split("/");
	}
	

	var data1 = new Date(dataArray1[2],dataArray1[1]-1,dataArray1[0],0,0,0).getTime();
	var data2 = (prazoContr-1) * (60 * 60 * 24 * 1000);
	
	var dataVigencia = new Date((data1 + data2));
	var ano = dataVigencia.getFullYear();
	var mes = dataVigencia.getMonth()+1;
	var dia = dataVigencia.getDate();
	
	var dataFinal = dia+"/"+mes+"/"+ano;
	$("#vigenciaContr").val(dataFinal);
	
}

function calculaTermino() {
	/*
	 * Esta função calcula a data de termino do contrato.
	 * Lembre-se que ao calcular datas com javascript, infelizmente
	 * meses são indexados à partir do zero
	 * e timestamp é tratado em milisegundos, e não em segundos
	 */
	var inicio = $("#inicioProjObra").val();
	var prazo = $("#prazoProjObra").val();
	
	if (inicio == undefined || inicio == "") {
		$("#dataTermino").val("");
		return;
	}
	
	if (prazo == undefined || prazo == "") {
		$("#dataTermino").val("");
		return;
	}
	
	var dataArray1 = inicio.split("/");
	var data1 = new Date(dataArray1[2],dataArray1[1]-1,dataArray1[0],0,0,0).getTime();
	var data2 = (prazo-1) * (60 * 60 * 24 * 1000);
	
	var dataTermino = new Date((data1 + data2));
	var ano = dataTermino.getFullYear();
	var mes = dataTermino.getMonth()+1;
	var dia = dataTermino.getDate();
	
	var dataFinal = dia+"/"+mes+"/"+ano;
	$("#dataTermino").val(dataFinal);
}

function calculaInicio() {
	var data = $("#dataReuniao").val();
	//alert(data);
	//alert('1');
	
	$.get('sgd.php', {
		acao: 'getProxDiaUtil',
		data: data
	}, function(d) {
		try {
			d = eval(d);
		}
		catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message + " Retorno: "+d);
			}
		}
		//alert(d[0].data)
		//alert(typeof d[0].success)
		if (d[0].success == true) {
			$("#inicioProjObra").val(d[0].data);
		}
		else {
			alert("Erro ao calcular a data de Inicio do Projeto/Obra. ");
		}
		
	});
}

function calculaValorContr(campo) {
	if (campo.hasClass('noedit'))
		return;
	
	var valMaoObra = 0;
	var valMaterial = 0;
	
	if (campo.attr("id") == 'valorProj') {
		$("#valorTotal").val(campo.val());
	}
	else {
		if ($("#valorMaoObra").val() != undefined && $("#valorMaoObra").val() != null) {
			var aux = $("#valorMaoObra").val();
			aux = aux.replace(".", "");
			aux = aux.replace(",", ".");
			// cast to float: +()
			valMaoObra = +(aux);
		}
		if ($("#valorMaterial").val() != undefined && $("#valorMaterial").val() != null) {
			var aux = $("#valorMaterial").val();
			aux = aux.replace(".", "");
			aux = aux.replace(",", ".");
			// cast to float: +()
			valMaterial = +(aux);
		}
		
		var soma = valMaoObra + valMaterial;
		
		$("#valorTotal").val(("" + soma.toFixed(2)).replace(".", ","));
	}
}

function valida_cnpj(cnpj) {
	var numeros, digitos, soma, i, resultado, pos, tamanho, digitos_iguais;
	digitos_iguais = 1;
	if (cnpj.length < 14 && cnpj.length < 15)
	      return false;
	for (i = 0; i < cnpj.length - 1; i++)
	      if (cnpj.charAt(i) != cnpj.charAt(i + 1))
	            {
	            digitos_iguais = 0;
	            break;
	            }
	if (!digitos_iguais)
	      {
	      tamanho = cnpj.length - 2
	      numeros = cnpj.substring(0,tamanho);
	      digitos = cnpj.substring(tamanho);
	      soma = 0;
	      pos = tamanho - 7;
	      for (i = tamanho; i >= 1; i--)
	            {
	            soma += numeros.charAt(tamanho - i) * pos--;
	            if (pos < 2)
	                  pos = 9;
	            }
	      resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
	      if (resultado != digitos.charAt(0))
	            return false;
	      tamanho = tamanho + 1;
	      numeros = cnpj.substring(0,tamanho);
	      soma = 0;
	      pos = tamanho - 7;
	      for (i = tamanho; i >= 1; i--)
	            {
	            soma += numeros.charAt(tamanho - i) * pos--;
	            if (pos < 2)
	                  pos = 9;
	            }
	      resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
	      if (resultado != digitos.charAt(1))
	            return false;
	      return true;
	      }
	else
	      return false;
} 

function validaReuniao() {
	var data = $("#dataReuniao").val();
	var dataSign = $("#dataAssinatura").val();
	
	if (data == "" || dataSign == "") {
		calculaVigencia();
		return;
	}
	
	arrayData = data.split("/");
	arraySign = dataSign.split("/");
	
	if (arrayData.length != 3 || arraySign.length != 3) {
		alert("Formato das datas inválido. Por favor, preencha as datas no formato: dd/mm/aaaa");
		$("#dataReuniao").datepicker("hide");
		$("#dataAssinatura").datepicker("hide");
		return;
	}
	
	
	dataReuniao = new Date(arrayData[2], arrayData[1]-1, arrayData[0], 0, 0, 0);
	dataAssinatura = new Date(arraySign[2], arraySign[1]-1, arraySign[0], 0, 0, 0);
	
	var reuniao = dataReuniao.getTime();  
	var assinatura = dataAssinatura.getTime();
	
	if (reuniao < assinatura) {
		$("#dataReuniao").val('');
		alert("Data de Assinatura deve ser anterior a Data de Reuniao.");
		$("#dataReuniao").datepicker("hide");
		$("#dataAssinatura").datepicker("hide");
		return;
	}
}