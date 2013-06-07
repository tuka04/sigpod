<?php
/**
 * @version 1.1.1.3 21/3/2013
 * @package documento.anexo
 * @author Leandro Kümmel Tria Mendes
 * @desc contem atributos de um anexo, tal como dono e métodos tal como desanexar.
 */
require_once 'classes/documento/anexo/IFAnexo.class.php';
class Anexo extends Historico_Doc implements IFAnexo{
	/**
	 * ID do usuario dono do anexo
	 * @var int
	 */
	private $owner;
	/**
	 * Documento associado ao anexo
	 * @var Documento
	 */
	private $docPai;
	/**
	 * @var BD
	 */
	private $bd;
	
	/**
	 * Id desse anexo
	 * @var int
	 */
	private $id;
	
	/**
	 * Construtor
	 * @param BD $bd
	 * @param int $owner : id do usuario dono
	 * @param int $bd : bd var 
	 * @param int $id : id desse documento
	 * @param Documento $docPai : documento pai
	 */
	public function __construct($bd, $owner, $id, $docPai=null){
		$this->import();
		$this->setDocPai($docPai);
		$this->bd = $bd;
		$this->owner=$owner;
		$this->id=$id;
		parent::__construct($bd);
	}
	/**
	 *  @see IFAnexo::import()
	 */
	public function import(){
		require_once 'classes/documento/anexo/IFAnexo.class.php';
	}
	/**
	 * @see IFAnexo::getOwner()
	 */
	public function getOwner(){
		return $this->owner;
	}
	/**
	 * @see IFAnexo::setOwner()
	 */
	public function setOwner($u){
		$this->owner=$u;
	}
	/**
	 * @see IFAnexo::getDoc()
	 */
	public function getDocPai(){
		return $this->docPai;
	}
	/**
	 * @see IFAnexo::setDoc()
	 */
	public function setDocPai($doc){
		$this->docPai = $doc;
	}
	/**
	 * @see IFAnexo::getId()
	 */
	public function getId(){
		return $this->id;
	}
	/**
	 * @see IFAnexo::setId()
	 */
	public function setId($id){
		$this->id = $id;
	}
	/**
	 * @see IFAnexo::remove()
	 */
	public function remove(){
		$sql1 = "UPDATE doc SET anexado = 0, docPaiID = 0, ownerID = ".$this->owner." WHERE id = ".$this->id.";";
		if($this->bd->query($sql1)){
			if($this->docPai->campos['documento'] == '0') {//nao deveria entrar aqui (já que ele tinha um anexo)
				$this->docPai->updateCampo('documento', "");
			} else {
				//vamos remover o documento, lembrando que no momento temos uma string com id1,id2,id3,...,idN
				if(strpos($this->docPai->campos['documento'],",")!==false){
					$d = explode(',',$this->docPai->campos['documento']);
					$key=-1;
					foreach ($d as $k=>$v)
						if($v==$this->id)
							$key=$k;
					if($key>-1)
						unset($d[$key]);
					if(count($d)>0)
						$this->docPai->campos['documento'] = implode(',',array_values($d));
					else
						$this->docPai->campos['documento']="";
				}
				else
					$this->docPai->campos['documento']="";
				$this->docPai->updateCampo('documento', $this->docPai->campos['documento']);
			}
			return true;
		} else {
			return false;
		}
	}
}
?>