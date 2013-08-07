$(document).ready(function(){
	
	$("input:checkbox").click(function(){
		carregaCampos();
	});
	
	$("#buscaForm").submit(function(submit){
		submit.preventDefault();
		doBusca(0, 100);
	});
	
});


function doBusca(inicio, numResultados){
	$("#numRes").html("");
	$("#resBusca").html('<center><img src="img/carregando.gif" width="235" height="235" alt="Carregando... Aguarde!"/></center>');
	$("#resBusca").show();
	$(".buscaFormTable").slideUp();
	$(".novaBuscaBtn").show();
	if($("#camposNomes").val())
		var camposNomes = $("#camposNomes").val().split(',');
	else
		var camposNomes = new Array();
	var valoresBusca = '';
	var tipoDoc = '';
	var checkedBoxes = $(".tipoDoc:checked");
	var urlVar = getUrlVars();
	var arquivado = $("#buscaArquivo:checked").val();
	var anex = '0';
	var apenasContr = false;
	
	if ($("#actionAnex:checked").length > 0) anex = $("#actionAnex:checked").val();
	
	if(urlVar['onclick'] == undefined)
		urlVar['onclick'] = '';
	if(urlVar['target'] == undefined)
		urlVar['target'] = '';
		
	$("#btnBuscar").val('Buscando...');
	
	$.each(checkedBoxes, function(i){
		tipoDoc += checkedBoxes[i].value + ',';
	});
	
	if (checkedBoxes.length == 1 && checkedBoxes[0].value == 'contr') {
		apenasContr = true;
	}
	
	$.each(camposNomes, function(i){
		if(camposNomes[i] != '') {
			if ($("#"+camposNomes[i]).is("input[type='radio'], input[type='checkbox']")) { 
				if ($("#" + camposNomes[i] + ":checked").length > 0)
					valoresBusca += camposNomes[i] + '=' + escape($("#" + camposNomes[i] + ":checked").val()) + '|';
			}
			else {
				if ($("#" + camposNomes[i]).val() != undefined) {
					valoresBusca += camposNomes[i] + '=' + escape($("#" + camposNomes[i]).val()) + '|';
					if ($("#" + camposNomes[i] + "_operador") != undefined) {
						valoresBusca += camposNomes[i] +"_operador=" + escape($("#" + camposNomes[i] + "_operador").val()) + '|';
					}
				}
			}
		}
	});
	
	
	var AcaoArquivar = "";
	$.get('sgd.php', { acao: 'getArquivarAcao' }, function(q) {
		//q = eval(q);
		try {
			q = JSON.parse(q);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message + "\n" + q);
			}
		}
		
		if (q.length > 0) {
			AcaoArquivar = q[0].acao;
		}
		
		
	});
	$.get('sgd_busca.php',{
			tipoDoc:       tipoDoc,
			tipoBusca:     "busca", 
			numCPO:        $("#numCPO").val(),
			numDoc:        $("#numDoc").val(),
			assunto_gen:   $("#assunto_gen").val(),
			dataCriacao1:  $("#dataCriacao1").val(),
			dataCriacao2:  $("#dataCriacao2").val(),
			dataDespacho1: $("#dataDespacho1").val(),
			dataDespacho2: $("#dataDespacho2").val(),
			unDespacho:    $("#unDespacho").val(),
			dataReceb1:    $("#dataReceb1").val(),
			dataReceb2:    $("#dataReceb2").val(),
			unReceb:       $("#unReceb").val(),
			contDesp:      $("#contDesp").val(),
			contGen:       escape($("#contGen").val()),
			anex: anex,
			arquivado: arquivado,
			valoresBusca: valoresBusca,
			inicioRes: inicio,
			numResult: numResultados
		}, function(data){
		//alert(data); 
		//data = eval(data);
			try {
				data = eval(data);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message + "\n JSON retornado: "+data);
					return;
					//alert(data);
				}
			}
			
			if(data.length != 0){
				$("#resBusca").html("");
				geraPaginas(data[0].total, inicio/numResultados, true);
				
				if (!apenasContr) {
					$("#resBusca").append('<table width="100%" id="res"><tr><td class="cc"><b>n° Doc.</b></td><td class="cc"><b>Tipo/Número</b></td><td class="cc"><b>Unidade/&Oacute;rg&atilde;o Interessado</b></td><td class="cc"><b>Assunto</b></td><td class="cc"><b>Empreendimento</b></td><td class="cc"><b>A&ccedil;&atilde;o</b></td></tr>');
				}
				else {
					//solicitacao 003, removendo data de vigencia <td class="cc"><b>Data Vig&ecirc;ncia</b></td>
					$("#resBusca").append('<table width="100%" id="res"><tr><td class="cc"><b>n° Doc.</b></td><td class="cc"><b>Número do Contrato</b></td><td class="cc"><b>Obras</b></td><td class="cc"><b>Tipo Processo</b></td><td class="cc"><b>Processo</b></td><td class="cc"><b>Empresa</b></td><td class="cc"><b>Data Conclus&atilde;o</b></td></tr>');
				}
				
				$.each(data,function(i){
					var id = data[i].id;
					var nome = data[i].nome;
					var emissor = data[i].emitente;
					var assunto = data[i].assunto;
					var empreendList = data[i].empreendList;
					var empreendimento = new Array();
					empreendimento[0] = "";
					if (empreendList != null && empreendList.length > 0) { // este doc esta atribuido a um empreendimento ? se sim, cria link
						for (e = 0; e < empreendList.length; e++) {
							empreendimento[e] = newWinLink('sgo.php?acao=verEmpreend&empreendID='+empreendList[e][0], 'obra', screen.width*newWinWidth, screen.height*newWinHeight, empreendList[e][1]);
						}
						
						if (data[i].guardachuva != null && data[i].guardachuva == 1) { // se for guardachuva, adiciona link para atribuicao
							if (urlVar['onclick'] == 'associarEmpreend')
								urlVar['target'] = id;
							if (urlVar['onclick'] == '') { 
								urlVar['onclick'] = 'associarEmpreend';
								urlVar['target'] = id;
							}
						}
						else { // nao eh guardachuva
							if (urlVar['onclick'] == 'associarEmpreend') {
								urlVar['onclick'] = '';
								urlVar['target'] = '';
							}
						}
					}
					else { // se nao, verifica se e' um processo do tipo de obra e caso afirmativo, cria link (data[0].tipo.nomeAbrv == "pr") &&
						if ((data[i].tipoProc != null) && (data[i].tipo.nomeAbrv == "pr") &&
							((data[i].tipoProc == "outro") || (data[i].tipoProc == "plan") || (data[i].tipoProc == "contrProj") || (data[i].tipoProc == "contrObr") || (data[i].tipoProc == "acompTec") || (data[i].tipoProc == "pagProj"))) {
							if (urlVar['onclick'] == 'associarEmpreend')
								urlVar['target'] = id;
							if (urlVar['onclick'] == '') { 
								urlVar['onclick'] = 'associarEmpreend';
								urlVar['target'] = id;
							}
						}
						else {
							if (urlVar['onclick'] == 'associarEmpreend') {
								urlVar['onclick'] = '';
								urlVar['target'] = '';
							}
						}
					}
					//var lk = newWinLink('sgd.php?acao=ver&docID='+id,'detalhe'+id,950,650,nome);
					var lk = newWinLink('sgd.php?acao=ver&docID='+id,'doc',screen.width*newWinWidth,screen.height*newWinHeight,nome);
					var actionList = "";
					actionList += urlVar['onclick'];
					
					// verifica se o doc está arquivado. Se estiver, consulta qual acao o usuario pode fazer (desarquivar ou solicitar desarq.)
					if (data[i].arquivado == 1) {
						actionList = AcaoArquivar;
					}
					
					var acao = addAction(actionList,id,nome,data[i].anexado,urlVar['target'],data[i].anexavel);
					
					var empr = "";
					for (contador = 0; contador < empreendimento.length; contador++) { 
						empr += empreendimento[contador];
						if (contador+1 != empreendimento.lenght) empr += "<br />";
					}
					
					if (data[i].ownerName != null) {
						owner = ' title="Este documento foi despachado para '+data[i].ownerName+'."';
					}
					else {
						owner = ' title="Este documento est&aacute; fora da CPO."';
						if (data[i].docPaiID != 0 && data[i].anexado) owner = ' title="Este documento est&aacute; anexado."';
						if (data[i].arquivado == 1) owner = ' title="Este documento est&aacute; arquivado."';
					}
					
					if (!apenasContr) {
						$("#res").append('<tr class="c"><td class="cc">'+id+'</td><td class="cc" '+owner+'>'+lk+'</td><td class="cc">'+emissor+'</td><td class="cc">'+assunto+'</td><td class="cc">'+empr+'</td><td class="cc">'+acao+'</td></tr>');
					}
					else {
						var linha = '';
						var tipoProc = 'Contrata&ccedil;&atilde;o de Obra';
						var lk = newWinLink('sgd.php?acao=ver&docID='+id,'doc',screen.width*newWinWidth,screen.height*newWinHeight,data[i].numeroCompl);
						var obras = data[i].data_contrato.obras;
						var processoPai = '';
						
						linha += '<tr class="c">';
						linha += '<td class="cc">'+id+'</td>';
						linha += '<td class="cc" '+owner+'>'+lk+'</td>';
						
						linha += '<td class="cc">';
						if (obras.lenght <= 0) {
							linha += 'Nenhuma obra associada';
						}
						else {
							var obra_link = '';
							for (var j = 0; j < obras.length; j++) {
								obra_link = newWinLink('sgo.php?acao=verObra&obraID='+obras[j].id, 'obra', screen.width*newWinWidth, screen.height*newWinHeight, obras[j].nome);
								linha += obra_link + '<br />';
							}
						}
						linha += '</td>';
						
						if (data[i].data_contrato.proc.tipoProc == 'contrProj')
							tipoProc = 'Contrata&ccedil;&atilde;o de Projeto';
						
						linha += '<td class="cc">'+tipoProc+'</td>';
						
						processoPai = newWinLink('sgd.php?acao=ver&docID='+data[i].data_contrato.proc.id,'doc',screen.width*newWinWidth,screen.height*newWinHeight, data[i].data_contrato.proc.numeroCompl);
						
						linha += '<td class="cc">'+processoPai+'</td>';
						linha += '<td class="cc">'+data[i].data_contrato.empresa+'</td>';
						// solicitacao 003: remover dataVigencia linha += '<td class="cc">'+data[i].vigenciaContr+'</td>';
						linha += '<td class="cc">'+data[i].dataTermino+'</td>';
						linha += '</tr>';
						
						$("#res").append(linha);
					}
				});
				$("#resBusca").append("</table>");
				$("#resBusca").append('<div id="buscaAlert"></div>');
				geraPaginas(data[0].total, inicio/numResultados, false);
				$("td[title]").tooltip({ offset: [-10, 2], effect: "slide", delay: 0 }).dynamic({ bottom: { direction: "down", bounce: true } });
			}else{
				$("#resBusca").html("<center><b>N&atilde;o foi encontrado nenhum documento.</b></center>");
			}
		
	});
	
	$("#btnBuscar").val('Buscar novamente.');
	$("#resBusca").slideDown();
}

