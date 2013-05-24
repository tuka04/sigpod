function searchMiniBusca(tipo){
	$("#empreendMiniBuscaResults").html('<br /><span style="font-weight:bold; text-align:center; display:block;">Aguarde... Buscando...</span><br />');
	
	var stringBusca = "";
	
	if (tipo == undefined || tipo == null) {
		tipo = 'empreendMiniBusca';
	}
	
	if (tipo == 'empreendMiniBusca') {
		stringBusca = escape($("#empreendMiniBuscaInput").val());
	}
	else {
		stringBusca = escape($("#processoMiniBuscaInput").val());
	}
	
	$.get('sgo_busca.php',{
		tipoBusca  : tipo,
		string : stringBusca
	},function(d){
		//var empreend = eval(d);
		try {
			var empreend = eval(d);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
			}
		}

		if(empreend.length == 0) {
			$("#empreendMiniBuscaResults").html('<br /><center><b>N&atilde;o h&aacute; obras com os crit&eacute;rios escolhidos.</b></center>');
		} else {
			var j, i;
			var pr = "";
			
			$("#empreendMiniBuscaResults").html('<table id="empreendMiniBuscaResultsTable" style="width:100%">'+
			'<tr><td class="c"><b>Empreendimento</b><br />Unidade/&Oacute;rg&atilde;o</td>'+
			'<td class="c" style="min-width:200px"></td></tr>'+
			'</table>');
			
		   	for(i = 0; i < empreend.length; i++){
		   		if (empreend[i].numero_pr != "" && empreend[i].numero_pr != null && empreend[i].docID != "" && empreend[i].docID != null) {
					pr = '(<b><a onclick="javascript:window.open(\'sgd.php?acao=ver&amp;docID='+empreend[i].docID+'&novaJanela=1\',\'doc_det\',\'width='+screen.width*newWinWidth+',height='+screen.height*newWinHeight+',scrollbars=yes,resizable=yes\').focus()">Processo '+empreend[i].numero_pr+'</a></b>: <div style="display: inline; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; width: 70%;">'+empreend[i].assunto+'</div>)';
				}
		   		
		   		$("#empreendMiniBuscaResultsTable").append('<tr class="c"><td class="c"><a href="javascript:void(0)" onclick="javascript:window.open(\'sgo.php?acao=verEmpreend&amp;empreendID='+empreend[i].id+'&novaJanela=1\',\'obra_det\',\'width='+screen.width*newWinWidth+',height='+screen.height*newWinHeight+',scrollbars=yes,resizable=yes\').focus()"><b>' + empreend[i].nome + '</b></a> '+pr+'<br />' + empreend[i].unOrg.compl + '</td>'+
		   											  '<td class="c" style="min-width:200px; text-align: right; vertical-align:middle;"><a id="link_'+empreend[i].id+'" href="javascript:void(0)" onclick="atribEmpreend('+empreend[i].id+',\'' + empreend[i].nome + '\')">Atribuir a este empreendimento</a></td></tr>');
			}
		}		
	});
}

function obraMiniBusca(){
	$("#obraMiniBuscaResults").html('<br /><span style="font-weight:bold; text-align:center; display:block;">Aguarde... Buscando...</span><br />');
	
	var stringBusca = escape($("#empreendMiniBuscaInput").val());
	
	$.get('sgo_busca.php',{
		tipoBusca : 'obraMiniBusca',
		string : stringBusca
	}, function(d){
		try {
			var obras = eval(d);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
			}
		}
		
		if(obras.length == 0) {
			$("#obraMiniBuscaResults").html('<br /><center><b>N&atilde;o h&aacute; obras com os crit&eacute;rios escolhidos.</b></center>');
		} else {
			var j, i;
			var pr = "";
			
			$("#obraMiniBuscaResults").html('<table id="obraMiniBuscaResultsTable" style="width:100%">'+
			'<tr><td class="c"><b>Obra</b><br />Unidade/&Oacute;rg&atilde;o</td>'+
			'<td class="c" style="min-width:200px"></td></tr>'+
			'</table>');
			
		   	for(i = 0; i < obras.length; i++){
		   		if(obras[i].obraID != null)
		   			$("#obraMiniBuscaResultsTable").append('<tr class="c"><td class="c"><a href="javascript:void(0)" onclick="javascript:window.open(\'sgo.php?acao=verObra&amp;obraID='+obras[i].obraID+'&novaJanela=1\',\'obra_det\',\'width='+screen.width*newWinWidth+',height='+screen.height*newWinHeight+',scrollbars=yes,resizable=yes\').focus()" style="font-weight: bold;" id="nome_obra_'+obras[i].obraID+'">' + obras[i].obraNome + '</b></a> <br />' + obras[i].unOrg + '</td>'+
		   											  '<td class="c" style="min-width:200px; text-align: right; vertical-align:middle;"><a id="link_'+obras[i].obraID+'" href="javascript:void(0)" onclick="atribObra('+obras[i].obraID+')">Atribuir a esta obra</a></td></tr>');
		   		else
		   			$("#obraMiniBuscaResultsTable").append('<tr class="c"><td class="c"><a href="javascript:void(0)" onclick="javascript:window.open(\'sgo.php?acao=verEmpreend&amp;empreendID='+obras[i].empreendID+'&novaJanela=1\',\'obra_det\',\'width='+screen.width*newWinWidth+',height='+screen.height*newWinHeight+',scrollbars=yes,resizable=yes\').focus()" style="font-weight: bold;" id="nome_obra_'+obras[i].empreendID+'">' + obras[i].empreendNome + '</b></a> <br />' + obras[i].unOrg + '</td>'+
								  '<td class="c" style="min-width:200px; text-align: right; vertical-align:middle;">Imposs&iacute;vel atribuir. Este empreendimento n&atilde;o cont&eacute;m obras.</td></tr>');
			}
		}
	});
}