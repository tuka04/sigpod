$(document).ready(function(){
	//faz o bind para que na hora de enviar o form, verifique se o cnpj eh valido
	$("#cadEmprForm").submit(function(event){
		if(!valida_cnpj($("#cnpj").val().replace(/[^0-9]/g,""))) {
			alert("CNPJ invalido ou nao preenchido!");
			event.preventDefault();
		}
	});
	
	$("#cnpj").keyup(function(){//formata automaticamente o campo de CNPJ
		
		var v = $("#cnpj").val();//le o valor do input
		
		if(v.length > 19) v = v.slice(0,17);
		
		v = v.replace(/[^0-9]/g,""); //retira tudo que nao eh numero do valor lido
		
		var i, vn="";
		for(i=0 ; i<v.length ; i++){//coloca os pontos, tracos e hifen na posicao correta
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
		$("#cnpj").val(vn);//sobrescreve o valor do formulario com a string formatada
	});
});

//algoritmo para verificar se o CNPJ eh valido
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