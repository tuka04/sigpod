<?php
class JS {
	
	public static function generateJSTagHtml($js){
		return '<script type="text/javascript">'.$js.'
				</script>';
	}
	
	public static function generateJSDocReady($js){
		return '<script type="text/javascript">
				$(document).ready(function(){'.$js.'});</script>';
	}
	
	public static function generateJqueryDialog($seletor){
		$js = '$("'.$seletor.'").autocomplete({
			source: function(request, response) {
				$.get("unSearch.php", {
					q: request.term,
					show: "un"
				}, function(data) {
					try {
						data = eval(data);
					} catch(e) {
						if (e instanceof SyntaxError) {
							alert("Erro encontrado. Contacte seu administrador e mostre essa mensagem: "+ e.message);
						}
					}
					response(data);
				});
			},
			minLength: 2,
			autoFocus: true,
			select: function(){
				$("'.$seletor.'").focus();
			}
		});';
		
		$js .= '$("'.$seletor.'").keyup(function(){
					v = $("'.$seletor.'").val();
					v = v.replace(/\./g,"");
					var expReg  = /^[0-9]{2,12}$/i;
					if (expReg.test(v)){
						var i, vn="";
						for(i=0 ; i<v.length ; i++){
							if(i%2 == 0 && i != 0)
								vn += ".";
							vn += v[i];
						}
						$("'.$seletor.'").val(vn);
					}
				});';
		
		return $js;
	}
	
	public static function getCKEditor(){
		$js = 'CKEDITOR.on("instanceReady", function(ck) { 
					ck.editor.removeMenuItem("paste");	
				});';
		return self::generateJSTagHtml($js);
	}
	/**
	 * Retorna o codigo javascript que controla o interface de outroAno do campo anoSelect
	 * @param string $nome
	 * @return string
	 * @see LabelCampo.class.php
	 */
	public static function getLabelCampoAnoSelect($nome){
		$js = '	$("#'.$nome.'2").hide();
				$("#'.$nome.'1").on("change",function(){
					$("#'.$nome.'").val($("#'.$nome.'1").val());
					if($("#'.$nome.'outroAno").attr("selected")){
						$("#'.$nome.'").val("");
						$("#'.$nome.'1").hide();
						$("#'.$nome.'2").show();
						$("#'.$nome.'2").focus();
					}
				});
				$("#'.$nome.'2").on("keyup",function(){
					$("#'.$nome.'").val($("#'.$nome.'2").val());
				});';
		return $js;
	}
	
	public static function getDatePicker($nome){
		$js = '$("#'.$nome.'").datepicker({
						dateFormat: "dd/mm/yy",
						regional: "pt-BR",
						showOtherMonths: true,
						constrainInput: true,
						selectOtherMonths: true,
						constrainInput: true,
						appendText: "(dd/mm/aaaa)",
						onSelect: function() {
							trataData($(this));
							$(this).focus();
						}
				});
				$("#'.$nome.'").bind("keydown", function(e) {
					if (e.keyCode == 8 || e.keyCode == 46)
						return;
					var texto = $(this).val();
					if (texto.length == 2)
						$(this).val(texto + "/");
					else if (texto.length == 5)
						$(this).val(texto + "/");
				});';
		return $js;
	}
	/**
	 * @param String $s: seletor jquery
	 * @return string
	 */
	public static function getHideElem($s){
		$js = '$("'.$s.'").hide();';
		return $js;
	}
}
?>