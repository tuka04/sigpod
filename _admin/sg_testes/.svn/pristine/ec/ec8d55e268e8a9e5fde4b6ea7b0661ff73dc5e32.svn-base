$(document).ready(function(){
	$("#nome").keyup(function(e){
		sugereObra();
	});
});


function sugereObra() {
	if($("#nome").val().length >= 3){
		$.get('sgo_busca.php',{
			tipoBusca:    "sugestao", 
			nome:         escape($("#nome").val()),
			unOrg:        ''
		}, function(data){
			//var obras = eval(data);
			try {
				obras = eval(data);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
				}
			}
			
			if(obras.length == 0) {
				$("#sugestoesObra").show();
				$("#sugestoesObra").html('<b>Nenhuma obra encontrada</b><br />');
			} else {
				$("#sugestoesObra").show();
				$("#sugestoesObra").html('Obras encontradas:<br /><table style="width:100%" id="obraNomes"></table>');
				
				$.each(obras,function(i){
					$("#obraNomes").append('<tr class="c"><td class="c">'+obras[i].codigo+' - '+obras[i].nome+' ('+obras[i].unOrg.sigla+')</td><td class="c" style="width: 125px;"><a href="javascript:void(0)" id="link_'+obras[i].id+'" onclick="atribObra('+obras[i].id+',\''+obras[i].nome+'\')">Atribuir a esta obra</a></td></tr><br />');
				});
			}
		});
	}
}


function atribEmpreend(empreendID,empreendNome,desfazer){
	//alert($("#obraAtual").text());
	if ((desfazer == false) && ($("#obraAtual").text() != "Este documento não está relacionado a nenhum empreendimento.")) {
		if ($("#guardachuva").val() == 0) {
			if (confirm("Este processo já está associado a um Empreendimento. Clique em OK se deseja realmente continuar.") == false) return;
		}
		else {
			if (confirm("Este é um processo guarda-chuva. Este empreendimento será adicionado à lista de empreendimentos aos quais este processo está atribuido. Clique em OK se deseja realmente continuar.") == false) return;
		}
	}
	$("#link_"+empreendID).html("Aguarde...");
	//var onclick = $("#link_"+obraID).attr("onclick");
	$("#link_"+empreendID).attr("onclick","void(0)");
	var getVars = getUrlVars();	
	
	var remover = 0;
	if (desfazer == true) remover = 1;
	
	$.get('sgd.php',{
		acao  : 'atribEmpreendAjax',
		docID : getVars['docID'],
		empreendID: empreendID,
		desfazer: remover
	},function(d){
		//var fb = eval(d);
		try {
			fb = eval(d);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
			}
		}
		
		if(fb[0].success == true) {
			$("#sugestoesObra").html("Atribui&ccedil;&atilde;o bem sucedida!");
			if (!desfazer) $("#obraAtual").html('Este documento foi relacionado ao empreendimento: <a href="javascript:void(0)" onclick="javascript:window.open(\'sgo.php?acao=verEmpreend&amp;empreendID='+empreendID+'\',\'obra_det\',\'width='+screen.width*newWinWidth+',height='+screen.height*newWinHeight+',scrollbars=yes,resizable=yes\').focus()">'+empreendNome+'</a> <a href="javascript:void(0)" onclick="javascriot:atribEmpreend('+empreendID+',\''+empreendNome+'\',1)">[desfazer]</a>');
			else $("#obraAtual").html('Atribui&ccedil;&atilde;o desfeita!'); 
			$("#link_"+empreendID).html("Sucesso!");
			$("#table_sugestoes").hide();
			$("#empreendMiniBusca").hide();
			if(empreendID == 0) {
				location.reload();
			}
		} else {
			if (fb[0].duplicado == true) {
				$("#link_"+empreendID).html("Erro. Já atribuido.");
			}
			else {
				$("#link_"+empreendID).html("Erro. Tentar novamente.");
			}
			$("#link_"+empreendID).attr("onclick","atribEmpreend("+empreendID+","+empreendNome+")");
		}
	});
}

