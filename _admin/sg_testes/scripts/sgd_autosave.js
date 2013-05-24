/**
 * Variavel Global que controla se o doc é a primeira IT a ser mostrada na tela
 * Esta variável é necessária pois os forms de IT na tela de empreendimento são carregadas dinamicamente por Ajax
 * @var boolean FirstIT
 */
var FirstIT = true;

/**
 * Timer do autosave
 * @var timer _autoSaveTimer
 */
var _autoSaveTimer;

/**
 * Verifica se há documento salvado automaticamente pendente e mostra dialog, em caso afirmativo.
 * Inicia o timer de auto-save dado escolha do usuário.
 * @param string formulario ID do formulario que deverá ser 'auto-salvado'
 * @returns timer
 */
function openForm(formulario) {
	// pega as veriáveis da URL
	var urlVars = getUrlVars(); 

	// se a ação restaurar não estiver definida ou for diferente de true, significa que o usuário abriu novo formulário de doc
	if (urlVars['restaurar'] == undefined || urlVars['restaurar'] == null || urlVars['restaurar'] == false || urlVars['acao'] == 'restaurarIT') {
		// verifica se há documento auto-salvado pendente
		$.get('autosave.php', {
			'acao': 'check'
		}, function(d) {
			try {
				d = eval(d);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message + " Retorno: " + d);
					//alert(d);
				}
			}
			
			// se há documento pendente,
			if (d[0].hasDocument == true) {
				
				// se o doc pendente é do tipo 'it' e acao não for 'novo' ou 'cad' e FirsIT for true, esta é a 1a it. 
				if ((urlVars['acao'] != 'cad' && urlVars['acao'] != 'novo') && d[0].tipoDoc == 'it' && FirstIT == true) {
					// desmarca FirstIT e não faz mais nada.
					FirstIT = false;
					return;
				}
				// caso contrário, mostra dialog
				
				// cria div de dialog
				var div = $("#divAutoSaveAlert");
				if (div.length <= 0) {
					$("body").append('<div id="divAutoSaveAlert" title="Alerta!"></div>');
					div = $("#divAutoSaveAlert");
				}
				
				// seta html do div
				var html = '';
				html += '<center><span style="color: red; font-size: 20px;"><b>ATEN&Ccedil;&Atilde;O</b></span>: </center><br />';
				html += 'Voc&ecirc; possui um documento do tipo <b>"'+d[0].tipoDocLabel+'"</b> ainda <b>n&atilde;o</b> salvo.<br />';
				html += 'O SiGPOD fez uma c&oacute;pia de seguran&ccedil;a para voc&ecirc; no dia <b>'+d[0].data+'</b>. ';
				html += 'Se voc&ecirc; prosseguir nesta tela, a c&oacute;pia de seguran&ccedil;a ser&aacute; perdida e <b>N&Atilde;O SER&Aacute; POSS&Iacute;VEL</b> restaur&aacute;-la. ';
				html += 'Voc&ecirc; <b>tem certeza</b> que deseja continuar ou prefere que o SiGPOD tente restaurar o documento para voc&ecirc; ?';
				div.html(html);
				
				// mostra dialog
				div.dialog({ 
					resizable: false,
					height: 300,
					width: 400,
					modal: true,
					buttons: {
						"DESCARTAR Documento": function() {
							if (confirm("Você TEM CERTEZA ? Última chance!")) {
								// descarta doc salvo pendente
								$.get('autosave.php', {
									'acao': 'descartar'
								}, function() {});
								
								// fecha dialog
								$(this).dialog('close');
								
								// cria timer
								_autoSaveTimer = newTimer(formulario);
								bindAutoSave(formulario);
							}
						},
						"Restaurar Cópia de Segurança": function() {
							var urlVarsAtuais = getUrlVars();
							var variaveis = jQuery.parseJSON(d[0].urlVars);
							
							// compara as variaveis 'acao' atuais com as salvas
							if (urlVarsAtuais['acao'] == variaveis['acao'] && urlVarsAtuais['acao'] != undefined) {
								if (urlVarsAtuais['acao'] == 'cad' || urlVarsAtuais['acao'] == 'novo') { // variaveis de cadastro ou criação novo
									// se os tipos de doc forem iguais, não necessita redirecionar página. Apenas restaura doc
									if (urlVarsAtuais['tipoDoc'] == d[0].tipoDoc) {
										restaurarDoc(formulario, false);
										// cria binds para verificação de alteração do form
										bindAutoSave(formulario);
										$(this).dialog('close');
										return;
									}
									else {
										// redireciona para página com form do doc a ser restaurado
										montaRedirect(variaveis);
									}
								}
								else { // variáveis para empreendimento
									if (d[0].tipoDoc != 'it') { // se não for it (no caso, é contrato)
										// se empreendID for igual ao empreendID da IT salva, restaura
										if (urlVarsAtuais['empreendID'] == variaveis['empreendID']) {
											restaurarDoc(formulario, false);
											bindAutoSave(formulario);
											$(this).dialog('close');
											return;
										}
										else {
											// senão, redirect
											montaRedirect(variaveis);
										}
									}
									else {
										// se for IT, redireciona
										var url = 'sgo.php?acao=restaurarIT';
										var win = window.open(url, 'obra','width='+screen.width*0.95+',height='+screen.height*0.9+',scrollbars=yes,resizable=yes');
										win.focus();
									}
								}
							}
							else {
								// redireciona caso variavel acao não esteja setada ou seja diferente da salva
								montaRedirect(variaveis);
								$(this).dialog('close');
							}
						},
						"Continuar e não descartar o documento antigo": function() {
							// cria campo auxiliar flag para identificar que o usuário quer manter o doc autosalvado automaticamente
							if ($("#_keepAutoSave").length <= 0) {
								$("#"+formulario).append('<input type="hidden" id="_keepAutoSave" name="_keepAutoSave" value="1">');
							}
							else {
								$("#_keepAutoSave").val(1);
							}
							
							alert("O Documento atual não será salvo automaticamente para preservar o antigo.");
							$(this).dialog('close');
						}
					},
					open: function() {
						// seta focus no botão de restaurar
						$(this).parent().find('button:nth-child(2)').focus();
					}
				});
				
			} // se não há documento pendente
			else {
				// cria timer e faz bind de verificação de alteração de formulário
				_autoSaveTimer = newTimer(formulario);
				bindAutoSave(formulario);
				return _autoSaveTimer;
			}
			
			// se acao for de restaurar IT, troca FirstIT para false
			if (urlVars['acao'] == 'restaurarIT')
				FirstIT = false;
			
		});
	} // se for redirect de restaurar, cria timer e binds
	else {
		_autoSaveTimer = newTimer(formulario);
		bindAutoSave(formulario);
		return _autoSaveTimer;
	}
}

