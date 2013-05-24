<?php
include '../includeAll.php';
$bd = new BD($conf["DBLogin"], $conf["DBPassword"], $conf["DBhost"], $conf["DBTable"]);

require_once '../classes/nusoap/nusoap.php';

$server = new soap_server;

$server->register('doLogin');
$server->register('getContratos');
$server->register('getObrasFromContrato');
$server->register('setNewMsg');
$server->register('getMessages');
$server->register('doChangePassword');
$server->register('flagLida');

function flagLida($empresaID, $mensagemID) {
	global $bd;
	
	$sql = "SELECT lida FROM empresa_msg_lida WHERE empresaID = $empresaID AND msgID = $mensagemID";
	$lida = $bd->query($sql);
	
	if(count($lida) == 0){
		$sql = "INSERT INTO empresa_msg_lida (empresaID, msgID, lida) VALUES ($empresaID, $mensagemID, 1)";
	} elseif ($lida[0]['lida'] == 0){
		$sql = "UPDATE empresa_msg_lida SET lida = 1 WHERE empresaID=$empresaID AND msgID=$mensagemID";
	} else {
		return array('success' => FALSE);
	}
	return array('success' => $bd->query($sql));
}

function getMessages ($contratoID, $empresaID) {
        global $bd;
        
        $doc = $bd->query('SELECT d.id AS contratoID,d.numeroComp FROM doc_contrato AS c INNER JOIN doc AS d ON c.id = d.tipoID WHERE c.empresaID = '.$empresaID.' AND d.id ='.$contratoID);
        
        $sql = "SELECT e.id, e.nome FROM doc_contrato AS c
        INNER JOIN doc AS d ON c.id = d.tipoID
        INNER JOIN obra_doc ON obra_doc.docID = d.id
        INNER JOIN obra_obra AS o ON o.id = obra_doc.obraID
        INNER JOIN obra_empreendimento AS e ON e.id= o.empreendID
        WHERE c.empresaID = $empresaID AND d.id = $contratoID GROUP BY e.id" ;

        $empreend = $bd->query($sql);
        
        foreach ($empreend as $k => $e) {
                $empreend[$k]['messages'] = getMessageReplies(0, $e['id']);
        }
        
        return array('contrato' => $doc[0] ,'empreend' => $empreend);
}

function doChangePassword($empresaID, $old_password, $new_password) {
	global $bd;
	
	$sql = "UPDATE empresa_login SET password='$new_password', firstPass=0 WHERE empresaID = $empresaID AND password='$old_password'";
	return array('success' => $bd->query($sql));
}

function getMessageReplies($refererMessageID, $empreendID){
        global $bd;
        
        $sql = "SELECT m.id, m.data, u.nome AS nomeUser, e.nome AS nomeEmpresa, m.data, m.assunto, m.conteudo, m. replyTo, ml.lida FROM obra_mensagem as m
                LEFT JOIN empresa AS e ON e.id = m.empresaID
                LEFT JOIN usuarios AS u ON u.id = m.usuarioID
					 LEFT JOIN empresa_msg_lida AS ml ON m.id = ml.msgID
                WHERE m.visibleEmpresa=1 AND m.replyTo = {$refererMessageID} AND m.empreendID = ".$empreendID;
        if($refererMessageID == 0) $sql .= ' order by m.data DESC';
        $msgs = $bd->query($sql);
        
        for($i=0; $i<count($msgs); $i++) {$msgs[$i]['replies'] = array();}
        
        for($i=0; $i<count($msgs); $i++) {
                $msgs[$i]['replies'] = getMessageReplies($msgs[$i]['id'], $empreendID); 
        }
        
        return $msgs;
}