function atribObra(obraID, desfazer){
	$("#link_"+obraID).html("Aguarde...");
	$("#link_"+obraID).attr("onclick","void(0)");
	var getVars = getUrlVars();	
	
	$.get('sgd.php',{
		'acao'    : 'atribObraAjax',
		'docID'   : getVars['docID'],
		'obraID'  : obraID,
		'desfazer': desfazer
	},function(d){
		try {
			var fb = eval(d);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
			}
		}
		
		if(fb[0].success == true) {
			if(!desfazer){
				var obraNome = $("#nome_obra_"+obraID).html();
				$("#obraAtual").append('<a id="link_obra_'+obraID+'" href="javascript:void(0)" onclick="javascript:window.open(\'sgo.php?acao=verObra&amp;obraID='+obraID+'\',\'obra_det\',\'width='+screen.width*newWinWidth+',height='+screen.height*newWinHeight+',scrollbars=yes,resizable=yes\').focus()" id="link_obra_'+obraID+'">'+obraNome+'</a> <a href="javascript:void(0)" onclick="javascriot:atribObra('+obraID+',1)" id="desfazer_obra_'+obraID+'">[desfazer]</a><br/>');
				$("#link_"+obraID).html("Sucesso!");
				$("#empreendMiniBusca").hide();
				$("#sem_obras").html('Documento associado &agrave;s seguintes obras:');
			} else {
				$("#link_"+obraID).html("Reatribuir a esta obra");
				$("#link_"+obraID).attr("onclick","atribObra("+obraID+")");
				$("#link_obra_"+obraID).html(''); 
				$("#desfazer_obra_"+obraID).html('');
			}
			
			if(obraID == 0) {
				location.reload();
			}
		} else {
			if (fb[0].duplicado == true) {
				$("#link_"+obraID).html("Erro. Já atribuido.");
			}
			else {
				$("#link_"+obraID).html("Erro. Tentar novamente.");
			}
			$("#link_"+obraID).attr("onclick","atribEmpreend("+obraID+",false)");
		}
	});
}

function editVal(campoNome){
	$("#"+campoNome+"_val").hide();
	$("#"+campoNome+"_edit").show();
	$("#"+campoNome+"_link").val("Salvar");
	$("#"+campoNome+"_form").attr("action","javascript:saveVal('"+campoNome+"')");
}

function saveVal(campoNome){
	// verificacao de formatacao de numeroPR
	if (campoNome == 'numero_pr') {
		if (!validaNumPr($("#numero_pr").val())) { 
			alert("O número do processo está fora do padrão.");
			event.preventDefault();
		}
	}

	// forca a atualizacao do campo do CKEDITOR
	for ( instance in CKEDITOR.instances )
		CKEDITOR.instances[instance].updateElement();
	
	var valor;
	
	//if (campoNome == 'guardachuva' || campoNome == 'sigiloso') {
	// input do tipo radio
	if ($("#"+campoNome).attr("type") == 'radio') {
		valor = escape($("input[@name="+campoNome+"]:checked").val());
	} // input do tipo checkbox
	else if ($("#"+campoNome).attr("type") == 'checkbox') {
		if ($("input[@name="+campoNome+"]:checked").length == 0) {
			valor = 0;
		}
		else {
			valor = 1;
		}
	} // input de qualquer outro tipo
	else {
		/*var urlVar = getUrlVars();
		if (campoNome == 'conteudo' && urlVar['acao'] == 'salvar') {
			// ""metodo alternativo"" para editar conteudo na pagina de 'sgd.php?acao=salvar' funcione.
			var form = $("#"+campoNome+"_edit").closest('form').attr("id");
			var param = $("#" + form).serialize();
			//var valor = unescape(param).replace("conteudo=", "").replace("+", " "); alert(valor);
			var valor = htmlentities($("#conteudo").val(), 'ENT_QUOTES', null, false);
		}
		else {
			//valor = escape($("#"+campoNome).val());
			
			//alert(valor);
		}*/
		valor = $("#"+campoNome).val();
		if (valor.indexOf("\t") != -1) {
			valor = valor.replace("\t", "");
		}
		if (valor.indexOf("\r") != -1) {
			valor = valor.replace("\r", "");
		}
		if (valor.indexOf("\n") != -1) {
			valor = valor.replace("\n", "");
		}
		
		if (valor.indexOf("&#39;") != -1) {
			valor = valor.replace("&#39;", "&amp;#39;");
		}
		valor = htmlentities(valor, 'ENT_QUOTES', null, false);
		if (valor.indexOf("&quot;") != -1) {
			valor = valor.replace("&quot;", "&amp;quot;");
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
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message + " Retorno: " + d);
				//$("body").append("<div>Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message + " Retorno: " + d +"</div>");
			}
		}
		
		if(fb[0].success == 'true'){
			var href = 'javascript:editVal(\''+campoNome+'\')';
			var msg = 'Salvo!';
			$("#"+campoNome+"_val").html(html_entity_decode(valor));
			$("#"+campoNome+"_edit").hide();
			$("#"+campoNome+"_val").show();
		} else {
			var href = 'javascript:saveVal(\''+campoNome+'\')';
			var msg = 'Erro. Tentar Novamente.';
		}
		
		$("#"+campoNome+"_link").val(msg);
		//$("#"+campoNome+"_form").action("href",href);
		$("#"+campoNome+"_form").attr("action", href);
	});
	
}