/**
 * Monta url para redirect de restauração de Doc e faz o redirect
 * @param array variaveis variaveis GET da url necessárias para geração do form e restauração do doc
 */
function montaRedirect(variaveis) {
	var url = '';
	var i = 0;
	// percorre as variáveis de url salvas e monta url
	for (var propriedade in variaveis) {
		if (!variaveis.hasOwnProperty(propriedade)) continue;
		
		if (i != 0) url += '&';
		url += propriedade + '=' + variaveis[propriedade];
		i++;
	}
	
	// adiciona variável de restaurar
	if (i != 0) url += '&';
	url += 'restaurar=true';
	
	// seta nome da janela
	var winName = 'doc';
	
	// se for acao correspondente a criação de doc fora de empreendimento, seta página php para sgd.php
	if (variaveis.hasOwnProperty('acao') && (variaveis['acao'] == 'cad' || variaveis['acao'] == 'novo')) {
		url = 'sgd.php?' + url;
	} // se não, sgo.php
	else {
		url = 'sgo.php?' + url;
		// troca nome da janela para obra
		winName = 'obra';
	}
	
	// se for nova janela, abre nova janela
	if (variaveis.hasOwnProperty('novaJanela') || winName == 'obra') {
		var win = window.open(url, winName,'width='+screen.width*0.95+',height='+screen.height*0.9+',scrollbars=yes,resizable=yes');
		win.focus();
		
		//window.location = 'index.php';
	}
	else {
		// senão, apenas faz redirect
		window.location = url;
	}
}

