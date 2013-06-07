<?php
/**
 * @version 1.1.1.3 21/3/2013
 * @package documento.anexo
 * @author Leandro Kümmel Tria Mendes
 * @desc contem atributos de um anexo, tal como dono.
 */
interface IFAnexo{
	/**
	 * @return int
	 */
	public function getOwner();
	/**
	 * @param int $u : id do usuario
	 */
	public function setOwner($u);
	/**
	 * @param Documento $docPai
	 */
	public function setDocPai($docPai);
	/**
	 * @return Documento
	 */
	public function getDocPai();
	/**
	 * @param int $id
	 */
	public function setId($id);
	/**
	 * @return int
	 */
	public function getId();
	/**
	 * Remove este anexo de um documento
	 * @return bool : true em caso de sucesso
	 */
	public function remove();
	/**
	 * Faz update dos campos após a remoção de um anexo
	 * @param string $tb
	 * @param string $campo
	 * @param string $newVal
	 * @return mixed
	 */
// 	private function updateCampo($tb,$campo,$newVal);
} 
?>