function geraPaginas(resultados, paginaAtual, top) {
	$("#numRes").html("<b>" + resultados + "</b> documentos. (Exibindo <b>100</b> resultados por página.)");
	$("#resBusca").append("<br /><b>Páginas</b>:");
	/*for (var i = 0; i < resultados/100; i++) {
		if (i == paginaAtual) {
			$("#resBusca").append(" <b>[ " + (i+1) + " ]</b>");
		}
		else  {
			$("#resBusca").append(' <a href="javascript:doBusca(' + i*100 + ', 100)">' + (i+1) + "</a>");
		}
	}
	$("#resBusca").append(". Listando resultados de <b>" + ((paginaAtual*100)+1)+ "</b> até ");
	if ((paginaAtual+1)*100 > resultados) {
		$("#resBusca").append("<b>" + resultados + "</b>.");
	}
	else {
		$("#resBusca").append("<b>" + (paginaAtual+1)*100 + "</b>.");
	}
	$("#resBusca").append("<br />");*/
	
	var firstInterval = false;
	var secondInterval = false;
	
	if (top == true) {
		var controle = 'Top';
	}
	else {
		var controle = 'Botton';
	}
	
	//alert(resultados/100)
	
	for (var i = 0; i < resultados/100; i++) {
		
		if (i <= 10) {
			if (i == paginaAtual) {
				$("#resBusca").append(" <b>[ " + (i+1) + " ]</b>");
			}
			else  {
				$("#resBusca").append(' <a href="javascript:doBusca(' + i*100 + ', 100)">' + (i+1) + "</a>");
			}
		}
		else if (i > 10 && i < (resultados/100) - 10 && paginaAtual <= 10) {
			if (firstInterval == false) {
				firstInterval = true;
				$("#resBusca").append(' <a id="link_int'+controle+'1" onclick="javascript:toggleIntBusca(1, '+top+')">.....</a><span id="interval'+controle+'1" style="display: none;"></span>');
			}
			if (i == paginaAtual) {
				$("#interval"+controle+"1").append(" <b>[ " + (i+1) + " ]</b>");
			}
			else  {
				$("#interval"+controle+"1").append(' <a id="q" href="javascript:doBusca(' + i*100 + ', 100)">' + (i+1) + "</a>");
			}
		}
		else if (i > 10 && i < (resultados/100) - 10 && paginaAtual <= 10) {
			if (firstInterval == false) {
				firstInterval = true;
				$("#resBusca").append(' <a id="link_int'+controle+'1" onclick="javascript:javascript:toggleIntBusca(1, '+top+')">.....</a><span id="interval'+controle+'1" style="display: none;"></span>');
			}
			$("#interval"+controle+"1").append(' <a id="q1" href="javascript:doBusca(' + i*100 + ', 100)">' + (i+1) + "</a>");
		}
		else if (i > 10 && i < (resultados/100) - 10 && paginaAtual > 10 && i <= paginaAtual + 5) {
			if (i < paginaAtual - 5) {
				if (firstInterval == false) {
					firstInterval = true;
					$("#resBusca").append(' <a id="link_int'+controle+'1" onclick="javascript:toggleIntBusca(1, '+top+')">.....</a><span id="interval'+controle+'1" style="display: none;"></span>');
				}
				if (i != paginaAtual) 
					$("#interval"+controle+"1").append(' <a href="javascript:doBusca(' + i*100 + ', 100)">' + (i+1) + "</a>");
				else
					$("#interval"+controle+"1").append(" <b>[ " + (i+1) + " ]</b>");
			}
			else {
				if (firstInterval == true) {
					firstInterval = null;
					//$("#resBusca").append("</span>");
				}
				
				if (i != paginaAtual) 
					$("#resBusca").append(' <a href="javascript:doBusca(' + i*100 + ', 100)">' + (i+1) + "</a>");
				else
					$("#resBusca").append(" <b>[ " + (i+1) + " ]</b>");
			}
		}
		else if (i > 10 && i < (resultados/100) - 10 && paginaAtual > 10 && paginaAtual < (resultados/100) - 10) {
			//alert(i);
			if (secondInterval == false) {
				secondInterval = true;
				$("#resBusca").append(' <a id="link_int'+controle+'2" onclick="javascript:toggleIntBusca(2, '+top+')">.....</a><span id="interval'+controle+'2" style="display: none;"></span>');
			}
			
			if (i != paginaAtual)
				$("#interval"+controle+"2").append(' <a href="javascript:doBusca(' + i*100 + ', 100)">' + (i+1) + "</a>");
			else
				$("#interval"+controle+"2").append(" <b>[ " + (i+1) + " ]</b>");
		}
		else if (i >= (resultados/100) - 10) {
			if (firstInterval == true) {
				firstInterval = null;
				//$("#resBusca").append("</span>");
			}
			
			if (paginaAtual > (resultados/100) - 10) {
				if (i != paginaAtual) 
					$("#resBusca").append(' <a href="javascript:doBusca(' + i*100 + ', 100)">' + (i+1) + "</a>");
				else
					$("#resBusca").append(" <b>[ " + (i+1) + " ]</b>");
			}
			else {
				if (i >= (resultados/100) - 5 && secondInterval == true) {
					secondInterval = null;
					//$("#resBusca").append("</span>");
				}
				
				if (i != paginaAtual) 
					$("#resBusca").append(' <a href="javascript:doBusca(' + i*100 + ', 100)">' + (i+1) + "</a>");
				else
					$("#resBusca").append(" <b>[ " + (i+1) + " ]</b>");
			}
		}		
	}
	
	$("#resBusca").append(".<br /> Listando resultados de <b>" + ((paginaAtual*100)+1)+ "</b> até ");
	if ((paginaAtual+1)*100 > resultados) {
		$("#resBusca").append("<b>" + resultados + "</b>.");
	}
	else {
		$("#resBusca").append("<b>" + (paginaAtual+1)*100 + "</b>.");
	}
	$("#resBusca").append("<br />");
	
}

