<?php
class HistFactory {
	/**
	 * Fábrica de padrões para histórico
	 * Constroi um objeto do histórico dependendo da categoria passada
	 * @param string $categoria
	 * @param BD $bd
	 */
	public static function novoHist($categoria, $bd) {
		if ($categoria == 'doc') {
			return new Historico_Doc($bd);
		}
		elseif ($categoria == 'empreend') {
			return new Historico_Empreend($bd);
		}
		elseif ($categoria == 'obra') {
			return new Historico_Obra($bd);
		}
		else {
			return null;
		}
	}
	
	/**
	 * Retorna Array de IDs dos obs historico de um determinado ID de uma determinada categoria
	 * @param string $categoria
	 * @param int $id
	 * @return array [id]
	 */
	public static function getHistID($categoria, $id, $bd){
		if ($categoria == 'doc') {
			return Historico_Doc::getAllHistID($id, $bd);
		}
		elseif ($categoria == 'empreend') {
			return Historico_Empreend::getAllHistID($id, $bd);
		}
		elseif ($categoria == 'obra') {
			return Historico_Obra::getAllHistID($id, $bd);
		}
		else {
			return null;
		}
	}
}
?>