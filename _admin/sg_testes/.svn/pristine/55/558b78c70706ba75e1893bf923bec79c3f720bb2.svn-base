/*
 * FUNCOES PARA A PAGINA DE ADMINISTRACAO (CAMPOS/DOCUMENTOS)  
 */

function addCampo(i){
	$("#configCampoCont").html(chooseCampo(i));
	$("#configCampo").slideDown();
}

function chooseCampo(i){
	return '<center><a href="javascript:void(0);" onclick="newCampo();">Criar novo campo</a><br /><br />'+
		'<a href="javascript:void(0);" onclick="loadCampo();">Utilizar campo existente</a></center>';
}

function loadCampo(){
	var form = '<form action="javascript:void(0);" onsubmit="buscaCampo();">'+
		'<center><b>Nome do campo:</b><input type="text" id="nomeCampoBusca" /><input type="submit" value="enviar" /></center></form>'+
			'<div id="resultados"></div>';
	
	$("#configCampoCont").html(form);
}

function newCampo(){
	var form = '<form action="javascript:void(0);" id="newCampoForm" onsubmit="addNewCampoInDoc();"><table width=100%>'+
		'<tr><td><b>Nome (Abrv):</b></td><td><input id="nomeCampo" type="text" length="20" maxlength="20" onblur="verificaCampo();" /><span id="disp"></span></td></tr>'+
		'<tr><td><b>Nome no Documento:</b></td><td><input id="labelCampo" type="text" length="20" maxlength="20" /></td></tr>'+
		'<tr><td><b>Tipo:</b></td><td><select id="tipoCampo" name="tipo" onchange="loadExtras();"><option value="nenhum" selected>-- Selecione --</option><option value="input">Campo de Texto (Input)</option><option value="select">Caixa de Sele&ccedil;&atilde;o</option><option value="textarea">&Aacute;rea de Texto</option><option value="yesno">Radio Sim/N&atildeo</option><option value="checkbox">Checkbox</option><option value="anoSelect">Caixa de Sele&ccedil;&atilde;o de Ano</option><option value="documentos">Adi&ccedil;&atilde;o de documentos</option><option value="autoincrement">N&uacute;mero de documento autoincremental</option><option value="userID">ID do usu&aacute;rio</option><option value="composto">Campo Composto</option><option value="outro">Outro</option></select></td></tr>'+
		'<tr><td><b>Atributos:</b></td><td><textarea id="attrCampo" name="attrCampo" rows=3 cols=30></textarea></b></td></tr>'+
		'<tr><td><b>Extras:</b></td><td id="extra"><i>Selecione um tipo primeiro</i></td></tr>'+
		'<tr><td colspan="2"><center><input id="cadCampo" type="submit" value="Cadastrar Campo" /></center></td></tr>'+
		'</table></form>';
	
	$("#configCampoCont").html(form);
}

function verificaCampo(){
	var nome = $("#nomeCampo").val();
	
	$.get("unSearch.php?show=verifCampo&nome="+nome,function(d){
		
		//d = eval(d);
		try {
			d = eval(d);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
			}
		}
		
		if(d[0].qtdeNome > 0) {
			$("#disp").css({"color":"red","font-weight":"bold"});
			$("#disp").html("Nome em uso! Favor escolher outro.");
			$("#nomeCampo").css("background-color","#FFCCCC");
			$("#cadCampo").attr("disabled","disabled");
		} else {
			$("#disp").css({"color":"green"});
			$("#nomeCampo").css("background-color","white");
			$("#disp").html("Nome dispon&iacute;vel.");
			$("#cadCampo").removeAttr("disabled");
		}
	});
}

function buscaCampo(){
	var nome = $("#nomeCampoBusca").val();
	var overflow = false;
	$.get("unSearch.php?show=buscaCampo&label="+nome,function(d){
		//d = eval(d);
		try {
			d = eval(d);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message);
			}
		}
		$("#resultados").html('<table width="500px"><tbody>');
		$.each(d,function(i){
			if(i > 5){
				overflow = true;
				return;				
			}
			
			if(this.attr.length > 50)
				this.attr = this.attr.slice(0,50)+'...';
			$("#resultados").append('<tr class="c"><td id="label_'+this.nome+'" class="c" width="50px" style="font-weight:bold">'
					+this.label+'</td><td id="tipo_'+this.nome+'" class="c" width="50px">'
					+this.tipo+'</td><td id="attr_'+this.nome+'" class="c" width="200px">'
					+this.attr+'</td><td id="extra_'+this.nome+'" class="c" width="150px">'
					+this.extra+'</td><td class="c" width="50px"><a href="javascript:void(0);" onclick="useCampo(\''+this.nome+'\')">Usar este</a></td></tr>'
					);				
		});
		$("#resultados").append('</tbody></table>');
		if(overflow){
			$("#resultados").append('<b>Sua busca retornou muitos resultados. Tente buscar por um termo mais preciso.</b>');
		}
	});
}