function toggleIntBusca(interval, top) {
	//alert(interval);
	
	if (top == true) {
		var controle = 'Top';
	}
	else {
		var controle = 'Botton';
	}
	
	$("#link_int"+controle+interval).hide();
	$("#interval"+controle+interval).show();
}

function carregaCampos(){
	$("#camposBusca").html("Carregando...");
	tipoString = '';
	var tipoDoc = $("input:checked[type=checkbox]");
	$.each(tipoDoc,function(i){
		tipoString += tipoDoc[i].value + ',';
	});
	
	var urlVar = getUrlVars();
	var mini = false;
	if (urlVar['acao'] == 'busca_mini' || urlVar['acao'] == 'anexDoc')
		mini = true;
	
	var anex = false;
	if (urlVar['acao'] == 'anexDoc')
		anex = true;
	
	$.get('sgd_busca.php',{tipoBusca: 'campoSearch', docs: tipoString, mini: mini, anex: anex}, function(htmlCampos){
		$("#camposBusca").html(htmlCampos);
		$( "#dataCriacao1" ).datepicker({
			defaultDate: "-60d",
			dateFormat: "dd/mm/yy",
			regional: "pt-BR",
			constrainInput: true,
			changeMonth: true,
			numberOfMonths: 3,
			onSelect: function( selectedDate ) {
				$( "#dataCriacao2" ).datepicker( "option", "minDate", selectedDate );
			}
		});
		$( "#dataCriacao2" ).datepicker({
			defaultDate: "-60d",
			dateFormat: "dd/mm/yy",
			regional: "pt-BR",
			constrainInput: true,
			changeMonth: true,
			numberOfMonths: 3,
			onSelect: function( selectedDate ) {
				$( "#dataCriacao1" ).datepicker( "option", "maxDate", selectedDate );
			}
		});
		
		$( "#dataDespacho1" ).datepicker({
			defaultDate: "-60d",
			dateFormat: "dd/mm/yy",
			regional: "pt-BR",
			constrainInput: true,
			changeMonth: true,
			numberOfMonths: 3,
			onSelect: function( selectedDate ) {
				$( "#dataDespacho2" ).datepicker( "option", "minDate", selectedDate );
			}
		});
		$( "#dataDespacho2" ).datepicker({
			defaultDate: "-60d",
			dateFormat: "dd/mm/yy",
			regional: "pt-BR",
			constrainInput: true,
			changeMonth: true,
			numberOfMonths: 3,
			onSelect: function( selectedDate ) {
				$( "#dataDespacho1" ).datepicker( "option", "maxDate", selectedDate );
			}
		});
		
		$( "#dataReceb1" ).datepicker({
			defaultDate: "-60d",
			dateFormat: "dd/mm/yy",
			regional: "pt-BR",
			constrainInput: true,
			changeMonth: true,
			numberOfMonths: 3,
			onSelect: function( selectedDate ) {
				$( "#dataReceb2" ).datepicker( "option", "minDate", selectedDate );
			}
		});
		$( "#dataReceb2" ).datepicker({
			defaultDate: "-60d",
			dateFormat: "dd/mm/yy",
			regional: "pt-BR",
			constrainInput: true,
			changeMonth: true,
			numberOfMonths: 3,
			onSelect: function( selectedDate ) {
				$( "#dataReceb1" ).datepicker( "option", "maxDate", selectedDate );
			}
		});
	});
	
}

