<?php
	function insertObra ($d) {
		global $bd;
		return $bd->query("INSERT INTO obra_cad
			(nome,
			local_lat,
			local_lng,
			dimensao,pavimentos,
			tipo,
			amianto,
			ocupacao,
			residuos,
			elevador,
			fase1_reqOFE,
			fase1_SAP) VALUES
			('{$d['nome']}',
			{$d['latObra']},
			{$d['lngObra']},
			{$d['dimensao']},
			'{$d['pavimentos']}',
			'{$d['tipo']}',
			{$d['amianto']},
			'{$d['ocupacao']}',
			'{$d['residuos']}',
			{$d['elevador']},
			{$d['ofir']},
			{$d['saa']}
			)");
	}
	
	function buscaObraSQL() {
		global $bd;
		
		return $bd->query("SELECT * FROM obra_cad");
	}
	
	function insereRecurso($id_obra, $montante, $origem, $prazo) {
		global $bd;
		
		return $bd->query("INSERT INTO obra_rec (obraID,montante,origem, prazo) VALUES ({$id_obra},{$montante},'{$origem}',{$prazo})");
	}
	
	function getLastObra() {
		global $bd;
		
		return $bd->query("SELECT id FROM obra_cad ORDER BY id DESC LIMIT 1");
	}
	/**
	 * Consulta o nome dos tipos de etapa
	 */
	function getTiposEtapa() {
		global $bd;
		
		return $bd->query("SELECT id, nome FROM label_obra_etapa ORDER BY nome");
	}
?>