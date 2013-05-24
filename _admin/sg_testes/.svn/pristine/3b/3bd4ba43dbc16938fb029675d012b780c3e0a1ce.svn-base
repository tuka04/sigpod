function selectAllHolidays() {
	
	var feriados = new Object();
	
	for (var m = 1; m < 13; m++) {
		feriados[m] = new Object();
	}
	
	// ano novo
	feriados[1][1] = true;
	// tiradentes
	feriados[4][21] = true;
	// dia trabalho
	feriados[5][1] = true;
	// rev consti
	feriados[7][9] = true;
	// independencia
	feriados[9][7] = true;
	// nossa sra aparecida
	feriados[10][12] = true;
	// finados
	feriados[11][2] = true;
	// consc negra
	feriados[11][20] = true;
	// proc republica
	feriados[11][15] = true;
	// n. sra conceicao
	feriados[12][8] = true;
	// natal
	feriados[12][25] = true;
	
	for (var i = 1; i < 13; i++) {
		for (var j = 1; j < 32; j++) {
			$("#mes"+(i-1)+" a").each(function() {
				if (feriados[i][j] == true && $(this).html().localeCompare(j) == 0) {
					if (!$(this).hasClass('Feriado')) {
						$(this).parent('td').click();
						hashFeriados[i][j] = true;
					}
				}
			});
		}
	}
	
}

function verificaTercaQuinta(dia, mes, ano) {
	var data = new Date(ano, mes-1, dia);
	
	// verifica se o usuario deseja colocar seg/sextas como feriados tambem quando eles caem em ter/qui
	if ($("input:radio[name='ponto_fac']:checked").val() == 0) {
		return;
	}
	
	// terca feira
	if (data.getDay() == 2) {
		var novoFeriado = new Date(ano, mes-1, dia-1);
		
		if (novoFeriado.getFullYear() == ano) {
			if (novoFeriado.getMonth() != mes-1) {
				$("#mes"+novoFeriado.getMonth()+" a").each(function() {
					if ($(this).html().localeCompare(novoFeriado.getDate()) == 0 && !$(this).hasClass('Feriado')) {
						$(this).parent('td').click();
						$(this).removeClass('ui-state-hover');
						hashFeriados[parseInt(novoFeriado.getMonth(), 10)+1][parseInt(novoFeriado.getDate(), 10)] = true;
					}
				});
			}
			else {
				$("#mes"+(mes-1)+" a").each(function() {
					if ($(this).html().localeCompare(dia-1) == 0 && !$(this).hasClass('Feriado')) {
						$(this).parent('td').click();
						$(this).removeClass('ui-state-hover');
						hashFeriados[parseInt(mes, 10)][parseInt(dia, 10)-1] = true;
					}
				});
			}
		}
	}
	
	
	// quinta feira
	if (data.getDay() == 4) {
		var novoFeriado = new Date(ano, mes-1, parseInt(dia, 10)+1);
		//alert(novoFeriado)
		
		if (novoFeriado.getFullYear() == ano) {
			if (novoFeriado.getMonth() != mes-1) {
				$("#mes"+novoFeriado.getMonth()+" a").each(function() {
					if ($(this).html().localeCompare(novoFeriado.getDate()) == 0 && !$(this).hasClass('Feriado')) {
						$(this).parent('td').click();
						$(this).removeClass('ui-state-hover');
						hashFeriados[parseInt(novoFeriado.getMonth(), 10)+1][parseInt(novoFeriado.getDate(),10)] = true;
					}
				});
			}
			else {
				//alert(2)
				$("#mes"+(mes-1)+" a").each(function() {
					if ($(this).html().localeCompare(parseInt(dia, 10)+1) == 0 && !$(this).hasClass('Feriado')) {
						$(this).parent('td').click();
						$(this).removeClass('ui-state-hover');
						hashFeriados[parseInt(mes,10)][parseInt(dia, 10)+1] = true;
					}
				});
			}
		}
	}
}