function checkAll(){
	var checked = $("input:checked[type=checkbox]");
	if(checked.length == 0){
		$("input[type=checkbox][disabled!=disabled]").attr('checked','checked');
		carregaCampos();
		$("#resBusca").slideUp();
	} else {
		resetBusca();
	}
	
}

//adiciona os links referentes a acoes
function addAction(action,id,nome,anexado,target,anexavel){
	action = action.split(",");
	var i = 0, ret = '';
	for(i = 0 ; i < action.length ; i++){
		if(i > 0 && ret != "") ret += '<br />';
		if(action[i] == 'desarquivar') ret += '<span id="arq'+id+'"><a onclick="desarquivarDoc('+id+')">Desarquivar Documento</a></span>';
		if(action[i] == 'solicArq') ret += '<span id="arq'+id+'"><a onclick="solicDesarq('+id+')">Solicitar Desarquivamento</a></span>';
		if(action[i] == 'referenciar') ret += '<a onclick="referDocAux(\''+nome+'\',\''+target+'\')">Referenciar Documento</a>';
		if(action[i] == 'adicionarCampo') ret += '<a onclick="addDoc('+id+',\''+nome+'\',\''+target+'\',\'<br>\')">Adicionar Documento</a>';
		if(action[i] == 'adicionar' && !anexado) ret += '<a onclick="addDoc('+id+',\''+nome+'\',\''+target+'\',\'<br>\')">Adicionar Documento</a>';
		if(action[i] == 'adicionar' && anexado) ret += 'J&aacute; anexado.';
		if(action[i] == 'ver') ret += newWinLink('sgd.php?acao=ver&docID='+id,'detalhe'+id,950,650,'Ver Documento');
		if(action[i] == 'associarEmpreend') ret += newWinLink('sgd.php?acao=atribEmpreend&onclick=anex&docID='+target, 'detalhe'+target, screen.width*newWinWidth, screen.height*newWinHeight, 'Atribuir a um Empreendimento');
		if(action[i] == 'anex'){
			var get = getUrlVars();
			var este = $("#"+'addEste').attr('checked');
			var outro = $("#"+'addOutr').attr('checked');
			if(este){
				if(anexavel == '1')
					ret += '<a class="resEntry" id="anexID'+id+'" href="javascript:anexarDoc('+get['docID']+','+id+');">Anexar a este documento.</a>';
				else
					ret += 'Este documento n&atilde;o pode receber outros documentos como anexo.';
			} else if(outro && !anexado){
					ret += '<a class="resEntry" id="anexID'+id+'" href="javascript:anexarDoc('+id+','+get['docID']+');">Anexar este documento.</a>';
			} else if(!este && ! outro) {
				ret += 'Nenhum tipo de anexa&ccedil;&atilde;o selecionado.';
			} else {
				ret += 'J&aacute; anexado.';
			}
		}
	}
	return ret;
}