function anexarDoc(filhoID,paiID){
	var ID = '';
	if($("#"+'addEste').attr('checked')) {
		ID = paiID;
	} else if($("#"+'addOutr').attr('checked')) {
		ID = filhoID;
	}
	$("#anexID"+ID).html('Anexando...');
	$("#anexID"+ID).attr('href','javascript: void(0);');
	
	$.get('sgd.php',{
		acao    : 'saveAnex',
		docID   : ID,
		filhoID : filhoID,
		paiID   : paiID
	}, function(d){
		//d = eval(d);
		try {
			d = eval(d);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
			}
		}
		
		if(d[0].success == 'true' && ID == paiID){
			var entries = $("a.resEntry");
			$.each(entries,function(i){
				entries[i].innerHTML ='Anexado a outro doc.';
				entries[i].attributes[2].nodeValue='javascript: void(0);';
			});
			$("#anexID"+ID).html('Anexado com sucesso!');
		} else if(d[0].success == 'true' && ID == filhoID){
			$("#anexID"+ID).html('Anexado com sucesso!');
		} else {
			$("#anexID"+ID).html('Falha. Tentar Novamente');
			$("#anexID"+ID).attr('href','javascript: anexarDoc('+filhoID+','+paiID+');');
		}
	});
}

function showAlert(){
	$("#alert").show();
	$("#c2").slideDown();
	resetBusca();
}

function hideAlert(){
	$("#alert").hide();
	$("#c2").slideDown();
	resetBusca();
}

function remontarDoc(){
	var getVars = getUrlVars();
	
	if (getVars['docID'] == undefined || getVars['docID'] == "") {
		if ($("#docID").length > 0) getVars['docID'] = $("#docID").html();
	}
	
	$.get('sgd.php',
		{
			acao : 'remontarDoc',
			docID: getVars['docID']
		},
		function(d){
			//d = eval(d);
			try {
				d = eval(d);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
				}
			}
			
			if(d[0].success == true) {
				alert("O novo arquivo foi criado com o nome de "+d[0].filename);
				if (getVars['acao'] != undefined && getVars['acao'] != 'salvar') location.reload();
				else if (getVars['acao'] == undefined) location.reload();
				else if (getVars['acao'] == 'salvar') {
					$("#pdfAnexos").append('<a onclick="window.open(\'files/'+d[0].filename+'\',\'ArqAnexo\',\'width='+screen.width*newWinWidth+',height='+screen.height*newWinHeight+',scrollbars=yes,resizable=yes\').focus()\">'+d[0].filename+'</a><br />');
				}
			}
			else alert("Falha ao gerar o arquivo.");
		}
	);
}

function dragAnex(paiID, filhoID, linhaPai, linhaFilho, confirm) {
	if (paiID != "" && filhoID != "") {
		if (paiID != undefined && filhoID != undefined) {
			if (paiID == filhoID) return;
			nome1 = $("#"+linhaPai).html();
			nome2 = $("#"+linhaFilho).html();
			assunto1 = $("#assunto"+linhaPai).html();
			assunto2 = $("#assunto"+linhaFilho).html();
			if (confirm == true) {
				$("#anex-confirm").attr("title", "Continuar anexa&ccedil;&atilde;o?");
				$("#anex-confirm").html("Voc&ecirc; est&aacute; tentando anexar o documento<br />("+paiID+") <b>"+nome1+"</b><br />Assunto: \""+assunto1+"\"<br />com o documento<br />("+filhoID+") <b>"+nome2+"</b><br />Assunto: \""+assunto2+"\".<br /><br />Deseja continuar ?");
				$("#anex-confirm").dialog({ 
					resizable: false,
					height: 250,
					width: 400,
					modal: true,
					buttons: {
						"Continuar": function() { $(this).dialog('close'); dragAnex(paiID, filhoID, linhaPai, linhaFilho, false); },
						"Cancelar": function() { $(this).dialog('close'); }
					}
				});
			}
			else {
			//if (confirm("Você está tentando anexar o documento ("+paiID+") "+nome1+" com o documento ("+filhoID+") "+nome2+". Deseja continuar ?")) {
				$.get('sgd.php',{
					acao    : 'saveAnex',
					docID   : paiID,
					filhoID : filhoID,
					paiID   : paiID
				}, function(d){
					//d = eval(d);
					try {
						d = eval(d);
					} catch(e) {
						if (e instanceof SyntaxError) {
							alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
						}
					}
					
					if(d[0].success == 'true') {
						$("#anex-confirm").attr('title', "Sucesso");
						$("#anex-confirm").html("Anexado com sucesso!");
						$("#anex-confirm").dialog({ 
							resizable: false,
							height: 250,
							width: 400,
							modal: true,
							buttons: {
								"OK": function() {
									/*if (parseInt(paiID) > parseInt(filhoID)) {
										if ($("#"+linhaPai).attr("name") == "pr") {
											$("#linhaDoc"+linhaFilho).remove();
										} 
										else {
											$("#linhaDoc"+linhaPai).remove();
										}
									}
									else {
										if ($("#"+linhaFilho).attr("name") == "pr") {
											$("#linhaDoc"+linhaPai).remove();
										} 
										else {
											$("#linhaDoc"+linhaFilho).remove();
										}
									}*/
									if (d[0].filhoID != undefined && d[0].filhoID != null) {
										if (d[0].filhoID == filhoID) {
											$("#multiCheck"+linhaFilho).removeAttr("checked");
											$("#linhaDoc"+linhaFilho).remove();
										}
										else {
											$("#multiCheck"+linhaPai).removeAttr("checked");
											$("#linhaDoc"+linhaPai).remove();
										}
									}
									$(this).dialog('close'); 
									//window.location.reload();
								}
							}
						});
					}
					else {
						//alert("Falha ao anexar os documentos.");
						$("#anex-confirm").attr('title', "Erro");
						$("#anex-confirm").html("Falha ao anexar os documentos. Por favor, tente novamente.");
						$("#anex-confirm").dialog({ 
							resizable: false,
							height: 250,
							width: 400,
							modal: true,
							buttons: {
								"OK": function() { $(this).dialog('close'); }
							}
						});
					}
					
				});
			}
		}
	}
}