/**
 * Cria novo Timer de Auto-save
 * @param formulario
 * @returns timer
 */
function newTimer(formulario) {
	// se formulario for nulo, não faz nada
	if (formulario == undefined || formulario == null) {
		return null;
	}
	
	var intervalo =  30 * 1000; // 30 sec
	
	// cria timer
	var ret = setTimeout("autosaveForm('"+formulario+"')", intervalo);
	
	// retorna
	return ret;
}


/**
 * Realiza o autosave do formulário de novo doc
 * @param string formulario ID do formulário
 * @returns timer
 */
function autosaveForm(formulario) {
	if (typeof CKEDITOR != "undefined") {
		// forca a atualizacao dos campos dos CKEDITOR
		for ( instance in CKEDITOR.instances ) {
	        CKEDITOR.instances[instance].updateElement();
	        
	        if (CKEDITOR.instances[instance].checkDirty()) {
	        	// se foi alterado o conteudo dos campos CKEDITOR, seta flag de form alterado e limpa bit de Dirty do ckeditor
	        	$("#"+formulario).data('changed', true);
	        	CKEDITOR.instances[instance].resetDirty();
	        }
		}
	}
	
	//$('input:focus').change();
	
	// se o formulário não foi alterado, apenas cria novo timer e não faz mais nada
	if ($("#"+formulario).data('changed') == undefined || $("#"+formulario).data('changed') == null || $("#"+formulario).data('changed') != true) {
		_autoSaveTimer = newTimer(formulario);
		return _autoSaveTimer;
	}
	
	// limpa flag de form alterado
	$("#"+formulario).removeData('changed');
	
	// pega variáveis GET da url
	var urlVars = getUrlVars();
	// inicialização de variáveis
	var conteudo;
	var form = $("#"+formulario);
	var tipoAcao = $("#action", form).val();
	var tipoDoc = $("#tipoDocCad", form).val();
	
	if (urlVars['acao'] == 'cadProcSap') {
		tipoAcao = 'cadProcSap';
	}
	
	// se doc não for IT, salva variáveis e ignora "restaurar" para não adicionar novamente
	if (tipoDoc != 'it') {
		var parametrosUrl = {};
		for (var i = 0; i < urlVars.length; i++) {
			if (urlVars[i] == 'restaurar') continue;
			parametrosUrl[urlVars[i]] = urlVars[urlVars[i]]; 
		}
	}
	else {
		// se for it, a única variável necessária é 'acao' = 'restaurarIT'
		var parametrosUrl = {};
		parametrosUrl['acao'] = 'restaurarIT';
	}
	
	// se for formulário de cadastro
	if (parametrosUrl['acao'] == 'cad' || (parametrosUrl['acao'] == 'verContratos')) {
		copiaCamposBusca();
	}
	
	// se não encontrou o formulário, retorna
	if (form.length <= 0) {
		return false;
	}
	
	// seleciona elementos do form que serão salvos
	var textInput = $("textarea, input[type=text], input[type=hidden]", form);
	var select = $("select", form);
	var radio = $("input:radio[checked=checked]", form);
	var checkbox = $("input[type=checkbox][checked=checked]", form);
	var hasFile = false;
	if ($("input:file").filter('[value!=""]').length > 0) {
		//alert('true');
		hasFile = true;
	}
	
	var arrayText = {};
	var contador = 0;
	// percorre os inputs e salva id, nome, valor
	$.each(textInput, function() {
		var nome = $(this).attr("name");
		var id = $(this).attr("id");
		var valor = htmlentities($(this).val(), 'ENT_QUOTES');
		//valor = valor.replace("\n", "");
		//alert(valor);
		
		//alert(id)
		
		/*if (id == "conteudo") {
			valor = unescape(valor);
			//alert(valor);
			valor = htmlentities(valor, 'ENT_QUOTES');
			//valor = escape(valor);
			//alert(valor);
		}*/
		
		//if (id == "despacho") alert(valor);
		
		if (id == undefined || id == "") {
			var tag = $(this).get(0).tagName;
			var type = $(this).attr("type");
			arrayText[contador] = { 'id': id, 'nome': nome, 'valor': valor, 'tag': tag, 'type': type };
		}
		else {
			arrayText[contador] = { 'id' : id, 'nome': nome, 'valor': valor };
		}
		
		//alert(arrayText[contador]['valor']);
		
		contador++;
	});
	
	var arraySelect = {};
	contador = 0;
	// percorre selects e salva id, nome, valor
	$.each(select, function() {
		var nome = $(this).attr("name");
		var id = $(this).attr("id");
		//var valor = escape($(this).val());
		var valor = htmlentities($(this).val(), 'ENT_QUOTES');
		//valor = valor.replace("\n", "");
		
		if (id == undefined || id == "") {
			var tag = $(this).get(0).tagName;
			var type = $(this).attr("type");
			arraySelect[contador] = { 'id': id, 'nome': nome, 'valor': valor, 'tag': tag, 'type': type };
		}
		else {
			arraySelect[contador] = { 'id' : id, 'nome': nome, 'valor': valor };
		}
		
		contador++;
	});
	
	var arrayRadio = {};
	contador = 0;
	// percorre radios e salva id, nome, valor
	$.each(radio, function() {
		var nome = $(this).attr("name");
		var id = $(this).attr("id");
		var valor = escape($(this).val());
		//valor = valor.replace('\n', "");
		
		if (id == undefined || id == "") {
			var tag = $(this).get(0).tagName;
			var type = $(this).attr("type");
			arrayRadio[contador] = { 'id': id, 'nome': nome, 'valor': valor, 'tag': tag, 'type': type };
		}
		else {
			arrayRadio[contador] = { 'id' : id, 'nome': nome, 'valor': valor };
		}
		
		contador++;
	});
	
	var arrayCheckbox = {};
	/*$.each(radio, function() {
		var nome = $(this).attr("name");
		var id = $(this).attr("id");
		var valor = escape($(this).val());
		
		if (id == undefined || id == "") {
			var tag = $(this).attr("tagName");
			var type = $(this).attr("type");
			arrayRadio[nome] = { 'id': id, 'valor': valor, 'tag': tag, 'type': type };
		}
		else {
			arrayRadio[nome] = { 'id' : id, 'valor': valor };
		}
	});*/
	
	var conteudo = { 'text': arrayText, 'select': arraySelect, 'radio': arrayRadio, 'checkbox': arrayCheckbox, 'hasFile': hasFile };
	
	// envia dados
	$.post('autosave.php?acao=salvar', {
		'tipoDoc': tipoDoc,
		'tipoAcao': tipoAcao,
		'conteudo': JSON.stringify(conteudo),
		'urlVars': JSON.stringify(parametrosUrl)
	}, function(d) {
		try {
			d = eval(d);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message + " Retorno: " + d);
				//alert(d);
			}
		}
		
		if (d[0].success == true) {
			// se sucesso, cria novo timer
			_autoSaveTimer = newTimer(formulario);
			
			var data = new Date();
			
			// mostra div de notificação
			var div = '<div id="divStatusAutoSave" class="alert" style="width: 99%;"></div>';
			
			var html = '<center><i>C&oacute;pia de Segurança salva em ';
			html += data.getDate()+'/'+(data.getMonth()+1)+'/'+data.getFullYear()+' &agrave;s '
			html += data.getHours()+':'+data.getMinutes()+':'+data.getSeconds()+'</i></center>';
			
			if ($("#divStatusAutoSave").length <= 0) {
				$("#"+formulario).before(div);
			}
			
			$("#divStatusAutoSave").html(html);
			$("#divStatusAutoSave").show();
			
			// retorna
			return _autoSaveTimer;
		} else {
			alert("Auto-save com problemas. O documento não será salvo automaticamente. Erro: "+d[0].feedback);
			return null;
		}
	});
}

