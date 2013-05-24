/*
 * Funcao que efetua a busca de empresas e monta a linha na tabela de cada uma
*/
function buscaEmpresa(){
	//busca deve ter pelo menos 3 caracteres
	if($("#q").val().length < 3){
		alert("Digite pelo menos 3 letras para a busca");
		return;
	}
	//consulta ajax para buscar uma empresa
	$.post("empresa.php?acao=doBusca",{
		q : escape($("#q").val())
	},
	function(d){
		try {
			d = JSON.parse(d);
		} catch(e) {
			if (e instanceof SyntaxError) {
				alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message+ "\nJSON retornado: " + d);
			}
		}
		
		//mostra DIV de resultados de busca
		var empresas = d.results;
		$("#emprBuscaResLabel").show();
		$("#empresaBuscaResDiv").html('');
		
		//para cada empresa, coloca no div
		if(empresas.length == 0){
			$("#empresaBuscaResDiv").html('<b>Nenhuma empresa encontrada.</b>');
		} else {
			$("#empresaBuscaResDiv").html("Encontrada(s) <b>"+empresas.length+"</b> empresa(s) com a(s) palavra(s) <b>"+$("#q").val()+"</b><br />");
			$("#empresaBuscaResDiv").append('<table id="empresaBuscaResTable" style="width: 100%"> <tr><td class="c" colspan="4"></td></tr>');
			
			var i =0;
			for(i=0;i<empresas.length;i++) {
				var e = '<tr class="c"><td class="c"><a href="javascript:void(0)" onclick="javascript:mostraEmpresa('+empresas[i].id+')"><b>'+empresas[i].nome+'</b></a><br />'+
					empresas[i].html+'</td>'+
					'<td class="c"><a href="javascript:void(0)" onclick="javascript:mostraFunc('+empresas[i].id+')">[ver/gerenciar funcion&aacute;rios]</a></td>';
				//if(d.perm.editarFunc == '1') e += '<td class="c"><a href="javascript:void(0)" onclick="javascript:addFunc('+empresas[i].id+')">[adicionar funcion&aacute;rio]</a></td>';
				if(d.perm.editarEmpresa == '1') e += '<td class="c"><a href="javascript:void(0)" onclick="javascript:editEmpresa('+empresas[i].id+')">[editar empresa]</a></td>';
				e += '</tr>';
				$("#empresaBuscaResTable").append(e);
			}
			
			$("#empresaBuscaResDiv").append('</table>');
		}		
	});
}

//mostra os funcionarios de uma empresa
function mostraFunc(id) {
	//realiza uma consulta ajax para obter os funcionarios de uma empresa
	$.get("empresa.php?acao=getFuncAjax",
		{"empresaID" : id },
		function(d){
			try {
				d = JSON.parse(d);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message+ "\nJSON retornado: " + d);
				}
			}
			//coloca a pagina com a tabela de usuarios no div correspondente
			$("#empresaBuscaDialog").html(d[0].funcHTML);
			//inicializa o dialog
			$("#empresaBuscaDialog").dialog({
				title: "Gerenciar Funcion&aacute;rios da "+d[0].empresaNome,
				autoOpen: true,
				height: 500,
				width: 500,
				modal: true,
				buttons: {
					"Fechar" : function () {
						$("#empresaBuscaDialog").html('');
						$(this).dialog("close");
					}
				}
			});
		});
}