function useCampo(nome){
	var extra = $("#extra_"+nome).html();
	var label = $("#label_"+nome).html();
	var tipo  = $("#tipo_" +nome).html();
	
	$("#camposDet").append(
			'<tr id="'+nome+'Det" class="c">'+
			'<td class="c"><b>'+label+'</b></td>'+
			'<td class="c" style="text-align: center;">'+tipo+'</td>'+
			'<td class="c" style="text-align: center;"> <input type="checkbox" name="'+nome+'_emi" value="1" /> </td>'+
			'<td class="c" style="text-align: center;"> <input type="radio"    name="emiPrinc" value="'+nome+'" /> </td>'+
			'<td class="c" style="text-align: center;"> <input type="checkbox" name="'+nome+'_campoBusca" value="1" /> </td>'+
			'<td class="c" style="text-align: center;"> <input type="radio"    name="campoIndice" value="'+nome+'" /> </td>'+
			'<td class="c"><a href="javascript:void(0);" onclick="excluirCampo(\''+nome+'\')">[Excluir]</a></td>'+
			'</tr>'
	);
	
	$("#campos").val($("#campos").val()+nome+',');
	
	closeCampo();
}

function loadExtras(){
	var tipoSelec = $("#tipoCampo").val();
	var extras = '';
	if(tipoSelec == 'input'){
		extras += '<input id="unOrg_autocompletar" type="checkbox" name="unOrg_autocompletar" /> Autocompletar de Unidades/&Oacute;rg&atilde;os<br />';
	}
	if(tipoSelec == 'autoincrement'){
		extras += '<input id="current_year" type="checkbox" name="current_year" /> Incluir "/2011" no final (ordem reininicia a cada ano)<br />';
	}
	if(tipoSelec != 'checkbox'){
		extras += '<input id="obrigatorio" type="checkbox" name="obrigatorio" /> Obrigat&oacute;rio<br />';
	}
	if(tipoSelec == 'input' || tipoSelec == 'select' || tipoSelec == 'anoSelect' || tipoSelec == 'autoincrement'){
		extras += '<input id="parte" type="checkbox" name="parte" /> Parte de um campo composto<br />';
	}
	
	$("#extra").html(extras);
}

function addNewCampoInDoc(){
	var extra = '', nome = $("#nomeCampo").val(), label = $("#labelCampo").val(), tipo = $("#tipoCampo").val(), attr = $("#attrCampo").val();
	
	if(nome == '' || label == '' || tipo == 'nenhum'){
		alert("Voc&ecirc; deve digitar nome, nome no documento e selecionar um tipo de campo.");
		return;
	}
	
	$("#cadCampo").val("Cadastrando Campo... Aguarde...");
	$("#cadCampo").attr("disabled","disabled");
	
	if ($("#unOrg_autocompletar").attr("checked"))
		extra += 'unOrg_autocompletar ';
	if ($("#current_year").attr("checked"))
		extra += 'current_year ';	
	if ($("#obrigatorio").attr("checked"))
		extra += 'obrigatorio ';
	if ($("#parte").attr("checked"))
		extra += 'parte ';
	
	$.get("unSearch.php?show=cadCampo&nome="+nome+"&label="+escape(label)+"&tipo="+tipo+"&attr="+attr+"&extra="+extra,function(r){
		if(r == 1){
			$("#camposDet").append(
				'<tr id="'+nome+'Det" class="c">'+
					'<td class="c"><b>'+label+'</b></td>'+
					'<td class="c" style="text-align: center;">'+$("#tipoCampo option:selected").val()+'</td>'+
					'<td class="c" style="text-align: center;"> <input type="checkbox" name="'+nome+'_emi" value="1" /> </td>'+
					'<td class="c" style="text-align: center;"> <input type="radio"    name="emiPrinc" value="'+nome+'" /> </td>'+
					'<td class="c" style="text-align: center;"> <input type="checkbox" name="'+nome+'_campoBusca" value="1" /> </td>'+
					'<td class="c" style="text-align: center;"> <input type="radio"    name="campoIndice" value="'+nome+'" /> </td>'+
					'<td class="c"><a href="javascript:void(0);" onclick="excluirCampo(\''+nome+'\')">[Excluir]</a></td>'+
				'</tr>'
			);
				
			if ($("#parte").attr("checked") == '') {
				$("#campos").val($("#campos").val()+nome+',');
			}
			closeCampo();
		} else {
			$("#cadCampo").val("Erro! Tentar Novamente");
			$("#cadCampo").attr("disabled");
		}
	});//end-get
	
}

function excluirCampo(nome){
	$("#"+nome+"Det").hide();
	$("#campos").val($("#campos").val().replace(','+nome,''));
}

function closeCampo(){
	$("#configCampo").slideUp();
}