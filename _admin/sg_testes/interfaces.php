<?php
function geraMapSelectionDIV($default_lat, $default_lng){
	return 'Lat: '.geraInput('latObra', array('type' => 'text', 'size' => '10', 'maxlength' => '10', 'value' => $default_lat)).' Lng:'.geraInput('lngObra', array('type' => 'text', 'size' => '10', 'maxlength' => '10', 'value' => $default_lng))
			.'<a href="javascript:void(0)" onclick="$(\'#selMap\').show();">Selecionar no mapa</a>
			<div id="selMap" style=" display: none;">
				<b> Ir para campus: </b>
				<a href="javascript:void(0)" onclick="javascript:document.getElementById(\'gmapsRes\').contentWindow.focusCampus(\'unicamp\')">Unicamp</a> | 
				<a href="javascript:void(0)" onclick="javascript:document.getElementById(\'gmapsRes\').contentWindow.focusCampus(\'cpqba\')">CPQBA</a> | 
				<a href="javascript:void(0)" onclick="javascript:document.getElementById(\'gmapsRes\').contentWindow.focusCampus(\'lim1\')">Campus 1</a> | 
				<a href="javascript:void(0)" onclick="javascript:document.getElementById(\'gmapsRes\').contentWindow.focusCampus(\'fop\')">FOP</a> | 
				<a href="javascript:void(0)" onclick="javascript:document.getElementById(\'gmapsRes\').contentWindow.focusCampus(\'cotuca\')">Cotuca</a> | 
				<a href="javascript:void(0)" onclick="javascript:document.getElementById(\'gmapsRes\').contentWindow.focusCampus(\'fca\')">FCA</a> | 
				<a href="javascript:void(0)" onclick="javascript:document.getElementById(\'gmapsRes\').contentWindow.focusCampus(\'pircentro\')">Centro</a>
				<iframe id="gmapsRes" src="sgo_map.php?mode=edt" style="min-height: 500px; width: 100%; height: 75%; padding: 0; margin: 0; border: 0;"></iframe>
			</div>';
}

/**
 * Gera Select
 * @param $id = nome do campo
 * @param $options = as opcoes (formato: array[label][value]
 * @param $selected qual opcao está selecionada
 * @param $defaultVal qual o valor padrão do select
 */
function geraSelect($id, $options, $selected = null, $defaultVal = '',$className = ''){
	$html = '<select id="'.$id.'" class="'.$className.'" name="'.$id.'">
				<option value="'.$defaultVal.'"> -- Selecione -- </option>';
	
	if ($options != null) {
		foreach ($options as $opt) {
			if(!isset($opt['label']) && !isset($opt['value']))
				return $opt['label'] . ' - '. $opt['value'];
			if($opt['value'] === $selected)
				$html .= '<option value="'.$opt['value'].'" selected="selected">'.$opt['label'].'</option>';
			else 
				$html .= '<option value="'.$opt['value'].'">'.$opt['label'].'</option>';
		}
	}
	
	$html .= '</select>';
	
	return $html;
}

function geraSimNao($id, $defaultYes = null) {
	if ($defaultYes) {
		$yes = 'checked = "checked"';
		$no = '';
	} elseif($defaultYes === null) {
		$yes = '';
		$no = '';		
	} else {
		$yes = '';
		$no = 'checked = "checked"';
	}
	
	$html = '<input type="radio" name="'.$id.'" id="'.$id.'_yes" value="1" '.$yes.'> Sim
			 <input type="radio" name="'.$id.'" id="'.$id.'_no" value="0" '.$no.'> N&atilde;o';
	
	return $html;
}

function geraInput($id, $attr){
	$a = '';
	foreach ($attr as $attrib => $value) {
		$a .= $attrib . '="'.$value.'" ';
	}
	
	$html = '<input name="'.$id.'" id="'.$id.'" '.$a.' />';
	
	return $html;
}

function geraTextarea($id, $cols, $rows, $defVal='', $attr=array()) {
	$a = '';
	foreach ($attr as $attrib => $value) {
		$a .= $attrib . '="'.$value.'" ';
	}
	
	return '<textarea name="'.$id.'" id="'.$id.'" cols="'.$cols.'" rows="'.$rows.'" '.$a.' >'.$defVal.'</textarea>'; 
}
?>