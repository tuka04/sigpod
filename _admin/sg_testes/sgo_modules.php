<?php

/**
 * Monta o formulario de Busca de obras
 * @return String código HTML do formulario de busca
 */
function showTestePaginaForm() {
    global $conf;
    global $bd;

    $html = showTestePaginaHTML();

    $Campos = '';

    $conTestes = mysql_connect("127.0.0.1:3306", "root", "sigpod");
    if (!$conTestes) {
        die('Could not connect: ' . mysql_error());
    } else {
        mysql_select_db("test", $conTestes);

        $result = mysql_query("SELECT * FROM Persons");

        while ($row = mysql_fetch_array($result)) {
            $Campos .= '<tr><td style="text-align: center;" class="topMenu">' . $row['FirstName'] . " " . $row['LastName'] . '</td></tr>';
        }
    }
    $html = str_ireplace('{Campos_tabela}', $Campos, $html);

    return $html;
}

function showTestePaginaForm2() {
    global $conf;
    global $bd;

    $html = showTestePaginaHTML();
    
    $result = mysql_query("select username from usuarios ORDER by username");

    while ($row = mysql_fetch_array($result)) {
        $Campos .= '<tr><td>' . $row['username'] . '</td></tr>';
    }
    $html = str_ireplace('{Campos_tabela}', $Campos, $html);

    return $html;
}