function filtraDocPend(tipo, atualizaCheckBox) {
	if (tipo == undefined || tipo == null)
		return;
	
	if (atualizaCheckBox == undefined || atualizaCheckBox == null)
		atualizaCheckBox = true;

	// seleciona todos os links do tipo
	var docs = $("a[name="+tipo+"]");
	
	// seleciona o filtro de data atual (se algum estiver em uso)
	var filtros = $(".pendDocsData");
	var filtroData = null;
	var data = new Date();
	var dataUT = data.getTime()/1000;
	
	
	
	$.each(filtros, function() {
		if ($(this).css("font-weight") == "bold") {
			filtroData = $(this);
		}
	});
	
	var dias = -1;
	if (filtroData != null && filtroData != undefined) {
		dias = filtroData.attr("name");
	}
	dataUT -= dias * 60 * 60 * 24;
	
	// percorre todos os links e seleciona a linha
	$.each(docs, function() {
		var linkID = $(this).attr("id");
		var dataDesp = $("td#dataDesp"+linkID);
		
		var tr = $(this).closest("tr")
		
		if($("td#dataDesp"+linkID).html() >= dataUT || dias == -1) {
			// esconde/mostra a linha da tabela mais próxima deste link
			tr.toggle();
		}
	});
	
	if (atualizaCheckBox == true) {
		if ($("#check_"+tipo).attr("checked") == "checked")
			$("#check_"+tipo).removeAttr("checked");
		else
			$("#check_"+tipo).attr("checked", "checked");
	}
}


function filtraDocsPendData(pos, dias){
	if (dias == undefined || dias == null || pos == undefined || pos == null)
		return;
	var i = 1;
	var data = new Date();
	var dataUT = data.getTime()/1000;
	
	dataUT -= dias * 60 * 60 * 24;
	
	var tipo;
	
	//var datadoc = $("#dataDesp1").html();	
	while($("td#dataDesp"+i).length){
		tipo = $("td#dataDesp"+i).attr("name");
		
		if($("td#dataDesp"+i).html() < dataUT && dias != -1) {
			//esconde
			$("tr#linhaDoc"+i).hide();
		} else {
			//mostra
			if ($("#check_"+tipo).length <= 0 || $("#check_"+tipo).attr("checked") == "checked") {
				$("tr#linhaDoc"+i).show();
			}
		}		
		i++;
	}
	
	var numPOS = $(".pendDocsData").length;
	
	for(i=1; i<=numPOS ; i++){
		$("#filtraData"+i).css("color","#BE1010");
		$("#filtraData"+i).css("font-weight","normal");
	}
	
	$("#filtraData"+pos).css("color","black");
	$("#filtraData"+pos).css("font-weight","bold");
	
}

/*function ordenaTabelaDespacho(tabela, asc) {
	var ordenacao = [[0,0]];
	if (asc == false) {
		ordenacao = [[0,1]];
		$("#linkOrdena").attr("onclick", "ordenaTabelaDespacho('"+tabela+"', true)");
	}
	else {
		$("#linkOrdena").attr("onclick", "ordenaTabelaDespacho('"+tabela+"', false)");
	}	
	
	$("#docsPend").trigger("sorton", ""+ordenacao);
	
	return false;
}*/

function showDocPendAcoes(docID){
	$("#boxAcoes"+docID).show();
	$("#linkAcoes"+docID).hide();
	
}

function hideDocPendAcoes(docID){
	$("#boxAcoes"+docID).hide();
	$("#linkAcoes"+docID).show();
}

function showOldPDFs(div) {
	if (div == "" || div == undefined)
		return;
	
	$("#"+div).slideToggle();
}