//adiciona um novo funcionario na empresa
function salvaNovoFunc(){
	//realiza consulta ajax para cadastrar funcionario
	$.post("empresa.php?acao=cadFunc",
		{"empresaID" : $("#empresaID").val(),
		 "nome" : escape($("#novoFuncNome").val()),
		 "crea": $("#novoFuncCrea").val()
		 },
		function(d){
			try {
				d = JSON.parse(d);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: " + e.message + "\nJSON retornado: " + d);
				}
			}
			//caso o funcionario tenha sido cadastrado com sucesso
			if(d[0].success == true && d[0].acaoEfetuada != null){
				//mostra mensagem de sucesso
				$("#cadFuncFeedbackDiv").html("Funcion&aacute;rio cadastrado com sucesso");
				$("#cadFuncFeedbackDiv").show();
				//coloca a linha do funcionario no fim da tabela
				$("#func_table").append(unescape(d[0].func_tr).replace(/\{\$\i\}/g,$('.func_tr').length));
				//esconde o form de cadastro
				$("#cadFuncForm").hide();
				//se houver tr de nenhum funcionario, remove
				if($('#empresaSemFunc_tr').length ==  1)
					$('#empresaSemFunc_tr').remove();
				//limpa o form de cadastro
				$("#novoFuncNome").val('');
				$("#novoFuncCrea").val('');
			//se tentou cadastrar um funcionario cujo crea esta associado a uma outra empresa
			} else if(d[0].success == true && d[0].acaoEfetuada == null){
				//associa o funcionario a esta empresa e mostra retorno
				$("#cadFuncFeedbackDiv").html("Usu&aacute;rio j&aacute; cadastrado. Nenhuma a&ccedil;&atilde;o efetuada.");
				$("#cadFuncFeedbackDiv").show();
				//esconde o form de cadastro
				$("#cadFuncForm").hide();
				//reseta o form de cadastro
				$("#novoFuncNome").val('');
				$("#novoFuncCrea").val('');
			//se nao obteve sucesso ao cadastrar funcionario
			} else {
				//mostra erro
				$("#cadFuncFeedbackDiv").html("Erro ao cadastrar funcion&aacute;rio. Tente novamente mais tarde ou contacte um administrador.");
				$("#cadFuncFeedbackDiv").show();
			}
			
		});
}

//funcao que eh executada quando eh clicado no botao de ativar/desativar funcionario
function activateFunc(i){
	$.post("empresa.php?acao=ativarFunc",
		{funcCrea : $("#crea_func"+i).html()},
		function (d){
			try {
				d = JSON.parse(d);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte o administrador e mostre essa mensagem: " + e.message + "\nJSON retornado: " + d);
				}
			}
			//atualiza o estado da celula de ativo
			if(d[0].newStatus == 1){
				$("#ativo_func"+i).html("Sim");
			} else if(d[0].newStatus == 0){
				$("#ativo_func"+i).html("N&atilde;o");
			}
			
		});
}

//funcao que mostra o input de edicao de funcionario
function showEditFunc(i){
	//esconde o nome original no span
	$("#nome_func"+i).hide();
	//mostra o input de edicao
	$("#input_nome_func"+i).show();
	//muda a acao de clicar no link de salvar
	$("#edit_link_func"+i).attr("onclick","doEditFunc("+i+")");
	//muda o texto de editar para salvar
	$("#edit_link_func"+i).html("[Salvar]");
}

//executa edicao de funcionario
function doEditFunc(i){
	//faz requisicao ajax para editar o nome do func
	$.post('empresa.php?acao=editFunc',
		{funcCrea : $("#crea_func"+i).html(),
		 nome : escape($("#input_nome_func"+i).val())},
		 function (d){
			try {
				d = JSON.parse(d);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte o administrador e mostre essa mensagem: " + e.message + "\nJSON retornado: " + d);
				}
			}
			//se a edicao foi bem sucedida
			if(d[0].success == true){
				//atualiza o span do nome do func
				$("#nome_func"+i).html($("#input_nome_func"+i).val());
				//mostra o span com o nome
				$("#nome_func"+i).show();
				//esconde o input
				$("#input_nome_func"+i).hide();
				//troca a acao do link
				$("#edit_link_func"+i).attr("onclick","showEditFunc("+i+")");
				//avisa sobre o salvamento
				$("#edit_link_func"+i).html("[Salvo!]");
			//senao, avisa sobre o erro
			} else {
				$("#edit_link_func"+i).html("[Erro!]");
			}
		});
}

//mostra o form de cadastro de funcionario (ao clicar no link de cadastrar funcionario)
function showCadFuncForm(){
	$("#cadFuncForm").slideDown();
	$("#cadFuncFeedbackDiv").hide();
}