function addDoc(id,nome,target,sep){
	if (target == 'unOrgInput') {
		window.opener.newDocLink(id,nome,'unOrgSolic','<br>');
	}
	
	window.opener.newDocLink(id,nome,target,'<br>');
	if(confirm("Documento adicionado com sucesso.\nClique OK para fechar a janela de busca."))
		self.close();
}

//cria um link para abertura em nova pagina
function newWinLink(url,name,w,h,label){
	return '<a href="javascript:void(0)" onclick="window.open(\''+url+'\',\''+name+'\',\'width='+w+',height='+h+',scrollbars=yes,resizable=yes\').focus()">'+label+'</a>';
}

function resetBusca(){
	$("input[type=checkbox]").removeAttr('checked');
	$("#numRes").html("");
	carregaCampos();
	$("#resBusca").slideUp();
}

function novaBusca(){
	$(".buscaFormTable").slideDown();
	$(".novaBuscaBtn").hide();
}

function solicDesarq(docID) {
	$.get('sgd.php', {
		acao: 'solicDesarqAjax',
		docID: docID
	}, function(d) {
		//d = eval(d);
		try {
			d = eval(d);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
			}
		}

		if (d[0].success) {
			$("#arq"+docID).html("Requisitado com sucesso!");
			$("#buscaAlert").attr("title", "Aviso");
			$("#buscaAlert").html("<center><b>Requisição gravada com sucesso!</b></center><br /><br />O Protocolo lhe encaminhará este documento quando ele entrar na CPO.");
			$("#buscaAlert").dialog({ 
				resizable: false,
				height: 200,
				width: 350,
				modal: true,
				buttons: {
					"OK": function() { $(this).dialog('close'); }
				}
			});
		}
		else {
			$("#arq"+docID).html(d[0].feedback);
		}
	});
}