function showTestePaginaForm3() {
    global $conf;
    global $bd;

    $html = showTestePaginaHTML();
    
    $result = mysql_query("SELECT DISTINCT dc.numeroComp,
				ob.nome,
				dc_proc.assunto
           FROM doc as dc 
     INNER JOIN obra_empreendimento as ob 
     INNER JOIN doc_processo as dc_proc
	 INNER JOIN guardachuva_empreend as gd
          WHERE dc.labelID =1 
            and ob.id = dc.numeroComp
			and dc_proc.numero_pr = dc.numeroComp
			and dc.empreendID = gd.empreendID
	   order by ob.nome");

    while ($row = mysql_fetch_array($result)) {
        $Campos .= '<tr class="c">
                        <td class="cc">' . $row['numeroComp'] . '</td>
                        <td class="cc">' . $row['nome'] . '</td>
                        <td class="cc">' . $row['assunto'] . '</td>
                    </tr>';
    }
    $html = str_ireplace('{Campos_tabela}', $Campos, $html);

    return $html;
}

function showBuscaObrasForm() {
    global $conf;
    global $bd;
    //carrega o layuout basico da pagina de busca
    $html = showBuscaObrasGmaps();
    $tipos_input = '';

    //cria dinamicamente as checkboxes para os tipos
    $tipos = $bd->query("SELECT abrv, nome FROM label_obra_tipo ORDER BY nome");
    foreach ($tipos as $t) {
        $tipos_input .= geraInput("tipo_" . $t['abrv'], array('name' => "tipo_" . $t['abrv'], 'type' => 'checkbox', 'value' => $t['abrv'], "class" => 'tipo')) . " " . $t['nome'] . "<br />";
    }
    $html = str_ireplace('{$tipo_checkbox}', $tipos_input, $html);

    //cria dinamicamente as checkboxes das caracteristicas
    $caract = $bd->query("SELECT abrv, nome FROM label_obra_caract");
    $caract_input = '';

    foreach ($caract as $c) {
        $caract_input .= geraInput("carct_" . $c['abrv'], array('name' => "caract_" . $c['abrv'], 'type' => 'checkbox', 'value' => $c['abrv'], "class" => 'caract')) . " " . $c['nome'] . "<br />";
    }
    $html = str_ireplace('{$caract_checkbox}', $caract_input, $html);

    //seleciona as dimensoes para completar os valores de busca
    $area = $bd->query("SELECT dimensao FROM obra_obra WHERE dimensao IS NOT NULL GROUP BY dimensao ORDER BY dimensao");
    if (count($area) < 2) {
        $a[1] = 0;
        $a[2] = 0;
        $a[3] = 0;
        $a[4] = 0;
    } else {
        $a[1] = $area[round(count($area) / 3)]['dimensao'];
        $a[2] = $area[round(count($area) / 3) + 1]['dimensao'];
        $a[3] = $area[round(count($area) / 3) * 2]['dimensao'];
        $a[4] = $area[round(count($area) / 3) * 2 + 1]['dimensao'];
    }
    //completa os valores de busca
    $html = str_ireplace(array('{$a1}', '{$a2}', '{$a3}', '{$a4}'), $a, $html);

    //busca os valores de custo das obras
    $custo = $bd->query("SELECT custo FROM obra_obra WHERE custo IS NOT NULL GROUP BY custo ORDER BY custo");
    //calcula os valores de busca
    if (count($custo) < 2) {
        $r[1] = 0;
        $r[2] = 0;
        $r[3] = 0;
        $r[4] = 0;
    } else {
        $r[1] = $custo[round(count($custo) / 3)]['custo'];
        $r[2] = $custo[round(count($custo) / 3) + 1]['custo'];
        $r[3] = $custo[round(count($custo) / 3) * 2]['custo'];
        $r[4] = $custo[round(count($custo) / 3) * 2 + 1]['custo'];
    }
    //substitui os valores de busca
    $html = str_ireplace(array('{$r1}', '{$r2}', '{$r3}', '{$r4}'), $r, $html);

    //retorno da pagina formada
    return $html;
}

/**
 * Rotina para gravação de nova obra no BD
 */
function salvaNovaObra() {
    global $bd;

    //cria nova obra,carrega os dados enviados do formulario e salva no BD
    $obra = new Obra($bd);
    $feedback = $obra->saveNew();
    if ($feedback['success']) {
        $obra->logaHistorico(1, array());
    }
    //cria a mensagem HTML de feedback
    $html = verObraFeedback($feedback, 'cad');

    $unOrg = $obra->get('unOrg');
    $nomeUn = attrFromGenericTable('nome, sigla', 'unidades');
    if (count($nomeUn))
        $unOrg .= ' - ' . $nomeUn[0]['nome'] . ' (' . $nomeUn[0]['sigla'] . ')';

    //preenche as variaveis na interface
    $fb_vars = array('{$id_obra}', '{$cod_obra}', '{$nome_obra}', '{$unOrg_obra}');
    $fb_vals = array($obra->get('id'), $obra->get('codigo'), $obra->get('nome'), $unOrg);
    $html = str_ireplace($fb_vars, $fb_vals, $html);

    //retorna a pagina HTML formada para exibicao
    return $html;
}

/**
 * Salva o novo empreendimento no BD e retorna mensagem de feedback
 * @return String codigo HTML do feedback sobre a insercao de novo empreendimento
 */
function salvaNovoEmpreend() {
    global $bd;

    //cria novo empreend, le os dados passados por parametro e salva no BD
    $empreend = new Empreendimento($bd);
    $feedback = $empreend->saveNew();

    //loga sucesso no historico do empreend
    if ($feedback['success']) {
        $empreend->logaHistorico('cadastro');
        doLog($_SESSION['username'], 'Cadastrou empreendimento ' . $empreend->get('nome'));

        if ($empreend->get('responsavel') > 0)
            $empreend->logaHistorico("editarResp", $empreend->get('responsavel'));
    }

    //cria a mensagem HTML de feedback
    $html = verObraFeedback($feedback, 'cadEmpr');

    $html = str_replace(array('{$empreend_nome}', '{$empreend_id}'), array($empreend->get('nome'), $empreend->get('id')), $html);

    return $html;
}

/**
 * Salva os dados modificados de uma obra e retorna a mensagem de feedback
 * @param Obra $obra
 * @param Empreendimento $empreend
 * @param Array $post
 * @return String cod HTML do feedback
 */
function salvaObra(Obra $obra, Empreendimento $empreend, $post) {
    //salva dados da obra
    $res = $obra->save($empreend->get('id'));

    if ($res['success']) {
        //se dados foram salvos com sucesso
        //recarrega os dados da obra
        $obra->load($obra->get('id'));
        //loga historico
        //$res2 = $obra->logaHistorico(2, array());

        /* if($res2['success']){
          //se acao foi logada com sucesso
          //gera feedback
          $fb = verObraFeedback($res2, 'cad');

          doLog($_SESSION['username'], 'Cadastrou obra '.$obra->get('nome').' no empreendimento '.$empreend->get('nome'));
          } else {
          //senao, gera feedback de erro
          $fb = verObraFeedback($res2, 'cad');
          } */
        $fb = verObraFeedback($res, 'cad');
        $empreend->logaHistorico('cadObra', '', '', '', $obra->get('id'));
        //faz troca dos campos pelos nomes corretos
        $fb = str_replace(array('{$id_obra}', '{$cod_obra}', '{$unOrg_obra}', '{$nome_obra}', '{$empreendID}'), array($obra->get('id'), $obra->get('codigo'), getVal($empreend->get('unOrg'), 'compl'), $obra->get('nome'), $empreend->get('id')), $fb);
    } else {
        //se os dados nao foram salvos com sucesso
        //gera feedback de erro
        $fb = verObraFeedback($res);
    }
    //print_r($res2);exit();
    return $fb;
}

/**
 * trata as variaveis GET colocando em um array organizado.
 * @return Array com as variaveis get tratadas
 */
function trataCadVars() {
    //le os dados do formulario enviado e os trata adequadamente segundo cada tipo de dado e cada campo no BD
    $campos = array('nome', 'tipo', 'amianto', 'ocupacao', 'residuos', 'pavimentos', 'elevador', 'latObra', 'lngObra', 'dimensao', 'dimensaoUn', 'unOrgSolic', 'nomeSolic', 'deptoSolic', 'emailSolic', 'ramalSolic', 'caract', 'descricao', 'respProjID', 'respObraID', 'visivel', 'empreendID', 'cod', 'observacoes');
    //trata cada campo do formulario de cadastro de obra
    foreach ($campos as $c) {
        //a funcao deve tratar a variavel apenas se ela existir
        if (!isset($_POST[$c]) || (isset($_POST[$c]) && $_POST[$c] == '')) {
            //campos numericos devem ser NULL ( e nao zero) caso esteja, vazios
            if ($c == 'latObra' || $c == 'lngObra' || $c == 'dimensao' || $c == 'amianto' || $c == 'elevador' || $c == 'pavimentos' || $c == 'ramalSolic' || $c == 'respID' || $c == 'empreendID') {
                $dadosObra[$c] = 'NULL';
            } else {
                //os demais, podem ser vazios
                $dadosObra[$c] = '';
            }
        } else {
            //caso contrario, apenas deve ser tratada a acentuiacao e caracteres especiais.
            $dadosObra[$c] = SGEncode($_POST[$c], ENT_QUOTES, null, false);

            if (($c == 'latObra' || $c == 'lngObra' || $c == 'dimensao' || $c == 'amianto' || $c == 'elevador' || $c == 'pavimentos' || $c == 'montanteRec') && strpos($_POST[$c], ',')) {
                $dadosObra[$c] = str_ireplace(',', '.', $_POST[$c]);
            }
        }
    }
    return $dadosObra;
}

/**
 * Monta a pagina de resumo de uma obra
 * @param Obra $obra
 * @return String codigo HTML do resumo da obra
 */
function showObraResumo($obra, $empreend) {
    global $conf;
    $empreend = new Empreendimento($obra->get('bd'));
    $empreend->load($obra->get('empreendID'), false, false);

    $template = showObraResumoTemplate();
    $html = $template['template'];
    $etapa_tr = '';

    //se a obra possuir imagem, monta as tags de IMG
    if ($obra->desc_img)
        $img = str_replace(array('{$obraCod}', '{$imgNome}', '{$obraNome}'), array($obra->codigo, $obra->desc_img, $obra->nome), $template['img']);
    else //senao, deixa em branco
        $img = '';


    //para cada etapa da obra que foi cadastrada
    foreach ($obra->etapa as $e) {
        //monta a linha da tabela contendo info da etapa
        if (isset($e->processo) && $e->processo)
            $proc = '<a href="javascript:void(0)" onclick="window.open(\'sgd.php?acao=ver&docID=' . $e->processo->id . '\',\'doc\',\'width=\'+screen.width*' . $conf["newWindowWidth"] . '+\',height=\'+screen.height*' . $conf["newWindowHeight"] . '+\',scrollbars=yes,resizable=yes\').focus()">Processo ' . $e->processo->numeroComp . '</a>';
        else
            $proc = 'Nenhum processo';
        //e suibstitui os valores pelos da etapa
        $etapa_tr = str_replace(array('{$etapa_nome}', '{$etapa_proc}', '{$etapa_estado}'), array($e->tipo['nome'], $proc, $e->estado['id']), $template['etapa_tr']);
    }

    //inicializa vetores de recursos
    $rec = array('c' => 0, 'd' => 0);
    $tr = array('c' => '', 'd' => '');

    //para cada recurso encontrado, adiciona ou subtrai o montante
    foreach ($obra->recursos as $r) {
        if ($r->tipo == 'c') {
            $rec['c'] += (floatval($r->montante));
        } else {
            $rec['d'] += (floatval($r->montante));
        }
    }

    //substitui os valores 
    $variaveis = array('{$nome}', '{$img}', '{$unOrg}', '{$descricao}', '{$area}', '{$etapa_tr}', '{$total_c}', '{$total_d}', '{$total_geral}');
    $valores = array($obra->nome, $img, getVal($empreend->get('unOrg'), 'compl'), getVal($obra->get('descricao'), 'label'), $obra->area['compl'], $etapa_tr, number_format($rec['c'], 2, ',', '.'), number_format($rec['d'], 2, ',', '.'), number_format($rec['c'] - $rec['d'], 2, ',', '.'));

    $html = str_replace($variaveis, $valores, $html);

    if (isset($_SESSION['perm'][9]) && $_SESSION['perm'][9] == 1)
        $html = str_replace('{$editar_link}', $template['editar_link'], $html);
    else
        $html = str_replace('{$editar_link}', '', $html);
    //retorna a pagina formada
    return $html;
}

/**
 * Funcao para mostrar detalhes da obra
 * @param int $id
 */
function showObraDetalhes($obra, $empreend) {
    //carrega o template basico
    $template = showObraDetalhesTemplate();

    //seta as variaveis a serem mostradas e substitui os vamores
    $variaveis = array('{$nome}', '{$cod}', '{$unOrg}', '{$tipo}', '{$local}', '{$area}', '{$responsavel_nome}', '{$estado}', '{$ocupacao}', '{$residuos}', '{$elevador}', '{$pavimentos}', '{$amianto}', '{$caract}');
    $valores = array($obra->nome, $obra->codigo, getVal($empreend->get('unOrg'), 'compl'), $obra->tipo['label'], $obra->local['compl'], $obra->area['compl'], $obra->responsavel['nomeCompl'], $obra->estado['label'], $obra->ocupacao['label'], $obra->residuos['label'], $obra->elevador['label'], $obra->pavimentos['label'], $obra->amianto['label'], $obra->caract['label']);
    $html = str_ireplace($variaveis, $valores, $template['template']);

    return $html;
}

/**
 * Mostra os recursos da obra
 * @param Obra $obra
 * @return String Codigo html da pagina de recursos
 */
function showRecursos($recursos, $empreendID, $isObra) {
    $template = showRecursosTemplate();
    $html = $template['template'];
    $rec_html = '';

    //para cada recurso encontrado
    foreach ($recursos as $r) {
        //carrega o template da linha
        $r_tr = $template['recurso_tr'];
        if ($r->prazo)
            $prazo = date("j/n/Y", $r->prazo);
        else
            $prazo = '';
        //completa com o prazo, origem e montante
        if ($_SESSION['perm'][21])
            $editLink = str_replace('{$rec_id}', $r->get('id'), $template['editar_link']);
        else
            $editLink = '';

        $r_tr = str_replace(
                array('{$rec_montante}', '{$rec_origem}', '{$rec_prazo}', '{$rec_mod_user}', '{$rec_mod_data}', '{$rec_id}', '{$rec_justif}', '{$editar_link}'), array(number_format((float) $r->montante, 2, ',', '.'), $r->origem, $prazo, $r->respUser['nomeCompl'], date("j/n/Y G:i", $r->dataUltimaModif), $r->get('id'), $r->justificativa, $editLink), $r_tr);
        $rec_html .= $r_tr;
    }
    //se nao houver recursos adicionados, mosntra mensagem pertinente
    if (count($recursos) == 0) {
        $rec_html = $template['semRec_tr'];
    }
    //substitui
    $html = str_ireplace(
            array('{$recurso_tr}', '{$empreend_id}'), array($rec_html, $empreendID), $template['template']);

    if (!$isObra) {
        $html = str_ireplace(array('{$novoRec_tr}', '{$novoRecLink}'), array(str_ireplace('{$empreend_id}', $empreendID, $template['novoRec_tr']), $template['novoRec_link']), $html);
    } else {
        $html = str_ireplace(array('{$novoRec_tr}', '{$novoRecLink}'), '', $html);
    }

    return $html;
}

/**
 * Carrega as etapas da obras, e monta a pagina de etapas
 * @param Obra $obra
 * @param Empreendimento $empreend
 * @return String codigo HTML das etapas da obra
 */
function showObraEtapas(Obra $obra, Empreendimento $empreend) {
    global $conf;
    //carrega o template basico das etapas
    $template = showObraEtapasTemplate();
    $html = $template['template'];
    $e_html = '';

    //para cada etapa, cria linha de visualização dos detalhes
    foreach ($obra->etapa as $e) {
        $e_tr = $template['etapa_tr'];
        $e_tr_det = '';

        $e_tr = str_ireplace(array('{$etapa_nome}', '{$etapaID}'), array($e->tipo['nome'], $e->getID()), $e_tr);
        if (isset($e->processo) && $e->processo)
            $e_tr = str_ireplace('{$etapa_proc}', '<a href="javascript:void(0)" onclick="window.open(\'sgd.php?acao=ver&docID=' . $e->processo->id . '\',\'doc\',\'width=\'+screen.width*' . $conf["newWindowWidth"] . '+\',height=\'+screen.height*' . $conf["newWindowHeight"] . '+\',scrollbars=yes,resizable=yes\').focus()">Processo ' . $e->processo->numeroComp . '</a>', $e_tr);
        else
            $e_tr = str_ireplace('{$etapa_proc}', '(link) Adicionar Processo', $e_tr);

        $det = ''; //TODO montar as etapas
        /* '<b> 1. Analise</b>

          <table>
          <tr class="c"><td>1.1.1</td><td>Oficio Unidade</td><td><a href=javascript:void(0)>Oficio EXMPL 123/2011</a></td><td><span style="color:green">Concluido</span></td></tr>
          <tr class="c"><td>1.1.2</td><td>Formulario Solicitacao de Obra</td><td><a href=javascript:void(0)>Adicionar</a></td><td><span style="color:red">Pendente</span></td></tr>
          <tr class="c"><td>1.1.3</td><td>Formulario de Abertura de Processo</td><td><a href=javascript:void(0)>Solicitacao de Abertura de Processo 123/2011</a></td><td><span style="color:green">Concluido</span></td></tr>
          </table>
          '; */

        //preenche os dados basicos das etapas
        $e_tr_det = str_replace(array('{$etapaID}', '{$etapa_det}'), array($e->getID(), $det), $template['etapa_det_tr']);

        $e_html .= $e_tr . $e_tr_det;
    }

    // mostra a mensagem de que nao ha etapas se nao houver etapas
    if (!count($obra->etapa))
        $e_html = $template['semEtapa_tr'];

    //montagem array de usuarios
    foreach (getAllUsersName() as $k => $u) {
        $nomes[$k]['value'] = $u['id'];
        $nomes[$k]['label'] = $u['nomeCompl'];
    }

    //
    foreach (getTiposEtapa() as $k => $e) {
        $etapas[$k]['value'] = $e['id'];
        $etapas[$k]['label'] = $e['nome'];
    }

    //substituicao de marcacoes pelo nome
    $html = str_ireplace(array('{$nome}', '{$etapa_tr}'), array($obra->nome, $e_html), $template['template']);

    //retorno da pagina formada	
    return $html;
}

/**
 * Mostra o historico da obra
 * @param Obra $obra
 * @param Empreendimento $empreend
 * @return String codigo html do  historico da obra
 */
function showObraHistorico(Obra $obra, Empreendimento $empreend) {
    global $bd;

    // carrega template
    $html = Historico::getTemplate();

    // inicializa tabela
    $tabela = '';

    // seleciona o histórico da obra
    $historicos = $obra->historico;

    if (count($historicos) <= 0) {
        $historicos = array();
        $tabela = '<tr><td colspan="3"><center><b>Nenhum hist&oacute;rico encontrado.</b></center></td></tr>';
    }

    // cria objeto de histórico
    $hist = HistFactory::novoHist('obra', $bd);

    // percorre lista de histórico
    foreach ($historicos as $h) {
        $hist->load($h['id']);

        $linha = $hist->printHTML();

        $tabela .= $linha['table_row'];
    }

    $html = str_replace('{$linhas_historico}', $tabela, $html);

    return $html;

    /* global $bd;
      //carrega template basico
      $template = showHistoricoTemplate();
      $historico_html = '';

      //seleciona o historico da obra
      $historico = $obra->historico;

      //monta cada entrada em uma linha
      foreach ($historico as $h) {
      $data = $h->get('data');
      $user = $h->get('user');
      $label = $h->get('label');
      $historico_html .= str_replace(array('{$entr_data}', '{$entr_user}', '{$entr_texto}'), array($data['amigavel'], $user['nome'], $label['text']), $template['entrada_tr']);
      }
      //se nao houver entradas, monta a respectiva linha
      if (!count($obra->historico))
      $historico_html = $template['semEntr_tr'];

      // retorna o cod html do historico
      return str_replace(array('{$nome}','{$tr_entradas}'), array($obra->nome, $historico_html), $template['template']); */
}

/**
 * Monta o formulario para edicao de dados da obra
 * @param Obra $obra
 * @param int $empreendID
 * @return codigo htmldo formulario de edicao
 */
function showObraEditForm($obra, $empreendID) {
    global $bd;
    $template = showObraEditFormTemplate();

    //CARACT
    $r = $bd->query("SELECT nome, abrv FROM label_obra_caract");
    foreach ($r as $c) {
        $caracts[] = array('value' => $c['abrv'], 'label' => $c['nome']);
    }
    $campos['caract'] = geraSelect('caract', $caracts, $obra->caract['abrv']);

    //TIPO
    $r = $bd->query("SELECT nome, abrv FROM label_obra_tipo ORDER BY nome");
    foreach ($r as $c) {
        $tipos[] = array('value' => $c['abrv'], 'label' => $c['nome']);
    }
    $campos['tipos'] = geraSelect('tipo', $tipos, $obra->tipo['abrv']);

    //RESPONSAVEL
    $r = getAllUsersName();
    foreach ($r as $u) {
        $users[] = array('value' => $u['id'], 'label' => $u['nomeCompl']);
    }

    //var_dump($obra->responsavel);
    $campos['respProj'] = geraSelect('respProjID', $users, $obra->responsavel['id'], 0);

    /* //RESPONSAVEL
      $r = getAllUsersName();
      foreach ($r as $u) {
      $users[] = array('value' => $u['id'] , 'label' => $u['nomeCompl']);
      }
      $campos['respObra'] = geraSelect('respObraID', $users, $obra->responsavelObra['id'], 0);
     */
    //AMIANTO
    $campos['amianto'] = geraSimNao('amianto', $obra->amianto['bool']);

    //ELEVADOR
    $campos['elevador'] = geraSimNao('elevador', $obra->elevador['bool']);

    //NOVO NOME
    $campos['novoNome'] = geraInput('nome', array('type' => 'text', 'size' => '150', 'maxlength' => '150', 'value' => $obra->nome));

    //OCUP
    $campos['ocupacao'] = geraInput('ocupacao', array('type' => 'text', 'size' => '150', 'maxlength' => '150', 'value' => $obra->ocupacao['valor']));

    //RESIDUOS
    $campos['residuos'] = geraInput('residuos', array('type' => 'text', 'size' => '150', 'maxlength' => '150', 'value' => $obra->residuos['valor']));

    //NUM PAVIMENTOS
    $campos['pavimentos'] = geraInput('pavimentos', array('type' => 'text', 'size' => '5', 'maxlength' => '5', 'value' => $obra->pavimentos['valor']));

    //DESCRICAO
    $campos['descricao'] = geraTextArea('descricao', 150, 3, $obra->descricao['valor']);

    //AREA
    $campos['area'] = geraInput('dimensao', array('type' => 'text', 'size' => '5', 'maxlength' => '10', 'value' => $obra->area['dimensao']))
            . ' ' . geraSelect('dimensaoUn', array(array('value' => 'm', 'label' => 'm'), array('value' => 'm2', 'label' => 'm2'), array('value' => 'm3', 'label' => 'm3'), array('value' => 'kVA', 'label' => 'kVA')), $obra->area['un']['valor']);

    //LOCAL
    $campos['local'] = geraMapSelectionDIV($obra->local['lat'], $obra->local['lng']);

    //PUblico?
    $campos['visivel'] = geraSimNao('visivel', $obra->visivel['bool']);

    //IMAGEM
    $campos['img'] = geraInput('img', array('type' => 'file', 'onclick' => "$('#img_selUp').attr('checked','checked');"));

    //COD
    $campos['cod'] = geraInput('cod', array('type' => 'hidden', 'value' => $obra->get('codigo'))); // 'type' => 'hidden'

    //OBSERVACOES
    $campos['observacoes'] = geraTextArea('observacoes', 150, 3, $obra->observacao);

    $variaveis = array('{$obraID}', '{$empreendID}', '{$nome}', '{$cod}', '{$cod_hidden_input}', '{$descr}', '{$tipo}', '{$local}', '{$area}', '{$estado}', '{$ocupacao}', '{$residuos}', '{$elevador}', '{$pavimentos}', '{$amianto}', '{$caract}', '{$novo_nome}', '{$img}', '{$visivel}', '{$responsavelProj_nome}', '{$observacoes}');
    $valores = array($obra->get('id'), $empreendID, $obra->nome, $obra->codigo, $campos['cod'], $campos['descricao'], $campos['tipos'], $campos['local'], $campos['area'], $obra->estado['label'], $campos['ocupacao'], $campos['residuos'], $campos['elevador'], $campos['pavimentos'], $campos['amianto'], $campos['caract'], $campos['novoNome'], $campos['img'], $campos['visivel'], $campos['respProj'], $campos['observacoes']);
    $html = str_ireplace($variaveis, $valores, $template['template']);

    return $html;
}

/**
 * Monta o formulario para o cadastro de empreendimento
 * @param int $ofirID
 * @return String codigo HTMl do formulario de cadastro de empreendimento
 */
function montaEmpreendCadForm($ofirID) {
    global $bd;

    if (isset($_GET['coord']) && strpos($_GET['coord'], "|") != false) {
        //caso tenha sido selecionado local para obra, despreza alguns algarismos menos significativos
        $pos['lat'] = substr(substr($_GET['coord'], 0, 10), 0, strpos($_GET['coord'], "|"));
        $pos['lng'] = substr($_GET['coord'], strpos($_GET['coord'], "|") + 1, 10);
    } else {
        //caso nao tenha sido selecionado local para a obra, deixa vazio
        $pos['lat'] = '';
        $pos['lng'] = '';
    }

    //le o formualario basico
    $html = showEmpreendCadForm($pos);

    //monta campos de edicao
    $descr = geraTextarea('descricao', 50, 3, '', array('style' => "width: 90%"));
    $justif = geraTextarea('justificativa', 50, 3, '', array('style' => "width: 90%"));
    $local = geraInput('local', array('size' => 85, 'maxlength' => 150));

    $r = getAllUsersName();
    foreach ($r as $u) {
        $users[] = array('value' => $u['id'], 'label' => $u['nomeCompl']);
    }
    $responsavel = geraSelect('responsavel', $users, null, 0);

    $html = str_ireplace(array('{$textarea_justif}', '{$input_local}', '{$textarea_descr}', '{$responsavel}'), array($justif, $local, $descr, $responsavel), $html);

    //se iniciou o cadastro atraves de oficio
    if ($ofirID) {
        $doc = new Documento($ofirID);
        $doc->loadCampos();

        $vars = array('{$ofirID}', '{$ofirNome}', '{$unOrgSolic}', '{$nomeSolic}', '{$deptoSolic}', '{$emailSolic}', '{$ramalSolic}', '{$estilo}', '{$local}');
        $vals = array($ofirID, 'Of&iacute;cio ' . $doc->numeroComp, $doc->campos['unOrg'], $doc->campos['solicNome'], $doc->campos['solicDepto'], $doc->campos['solicEmail'], $doc->campos['solicRamal'], 'background-color: #DDFFDD;', geraMapSelectionDIV($pos['lat'], $pos['lng']));
        $html = str_ireplace($vars, $vals, $html);
        $html .= '<script type="text/javascript">$(document).ready(function() {';
        $html .= 'validateDoc(\'ofir\');';
        $html .= '});</script>';

        //senao nao preenche os dados de oficio
    } else {
        $vars = array('{$ofirID}', '{$ofirNome}', '{$unOrgSolic}', '{$nomeSolic}', '{$deptoSolic}', '{$emailSolic}', '{$ramalSolic}', '{$estilo}', '{$local}');
        $vals = array('', 'Nenhum Selecionado', '', '', '', '', '', '', geraMapSelectionDIV($pos['lat'], $pos['lng']));
        $html = str_ireplace($vars, $vals, $html);
    }

    //monta o formulario na parte de interfaces
    return $html;
}

/**
 * Mostra menu de ações
 * @param Obra $obra
 * @param Array $perm
 * @param Array $itens
 */
function showObraActionMenu(Obra $obra, $perm, $itens) {
    $template_basico = obraActionMenu();

    $links = '';
    //para cada item a ser mostrado, faz o tratamento
    foreach ($itens as $a) {
        if (isset($template_basico['acoes'][$a]) && (($perm != null && $perm[$a]) || $perm == null)) {
            $links .= str_replace(array('{$obraID}', '{$empreendID}'), array($obra->get('id'), $obra->get('empreendID')), $template_basico['acoes'][$a]);
        }
    }
    return str_replace('{$acoes}', $links, $template_basico['estrutura']);
}

/**
 * Mostra menu de ações
 * @param Empreendimento $empreend
 * @param Array $perm
 * @param Array $itens
 */
function showEmpreendActionMenu(Empreendimento $empreend, $perm, $itens) {
    $template_basico = empreendActionMenu();

    $links = '';
    //para cada item a ser mostrado, faz o tratamento
    foreach ($itens as $a) {
        if (isset($template_basico['acoes'][$a]) && (($perm != null && $perm[$a]) || $perm == null)) {
            $links .= str_replace('{$empreendID}', $empreend->get('id'), $template_basico['acoes'][$a]) . '<br />';
        }
    }
    return str_replace('{$acoes}', $links, $template_basico['estrutura']);
}

/**
 * Gera formulário para edicao de empreendimento ja cadastrado
 * @param Empreendimento $empreend
 */
function showEmpreendEditForm($empreend) {
    //carrega o template basico do formulario de edicao de obras
    $template_basico = showEmpreendEditFormTemplate();
    $html = $template_basico['template'];

    //gera os campos para serem preenchidos
    $campo['nome'] = geraInput('nome', array('style' => "width: 90%", 'value' => $empreend->get('nome')));
    $campo['descr'] = geraTextarea('descricao', 50, 3, $empreend->get('descricao'), array('style' => "width: 90%"));
    $campo['justif'] = geraTextarea('justificativa', 50, 3, $empreend->get('justificativa'), array('style' => "width: 90%"));
    $campo['local'] = geraInput('local', array('style' => "width: 90%", 'value' => $empreend->get('local')));
    $campo['unorg'] = getVal($empreend->get('unOrg'), 'compl');
    $campo['nome_solic'] = geraInput('nome_solic', array('style' => "width: 90%", 'value' => getVal($empreend->get('solicitante'), 'nome')));
    $campo['depto_solic'] = geraInput('depto_solic', array('style' => "width: 90%", 'value' => getVal($empreend->get('solicitante'), 'depto')));
    $campo['email_solic'] = geraInput('email_solic', array('style' => "width: 90%", 'value' => getVal($empreend->get('solicitante'), 'email')));
    $campo['ramal_solic'] = geraInput('ramal_solic', array('style' => "width: 90%", 'value' => getVal($empreend->get('solicitante'), 'ramal')));

    $r = getAllUsersName();
    foreach ($r as $u) {
        $users[] = array('value' => $u['id'], 'label' => $u['nomeCompl']);
    }
    $campo['responsavel'] = geraSelect('responsavel', $users, $empreend->get('responsavel'));

    //coloca os campos nos lugares marcados
    $vars = array('{$nome}', '{$descricao}', '{$local}', '{$justif}', '{$unorg}', '{$nome_solic}', '{$depto_solic}', '{$email_solic}', '{$ramal_solic}', '{$empreendID}', '{$responsavel}');
    $vals = array($campo['nome'], $campo['descr'], $campo['local'], $campo['justif'], $campo['unorg'], $campo['nome_solic'], $campo['depto_solic'], $campo['email_solic'], $campo['ramal_solic'], $empreend->get('id'), $campo['responsavel']);

    $html = str_ireplace($vars, $vals, $html);

    //retorna os campos
    return $html;
}

/**
 * Gera feedback de salvamento de Empreendimento
 * @param Empreendimento $empreend
 * @param Array $post
 * @return String
 */
function showEmpreendSalva(Empreendimento $empreend, $post) {
    //se houver mais ou menos que 8 dados recebidos do form, gera erro e retorna feedback desse erro
    if (count($post) != 9) {
        return verObraFeedback(array('success' => false, 'errorNo' => 1, 'errorFeedback' => 'Faltam dados do empreendimento.'));
    }

    //trata os dados recebidos e seta os atributos do empreendimento
    $empreend->set('nome', SGEncode($post['nome'], ENT_QUOTES, null, false));
    $empreend->set('descricao', SGEncode($post['descricao'], ENT_QUOTES, null, false));
    $empreend->set('local', SGEncode($post['local'], ENT_QUOTES, null, false));
    $empreend->set('justificativa', SGEncode($post['justificativa'], ENT_QUOTES, null, false));
    $empreend->set('solicitante', array(
        'nome' => SGEncode($post['nome_solic'], ENT_QUOTES, null, false),
        'depto' => SGEncode($post['depto_solic'], ENT_QUOTES, null, false),
        'email' => SGEncode($post['email_solic'], ENT_QUOTES, null, false),
        'ramal' => SGEncode($post['ramal_solic'], ENT_QUOTES, null, false))
    );
    $empreend->set('responsavel', SGEncode($post['responsavel'], ENT_QUOTES, null, false));

    //salva os atributos do empreendimento
    $fb = $empreend->save();
    //cria mensagem de feedback referene ao sucesso ou nao da operacao
    if ($fb['success']) {
        return str_ireplace(array('{$empreend_id}', '{$empreend_nome}'), array($empreend->get('id'), $empreend->get('nome')), verObraFeedback($fb, 'slvEmpr'));
    } else {
        return verObraFeedback($fb);
    }
}

function showEmpreendSugest($unOrg, BD $bd) {
    $template = showEmpreendSugerirTemplate();
    $unID = substr($unOrg, 0, 17);
    $linhasEmpreend = '';
    //var_dump($unOrg);
    $empreendID = $bd->query("SELECT id FROM obra_empreendimento WHERE unOrg = '$unID' ORDER BY nome");

    foreach ($empreendID as $eID) {
        $empreend = new Empreendimento($bd);
        $empreend->load($eID['id']);
        $atrib = array('{$empreend_nome}', '{$empreendID}', '{$empreend_unOrg_sigla}');
        $vals = array($empreend->get('nome'), $empreend->get('id'), getVal($empreend->get('unOrg'), 'sigla'));
        $linhasEmpreend .= str_replace($atrib, $vals, $template['tr']);
    }

    return $linhasEmpreend;
}

function showObraMiniBusca() {
    $template = showObraMiniBuscaTemplate();

    return $template['template'];
}

function showEmpreendMiniBusca($text) {
    $template = showEmpreendMiniBuscaTemplate();
    $atribs = array('{$texto}');
    $vals = array($text);

    $html = str_replace($atribs, $vals, $template['template']);

    return $html;
}

function showEquipEdit(Empreendimento $empreend) {
    $template = showEquipeFormTemplate();
    $html = $template['template'];

    $equipe = '';
    $usuarios = '';

    $empreend->getEquipe();
    $team = $empreend->get('equipe');
    foreach ($team as $u) {
        $user = getUsers($u);
        if (count($user) <= 0)
            continue;
        $user = $user[0];

        $equipe .= '<option value="' . $user['id'] . '" name="' . $user['nomeCompl'] . '">' . $user['nomeCompl'] . '</option>';
    }

    $pessoas = getAllUsersName();
    foreach ($pessoas as $p) {
        if (in_array($p['id'], $team)) { // verifica se o usuário já está na equipe
            continue;
        }

        $usuarios .= '<option value="' . $p['id'] . '" name="' . $p['nomeCompl'] . '">' . $p['nomeCompl'] . '</option>';
    }

    $html = str_ireplace('{$empreendID}', $empreend->get('id'), $html);
    $html = str_ireplace('{$equipe}', $equipe, $html);
    $html = str_ireplace('{$usuarios}', $usuarios, $html);

    return $html;
}

/**
 * trata data retornando o unixtimestamp das datas no formato dd/mm ou dd/mm/aaaa
 * @param string de entrada
 * @return int data da string de entrada em UnixTimestamp
 * @return null se data no formato invalido
 */
function trataData($string) {
    //separa as datas pelo separador padrao '/' ou '-'
    if (strpos($string, "/") !== false) {
        $string = explode('/', $string);
    } elseif (strpos($string, "-") !== false) {
        $string = explode('-', $string);
    } else {
        return 'NULL';
    }

    //se tiver 2 'pedaços', assume que a data eh do tipo dd/mm
    //se tiver 3 'pedaços', assume que a data eh do tipo dd/mm/aaaa
    //senao, retorna null
    if (count($string) < 2 || count($string) > 3) {
        return 'NULL';
    } elseif (count($string) == 2) {
        $dia = 1;
        $mes = $string[0];
        $ano = $string[1];
    } else {
        $dia = $string[0];
        $mes = $string[1];
        $ano = $string[2];
    }

    $unixtimestamp = mktime(23, 59, 59, $mes, $dia, $ano);

    if (!$unixtimestamp)
        return PHP_INT_MAX;
    else
        return $unixtimestamp;
}

?>