//mostra o dialog com os detalhes da empresa
function mostraEmpresa(i){
	$.post('empresa.php?acao=getEmprDet',
		{empresaID : i},
		 function (d){
			try {
				d = JSON.parse(d);
				} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte o administrador e mostre essa mensagem: " + e.message + "\nJSON retornado: " + d);
				}
			}
			//seta os detalhes da empresa nos devidos span
			$("#nome_empresa").html(d[0].nome);
			$("#cnpj_empresa").html(d[0].cnpj);
			$("#endereco_empresa").html(d[0].endereco);
			$("#complemento_empresa").html(d[0].complemento);
			$("#cidade_empresa").html(d[0].cidade);
			$("#estado_empresa").html(d[0].estado);
			$("#cep_empresa").html(d[0].cep);
			$("#telefone_empresa").html(d[0].telefone);
			$("#email_empresa").html(d[0].email);
			$("#fax_empresa").html(d[0].fax);
			
			//monta o dialog
			$("#empresaDetDialog").dialog({
			title: "Detalhes da empresa "+d[0].nome,
			autoOpen: true,
			height: 400,
			width: 500,
			modal: true,
			buttons: {
				"Ver/Gerenciar Funcionários" : function(){
					$(this).dialog("close");
					mostraFunc(i);
				},
				"Fechar" : function () {
					$("#empresaBuscaDialog").html('');
					$(this).dialog("close");
				}
			}
		});
	 });		
}

//funcao para requisitar infos da empresa para edicao
function editEmpresa(i){
	$.post('empresa.php?acao=getEmprDet',
		{empresaID : i},
		 function (d){
			try {
				d = JSON.parse(d);
				} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte o administrador e mostre essa mensagem: " + e.message + "\nJSON retornado: " + d);
				}
			}
			
			//seta os campos do form com os dados da empresa
			$("#nome").val(unescape(d[0].nome_form));
			$("#cnpj").remove();//remove o campo para edicao de cnpj
			$("#cnpj_static").show();
			$("#cnpj_static").html(d[0].cnpj);
			$("#endereco").val(unescape(d[0].endereco_form));
			$("#complemento").val(unescape(d[0].complemento_form));
			$("#cidade").val(unescape(d[0].cidade_form));
			$("#estado").val(unescape(d[0].estado_form));
			$("#cep").val(unescape(d[0].cep_form));
			$("#telefone").val(unescape(d[0].telefone_form));
			$("#fax").val(unescape(d[0].fax_form));
			$("#email").val(unescape(d[0].email_form));
			$("#cadEmpresaFeedback").hide();
			$("#cadEmpresaFeedback").html('');
			
			//monta o dialog
			$("#empresaEditDialog").dialog({
				title: "Editar empresa "+d[0].nome,
				autoOpen: true,
				height: 500,
				width: 500,
				modal: true,
				buttons: {
					"Salvar" : function(){
						doSalvaEmpresa(i);
					},
					"Fechar" : function () {
						$("#empresaBuscaDialog").html('');
						$(this).dialog("close");
					}
				}
			});
		});	
}

//monta consulta ajax para efetivamente salvar a edicao de dados da empresa
function doSalvaEmpresa(i){
	//passa os dados da empresa a ser editada via ajax para o backend
	$.post('empresa.php?acao=saveEmpresa',
		{empresaID : i,
		 nome : htmlentities($("#nome").val()),
		 endereco : htmlentities($("#endereco").val()),
		 complemento : htmlentities($("#complemento").val()),
		 cidade : htmlentities($("#cidade").val()),
		 estado : htmlentities($("#estado").val()),
		 cep : htmlentities($("#cep").val()),
		 telefone : htmlentities($("#telefone").val()),
		 fax: htmlentities($("#fax").val()),
		 email : htmlentities($("#email").val())
		},
		function(d){
			try {
				d = JSON.parse(d);
			} catch(e) {
				if (e instanceof SyntaxError) {
					alert("Erro encontrado. Contacte o administrador e mostre essa mensagem: " + e.message + "\nJSON retornado: " + d);
				}
			}
			//se a edicao foi bem sucedida, mostra a mensagem de sucesso
			if(d[0].success == true) {
				$("#cadEmpresaFeedback").show();
				$("#cadEmpresaFeedback").html('Empresa salva com sucesso!');
			} else {
				//senao, mostra feedback de erro
				$("#cadEmpresaFeedback").show();
				$("#cadEmpresaFeedback").html('Erro ao salvar empresa: '+d[0].errorFeedback);
			}
			
		});
}