/**
 * Restaura dados do formulário do documento auto-salvado
 * @param string formulario ID do formulário
 * @param boolean aoCarregar flag que indica se a restauração deverá ser feita no evento OnLoad
 * @returns
 */
function restaurarDoc(formulario, aoCarregar) {
	var form = $("#"+formulario);
	//alert(form)
	
	if (aoCarregar == undefined || aoCarregar == null) {
		aoCarregar = true;
	}
	
	$.get('autosave.php', {
		'acao': 'get'
	}, function(d) {
		try {
			//alert(d);
			//d = jQuery.parseJSON(d);
			d = eval(d);
		} catch(e) {
			alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message + " Retorno: "+d);
			//alert(d);
			return;
		}
		//alert(d[0])
		d = d[0];		
		//alert(d);
		// se há documento pendente, (verificação de segurança
		if (d.hasDocument) {
			// pega campos
			var conteudo = d.conteudo;
			var text = conteudo['text'];
			var select = conteudo['select'];
			var radio = conteudo['radio'];
			var hasFile = conteudo['hasFile'];
			
			//alert(hasFile)
			if (hasFile) {
				alert("O SiGPOD não pode restaurar os campos de anexação de arquivos. Por favor, não se esqueça de anexá-los novamente.");
			}
			
			// se o documento for do tipo contrato, seta o tipoProc e mostra formulário
			if (d.tipoDoc == 'contr') {
				for (var indice in text) {
					if (text.hasOwnProperty(indice)) {
						if (text[indice]['id'] == "tipoProc") {
							var campo = text[indice];
							// só passa o valor do tipoProc, uma vez que os outros campo serão preenchidos mais abaixo
							showContrForm(0, campo['valor'], '', '', '');
							$(".hid").slideDown();
						}
					}
				}
			}
			
			// percorre campos select
			for (var indice in select) {
				if (select.hasOwnProperty(indice)) {
					var campo = select[indice];
					var id = campo['id'];
					
					// seta valor do campo e seleciona opção correspondente
					if (id != undefined && id != null) {
						//$("#"+id+" option:selected").removeAttr("selected");
						//$("#"+id+" option:selected").removeAttr("selected");
						
						$("#"+id+" option[value='"+unescape(campo['valor'])+"']").attr("selected", "selected");
						$("#"+id).val(unescape(campo['valor']));
						
						// tratamento para select de despacho... seta option e value e dispara evento de change neste campo
						if (id == "para") {
							//alert(unescape(campo['valor']));
							//alert($("#"+id+" option[value='"+unescape(campo['valor'])+"']").length);
							if ($("#para option:selected").val() != undefined && $("#para option:selected").val() != null && $("#para option:selected").val() != "--Selecione--") {
								$("#"+id).change();
							}
						}
						
						// tratamento para cadastro de contrato (campos de funcionário)
						if (id.indexOf("empresaFuncID") != -1) {
							var numero = id.substring("empresaFuncID".length, id.length);
							// cria campos para funcionários subsequentes
							newFunc(parseInt(numero));
							
							// seta crea
							if (campo['valor'] != null && campo['valor'] != undefined && campo['valor'] != 'null' && campo['valor'] != 'undefined' && campo['valor'] != '')
								$("#"+id).parent().next("td").next("td").html(campo['valor']);
						}
						
					}
					else {
						var selector = campo['tag'];
						selector += "[name="+campo['nome']+"]";
						
						//$(selector+" option:selected").removeAttr("selected");
						//$(selector+" option:selected").removeAttr("selected");
						$(selector+" option[value='"+unescape(campo['valor'])+"']").attr("selected", "selected");
						$(selector).val(campo['valor']);
					}
					
					// se for campo de empresa, cria os campos do 1o funcionário
					if (d.tipoDoc == 'contr' && id == 'empresaID') {
						var nome = $("#empresaID option:selected").html();
						showEmpresaFuncionarios(campo['valor'], nome);
					}
					
				}
			} /* for select */
			
			// percorre campos text
			for (var indice in text) {
				if (text.hasOwnProperty(indice)) {
					var campo = text[indice];
					var id = campo['id'];

					//alert(id);
					
					if (id != undefined && id != null) {
						// se for campo de CKEDITOR
						if ($("#"+id).hasClass('ckeditor')) {
							var ckeditorVal = html_entity_decode(html_entity_decode(campo['valor']));
							// seta valores dos campos CKEDITOR no evento onLoad
							if (aoCarregar) {
								CKEDITOR.instances[campo['nome']].on('instanceReady', function() {
									this.setData(unescape(ckeditorVal));
									this.resetDirty();
								});
								
								// seta dados fora do evento. Necessário por que javascript não é síncrono D:
								CKEDITOR.instances[campo['nome']].setData(unescape(ckeditorVal), function() { this.checkDirty(); });
								CKEDITOR.instances[campo['nome']].resetDirty();
							}
							else {
								// seta dados do campo CKEDITOR
								CKEDITOR.instances[campo['nome']].setData(unescape(ckeditorVal), function() { this.checkDirty(); });
								CKEDITOR.instances[campo['nome']].resetDirty();
							}
						}
						else {
							//alert(id)
							// se não, apenas seta valor
							$("#"+id).val(html_entity_decode(campo['valor']));
						}
					}
					else {
						var selector = campo['tag'];
						selector += "[name="+campo['nome']+"]";
						$(selector).val(unescape(campo['valor']));
					}
				}
			} /* for text */
			
			// percorre radios e checa radios com valores corretos
			for (var indice in radio) {
				if (radio.hasOwnProperty(indice)) {
					var campo = radio[indice];
					var nome = campo['nome'];
					var radio = $("input:radio[name="+nome+"]");
					radio.filter("[value="+campo['valor']+"]").attr("checked", "checked");
				}
			} /* for radio */
			
			/*if ($("#_docRestaurado").length <= 0) {
				$("#"+formulario).append('<input type="hidden" id="_docRestaurado" name="_docRestaurado" value="1">');
			}
			else {
				$("#_docRestaurado").val(1);
			}*/
				
			
			// copia campos de busca para form de busca para cadastro de docs
			try {
				//d.urlVars = jQuery.parseJSON(d.urlVars);
				d.urlVars = eval(d.urlVars);
				if (d.urlVars['acao'] != undefined && (d.urlVars['acao'] == 'cad' || (d.urlVars['acao'] == 'verContratos'))) {
					// se for formulário de cadastro, seta os campos de busca
					var camposBusca = $("#camposBusca").val();
					camposBusca = camposBusca.split(',');
					
					var contador = 0;
					// percorer campos de busca e seta os valores dos campos
					$.each(camposBusca, function() {
						var campo = $("#"+camposBusca[contador]);
						if (campo.get(0).tagName.toUpperCase() == "select".toUpperCase()) { 
							$("#"+camposBusca[contador]+" option:selected").removeAttr("selected");
							$("#"+camposBusca[contador]+" option").filter("[value='"+unescape($("#_"+camposBusca[contador]).val())+"']").attr("selected", "selected");
							
							if (camposBusca[contador] == 'numero_pr_tipo' && unescape($("#_"+camposBusca[contador]).val()) == "F") {
								$("#numero_pr_un").hide();
							} 
						}
						else {
							$("#"+camposBusca[contador]).val($("#_"+camposBusca[contador]).val());
						}
						
						contador++;
					});
					
					$("div .hid").show();
				}
				if (d.urlVars['acao'] == "restaurarIT") {
					$("#novoForm" + $("#etapaTipoID").val() + $("#faseTipoID").val()).show();
				}
			} catch(e) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
				return;
			}			
			
			bindAutoSave(formulario);
			$("#"+formulario).removeData('changed');
			return;
		}
		else {
			alert("Erro: Não foi encontrado nenhum documento a ser recarregado. Contacte seu administrador.");
		}
	});
	
	bindAutoSave(formulario);
	$("#"+formulario).removeData('changed');
	_autoSaveTimer = newTimer(formulario);
	return _autoSaveTimer;
}


/**
 * Faz bind dos campos do formulário para setar flag de form alterado
 * @param string formulario ID do formulário
 */
function bindAutoSave(formulario) {
	$("#"+formulario+" :input").bind('input', function() {
		  $(this).closest('form').data('changed', true);
	});
	
	$("#"+formulario+" :input").bind('keydown', function() {
		  $(this).closest('form').data('changed', true);
	});
	
	/*for (var i in CKEDITOR.instances) {
		CKEDITOR.instances[i].on('key', function() {
			//alert('teste')
			$(this).closest('form').data('changed', true);
		});
	}*/
}