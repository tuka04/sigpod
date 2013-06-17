<?php
class HtmlTable extends HtmlTag{
	/**
	 * Numero de linhas da tabela
	 * @var int 
	 */
	private $num_lines=0;
	/**
	 * Numero de colunas da tabela
	 * @var int
	 */
	private $num_cols=0;
	/**
	 * Cabecalho da tabela
	 * @var HtmlTag
	 */
	private $head;
	/**
	 * Cabecalho da tabela
	 * @var HtmlTag
	 */
	private $body;
		
	public function HtmlTable($id,$class,$num_cols){
		parent::HtmlTag("table", $id, $class);
		$this->head=new HtmlTag("thead", "", "");
		$this->body=new HtmlTag("tbody", "", "");
		$this->num_cols=$num_cols;		
		$this->setChildren($this->head);
		$this->setChildren($this->body);
	}
	
	public function appendLine($val){
		if(count($val)!=$this->num_cols)//erro
			die("Numero de colunas nao bate com o numero de valores");
		if($this->body==null)//tbody nao existe?
			$this->body = new HtmlTag("tbody", "", "");
		$tr = new HtmlTag("tr", $this->getVar("id")."_".$this->num_lines, "");//nova linha
		$this->body->setChildren($tr);
		$td = array();
		foreach ($val as $v)
			$tr->setChildren(new HtmlTag("td", "", "",$v));
		$this->num_lines++;
	}
	/**
	 * seta o estilo de uma coluna
	 * Se deixado -1 o estilo vai para todas as linhas
	 * @param int $i : linha
	 * @param int $j : coluna
	 * @param HtmlTagStyle $style
	 */
	public function setColumnStyle(HtmlTagStyle $style,$i=-1,$j=-1){
		$tr = $this->body->getVar("children");
		$st = $style->toArray();
		$c=0;
		while($tr!=null){
			if($i!=-1){
				if($c==$i){
					$td = $tr->getVar("children");
					$c=0;
					while($td!=null){
						if($j!=-1){
							if($c==$j){
								$td->setStyle(array_keys($st),array_values($st));
								return;
							}
							$c++;
						}
						else{
							$td->setStyle(array_keys($st),array_values($st));
							$td=$td->getVar("next");
						}
					}
					return;
				}
				$c++;
			}
			else{
				$td = $tr->getVar("children");
				while($td!=null){
					if($j!=-1){
						if($c==$j){
							$td->setStyle(array_keys($st),array_values($st));
						}
						$td=$td->getVar("next");
						$c++;
					}
					else{
						$td->setStyle(array_keys($st),array_values($st));
						$td=$td->getVar("next");
					}
				}
				$c=0;
			}
			$tr=$tr->getVar("next");
		}
	}
	/**
	 * seta o estilo de uma linha
	 * Se deixado -1 o estilo vai para todas as linhas 
	 * @param int $i
	 * @param HtmlTagStyle $style
	 */
	public function setLineStyle(HtmlTagStyle $style,$i=-1){
		$tr = $this->body->getVar("children");
		$st = $style->toArray();
		$c=0;
		while($tr!=null){
			if($i!=-1){
				if($c==$i){
					$tr->setStyle(array_keys($st),array_values($st));
					$tr=$tr->getVar("next");
				}
				$c++;
			}
			else{
				$tr->setStyle(array_keys($st),array_values($st));
				$tr=$tr->getVar("next");
			}
		}
	}
	/**
	 * seta o classe de uma linha
	 * Se deixado -1 o estilo vai para todas as linhas
	 * @param int $i
	 * @param HtmlTagStyle $style
	 */
	public function setLineClass($class,$i=-1){
		$tr = $this->body->getVar("children");
		$st = $style->toArray();
		while($tr!=null){
			$tr->setVar("class",$class);
			$tr=$tr->getVar("next");
		}
	}
	
	public function getNumLines(){
		return $this->num_lines;
	}
	
	public function getNumCols(){
		return $this->num_cols;
	}
} 
?>