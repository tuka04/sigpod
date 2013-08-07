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
//solicitacao 004
function gerenciarContratoAlerta(){
	//construcao da tabela de gerencia
	$.ajax({
		  type:"POST",
		  url: "adm.php",
		  data:{getDatasAlerta:true},
		  cache: false,
		  dataType: "json",
		  success: function(data){
			  var $el = $("#dialogGerenciarAlerta");
			  $el.children("span").remove();
			  $el.children("br").remove();
			  $("#tableAlerta").remove();
			  $el.append(data.tabela);
			  $el.dialog({
				 title:"Administração de alerta contratual",
				 modal:true,
				 autoOpen:true,
				 width:350,
				 height:180,
			  });
			  $('#tableAlerta').tablesorter({ 
					headers: { 0:{sorter:false}} 
				}); 
			  $('#tableAlerta input[type="checkbox"]').enableCheckboxRangeSelection();
		  }
	});
}

function gerenciarContratoOpenAdd(id1,id2,rm){
	var $el = $('#'+id1);
	var $ela = $('#'+id2);
	var $rm = $('#'+rm);
	$el.show("slide",{direction:"left"},500);
	$ela.show("slide",{direction:"left"},500);
	$rm.hide();
	$("#respServerSysAlerta").html("");
}
function gerenciarContratoCancel(rm1,rm2,id,name){
	var $el = $('#'+rm1);
	var $ela = $('#'+rm2);
	var $rm = $('#'+id);
	var $val = $("input[name='"+name+"']");
	$val.attr("value","Dias");
	$val.css("opacity","0.5");
	$el.hide("slide",{direction:"right"},200);
	$ela.hide("slide",{direction:"right"},200);
	$rm.delay(200).show("slide",{direction:"left"},500);
}
function gerenciarContratoClearValue(name){
	var val = $("input[name='"+name+"']").attr('value');
	if(val=="Dias"){
		$("input[name='"+name+"']").attr('value','');
		$("input[name='"+name+"']").css('opacity','1');
	}
}
function gerenciarContratoSave(name,check){
	var $n = $("input[name='"+name+"']");
	var $c = $("input[name='"+check+"']");
	var c=$c.is(":checked")?1:0;
	var exp = /^-?\d\d*$/;
	if(!exp.test($n.attr("value"))){
		alert("Por favor, utilize apenas números maiores que zero e inteiro. Exemplo: 10 ou 36");
		return;
	}
	var val = $n.attr("value");
	$.ajax({
		  type:"POST",
		  url: "adm.php",
		  data:{saveSysAlerta:true,ini:val,diario:c},
		  cache: false,
		  dataType: "json",
		  success: function(data){
			  if(data.success=='true'||data.success==true){
				  $("#respServerSysAlerta").html("Registro inserido com sucesso.");
				  var num_lin = $("#tableAlerta tbody").children().length;//num de linhas
				  var tr = '<tr id="tableAlerta_'+num_lin+'">';
				  var l1='<td><input type="checkbox" id="chk_'+num_lin+'"/>'//linha 1 checkbox
				  		 +'<input type="hidden" name="aid" value="'+data.id+'"/>'
				  		 +'<input type="hidden" name="lid" value="tableAlerta_'+num_lin+'"/>';
				  var l2='<td>'+data.id+'</td>';
				  var l3='<td>'+val+'</td>';
				  $("#tableAlerta tbody").append(tr+l1+l2+l3+"</tr>");
				  gerenciarContratoCancel("sys_alerta_campos","bsc_sys_alerta","addSysAlerta","sys_alerta.ini");
			  }
			  else{
				  $("#respServerSysAlerta").html(data.msg);
			  }
		  }
	});
}
function removeSysAlerta(tr,id){
	var aid = new Array();
	var lid = new Array();
	$("#tableAlerta input[type=checkbox]").each(function(){
		if(this.checked){
			aid.push($(this).parent().children('input[type="hidden"][name="aid"]').attr('value'));
			lid.push($(this).parent().children('input[type="hidden"][name="lid"]').attr('value'));
		}
	});
	if(aid.length==0){
		alert("Por favor, selecione pelo menos um alerta.");
		return;
	}
	$el = $("#rmAlertaSysAlerta");
	if(!$el.attr('id')){
		var html = "<div id='rmAlertaSysAlerta'>Deseja remover o alerta de código: "+aid.toString()+"</div>";
		$('body').append(html);
		$el = $("#rmAlertaSysAlerta");
	}
	else{
		$("#rmAlertaSysAlerta").html("Deseja remover o alerta de código: "+aid.toString()+"</div>");
	}
	$el.dialog({
		resizable: false,
		height:140,
		modal: true,
		autoOpen:true,
		buttons: {
			"Sim": function() {
				$.ajax({
					  url: "adm.php",
					  data:{removeSysAlerta:true,id:aid.toString()},
					  cache: false,
					  dataType: "json",
					  success: function(data){
						  if(data.msg=='true'||data.msg==true){
							  $("#respServerSysAlerta").html("Registro removido com sucesso.");
							  for(var i in lid)
								  $("#"+lid[i]).remove();
						  }
					  }
				});
				$( this ).dialog( "close" );
			},
			"Não": function() {
				$( this ).dialog( "close" );
			}
		}
	});
}
//fim 004
//solicitacao 005
function gerenciarContratoEstado(){
	//construcao da tabela de gerencia
	$.ajax({
		  type:"POST",
		  url: "contrato_estados.php",
		  data:{getSysContratoEstado:true},
		  cache: false,
		  dataType: "json",
		  success: function(data){
			  var $el = $("#"+data.dialogID);
			  var $tb = $("#"+data.tabelaID);
			  $tb.remove();
			  $el.append(data.tabela);
			  $el.dialog({
				 title:"Administração de estado contratual",
				 modal:true,
				 autoOpen:true,
				 width:450,
				 height:300,
			  });
			  $tb.tablesorter({ 
					headers: { 0:{sorter:false}} 
				}); 
			  $('#'+data.tabelaID+' input[type="checkbox"]').enableCheckboxRangeSelection();
		  }
	});
}
function gerenciarEstadoOpenAdd(el,tid){
	$link = $("#"+el.id);
	var $el = $('#'+tid);
	$el.show("slide",{direction:"left"},500);
	$link.hide();
	$("#daoDeletarContratoEstado").hide();
	$("#tableSysContratoEstado").hide();
}
function gerenciarContratoEstadoClearValue(nid,tid){
	var $el = $('#'+tid);
	var $eln = $('#'+nid);
	$("#respServerSysContratoEstado").css("display","none");
	//limpa campos
	$el.children('tbody').each(function(){
		$(this).children('tr').each(function(){
			$(this).children('td').each(function(){
				$(this).children('input').each(function(){
					if($(this).is('input[type="checkbox"]'))
						this.checked = false;
					else
						$(this).attr("value","");
				});
			});
		})
	});
	$el.hide()
	$eln.show("slide",{direction:"left"},500);
	$("#daoDeletarContratoEstado").show("slide",{direction:"left"},500);
	$("#tableSysContratoEstado").show("slide",{direction:"left"},500);
}
function gerenciarContratoEstadoSave(tb){
	var $el = $("#"+tb);
	var nome="",motivo=0,dias=0,data=0;
	var error=false;
	var msgErro="";
	$("#"+tb+" input").each(function(){
		if($(this).is('input[type="checkbox"]')){
			var name = $(this).attr("name").split(".");
			if(name[name.length-1] == "motivo")
				motivo=(this.checked)?1:0;
			else if(name[name.length-1] == "data")
				data=(this.checked)?1:0;
		}
		else if($(this).attr("obr") && $(this).attr("value")==""){
			msgError="Campos com * são obrigatórios.";
			error=true;
			return;
		}
		else{
			var name = $(this).attr("name").split(".");
			if(name[name.length-1] == "nome")
				nome = $(this).attr("value");
			else if(name[name.length-1] == "dias"){
				dias = $(this).attr("value")==""?0:$(this).attr("value");
				var exp = /^-?\d\d*$/;
				if(!exp.test(dias)){
					msgError="Por favor, em Dias, utilize apenas números maiores que zero e inteiro. Exemplo: 10 ou 36";
					error = true;
					return;
				}
			}
		}
	});
	if(error){
		displayMsgError("respServerSysContratoEstado",msgError);
		return;
	}
	$.ajax({
		  type:"POST",
		  url: "contrato_estados.php",
		  data:{saveSysContratoEstado:true,nome:nome,dias:dias,motivo:motivo,data:data},
		  cache: false,
		  dataType: "json",
		  success: function(data){
			  if(data.success=='true'||data.success==true){
				  var num_lin = $("#"+data.tabela+" tbody").children().length;//num de linhas
				  var tr = '<tr id="'+data.tabela+'_'+num_lin+'">';
				  var l1='<td><input type="checkbox" id="chk_'+num_lin+'"/>'//linha 1 checkbox
				  		 +'<input type="hidden" name="eid" value="'+data.id+'"/>'
				  		 +'<input type="hidden" name="lid" value="'+data.tabela+'_'+num_lin+'"/>';
				  var l2='<td>'+data.id+'</td>';
				  var l3='<td>'+nome+'</td>';
				  var l4='<td>'+(motivo?"Sim":"Não")+'</td>';
				  var l5='<td>'+(dias?dias:0)+'</td>';
				  var l6='<td>'+(data?"Sim":"Não")+'</td>';
				  $("#"+data.tabela+" tbody").append(tr+l1+l2+l3+l4+l5+l6+"</tr>");
				  gerenciarContratoEstadoClearValue("linkNovoContratoEstado",tb);
				  displayMsgNotice("respServerSysContratoEstado",data.msg);
			  }
			  else{
				  displayMsgError("respServerSysContratoEstado",data.msg);
			  }
		  }
	});
}
function removeSysContratoEstado(tbID){
	var eid = new Array();
	var lid = new Array();
	$("#"+tbID+" input[type=checkbox]").each(function(){
		if(this.checked){
			eid.push($(this).parent().children('input[type="hidden"][name="eid"]').attr('value'));
			lid.push($(this).parent().children('input[type="hidden"][name="lid"]').attr('value'));
		}
	});
	if(eid.length==0){
		alert("Por favor, selecione pelo menos um estado.");
		return;
	}
	$el = $("#rmSysContratoEstado");
	if(!$el.attr('id')){
		var html = "<div id='rmSysContratoEstado'>Deseja remover o alerta de código: "+eid.toString()+"</div>";
		$('body').append(html);
		$el = $("#rmSysContratoEstado");
	}
	else{
		$("#rmSysContratoEstado").html("Deseja remover o(s) estado(s) de código: "+eid.toString()+"</div>");
	}
	$el.dialog({
		type:"POST",
		resizable: false,
		height:140,
		modal: true,
		autoOpen:true,
		buttons: {
			"Sim": function() {
				$.ajax({
					  url: "contrato_estados.php",
					  data:{removeSysContratoEstado:true,id:eid.toString()},
					  cache: false,
					  dataType: "json",
					  success: function(data){
						  if(data.success=='true'||data.success==true){
							  for(var i in lid)
								  $("#"+lid[i]).remove();
							  displayMsgNotice("respServerSysContratoEstado",data.msg);
						  }
						  else{
							  displayMsgError("respServerSysContratoEstado",data.msg);
						  }
						  
					  }
				});
				$( this ).dialog( "close" );
			},
			"Não": function() {
				$( this ).dialog( "close" );
			}
		}
	});
}
function displayMsgError(divID,msg){
	var classes = "ui-state-error";
	var $el = $("#"+divID);
	$el.removeClass("ui-state-highlight");
	$el.addClass(classes);
	var $el = $("#"+divID);
	$el.show("pulsate",{},250);
	$el.html(msg);
}
function displayMsgNotice(divID,msg){
	var classes = "ui-state-highlight";
	var $el = $("#"+divID);
	$el.removeClass("ui-state-error");
	$el.addClass(classes);
	$el.show("pulsate",{},250);
	$el.html(msg);
}
//fim 005