function setNewMsg ($empresaID, $obraID_array, $replyTo, $assunto, $conteudo) {
        global $bd;
        
        if($obraID_array !== null) {
			$sql = "SELECT empreendID as eid FROM obra_obra WHERE ";
			foreach ($obraID_array as $oID) {
					$sql .= " id=".$oID." OR ";
			}
			$sql = rtrim($sql," OR ") . ' GROUP BY empreendID';
		} else {
			$sql = "SELECT empreendID AS eid FROM obra_mensagem WHERE id=".$replyTo;
		}
		$empreendIDs = $bd->query($sql);
        
        foreach ($empreendIDs as $e) {
                $success =  $bd->query("INSERT INTO obra_mensagem (usuarioID, empresaID, empreendID, replyTo, data, assunto, conteudo, anexos, visibleEmpresa) VALUES 
                                                                           (0, $empresaID, {$e['eid']}, $replyTo,".time().", '$assunto', '$conteudo', '', 1 )");
                if($success == false){
                        return false;
                }
        }
        return true;
}
                

function getObrasFromContrato($empresaID, $contratoID) {
        global $bd;
        
        return $bd->query("SELECT o.nome, o.id FROM doc_contrato AS c
        INNER JOIN doc AS d ON c.id = d.tipoID
        INNER JOIN obra_doc ON obra_doc.docID = d.id
        INNER JOIN obra_obra AS o ON o.id = obra_doc.obraID
        WHERE c.empresaID = $empresaID AND d.id = $contratoID");
}

function doLogin($username, $password){ 
        global $bd;
        
        $sql = "SELECT empresaID, active, firstPass FROM empresa_login WHERE username = '$username' AND password = '$password'";
        $res = $bd->query($sql);
        
        if(count($res) === 0)
                return array('success' => FALSE, 'reason' => 'Usuário e/ou senha incorreto(s).');
        elseif ($res[0]['active'] !== '1') {
                return array('success' => FALSE, 'reason' => 'Usuário desativado.');
        } else {
                $sql = "SELECT * FROM empresa WHERE id={$res[0]['empresaID']}";
                $empresa = $bd->query($sql);
                 
                if(count($empresa) == 0)
                        return array('success' => FALSE, 'reason' => 'Empresa não encontrada', 'firstPass' => $res[0]['firstPass']);
                else
                        return array('success' => TRUE, 'reason' => 'OK', 'empresa' => $empresa[0], 'firstPass' => $res[0]['firstPass']);
        }
}

function getContratos($empresaID){
        global $bd;
        
        /*$contratos = $bd->query("SELECT o.empreendID, o.nome, d.numeroComp
        FROM doc_contrato AS c
        INNER JOIN doc AS d ON c.id = d.tipoID
        INNER JOIN obra_doc ON obra_doc.docID = d.id
        INNER JOIN obra_obra AS o ON o.id = obra_doc.obraID
        INNER JOIN obra_empreendimento AS e ON e.id = o.empreendID
        WHERE d.labelID = 10 AND c.empresaID = $empresaID
        ORDER BY d.numeroComp, o.empreendID");
        return $contratos;*/
        $resposta = array();
        
        $contratos = $bd->query("SELECT d.id as id, d.numeroComp as numero FROM doc as d INNER JOIN doc_contrato as c ON d.tipoID = c.id WHERE d.labelID=10 AND c.empresaID = $empresaID");
        foreach ($contratos as $c) {
					 $mensagens_novas = $bd->query("
						SELECT count(distinct m.id) AS msg_novas FROM obra_doc as od
						INNER JOIN obra_obra AS o ON o.id=od.obraID
						inner JOIN obra_mensagem AS m ON o.empreendID=m.empreendID
						left JOIN empresa_msg_lida AS eml ON eml.msgID=m.id
						WHERE m.visibleEmpresa=1 AND od.docID=".$c['id']." AND (eml.lida IS NULL or eml.lida=0)");
                $contr = array("numero" => $c['numero'], 'id' => $c['id'], 'msg_novas' => $mensagens_novas[0]['msg_novas']);
                $empreendimentos = $bd->query("SELECT o.empreendID FROM obra_doc AS od INNER JOIN obra_obra AS o ON od.obraID = o.id WHERE od.docID=".$c['id']." GROUP BY o.empreendID");
                foreach ($empreendimentos as $e) {
                        $empreend = array("empreendID" => $e['empreendID']);
                        $empreend['obras'] = $bd->query("SELECT o.nome FROM obra_doc AS od INNER JOIN obra_obra AS o  ON od.obraID = o.id  WHERE od.docID=".$c['id']." AND o.empreendID = ".$e['empreendID']);
                        
                        $contr['empreend'][] = $empreend;
                }
                
                $resposta[] = $contr;
                
        }
        return $resposta;
}

// Usar o request para invocar o servico
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
?>