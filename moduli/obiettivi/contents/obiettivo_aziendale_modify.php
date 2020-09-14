<?php
$user = LoggedUser::Instance();
if (!$user->hasPrivilege("obiettivi_aziendali_edit")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione degli obiettivi aziendali.");
}
//recupero dei parametri
//anno***********
$anno = $cm->oPage->globals["anno"]["value"];

if (isset($_REQUEST["keys[ID_obiettivo]"])) {
    $obiettivo = new ObiettiviObiettivo($_REQUEST["keys[ID_obiettivo]"]);
    if ($obiettivo->data_eliminazione !== null) {
        ffErrorHandler::raise("Errore nel passaggio dei parametri: ID_obiettivo di un obiettivo eliminato.");
    }
    $title = "Modifica obiettivo";
    $ob_desc = "Obiettivo " . $obiettivo->codice;
} else {
    $title = "Nuovo obiettivo";
    $ob_desc = "Nuovo";
}

$db = ffDb_Sql::factory();

//definizione del record
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "obiettivo-aziendale-modify";
$oRecord->title = $title;
$oRecord->resources[] = "obiettivo";
$oRecord->src_table = "obiettivi_obiettivo";

//viene definita sul record l'eliminazione logica del record piuttosto che quella fisica
$oRecord->del_action = "update";
$oRecord->del_update = "data_eliminazione=" . $db->toSql(date("Y-m-d H:i:s"));

//evento per propagazione eliminazione
$oRecord->addEvent("on_done_action", "editRelations");

$oRecord->addContent(null, true, "descrizione");
$oRecord->groups["descrizione"]["title"] = $ob_desc;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_obiettivo";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField, "descrizione");

$oField = ffField::factory($cm->oPage);
$oField->id = "titolo";
$oField->label = "Titolo";
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->required = true;
$oRecord->addContent($oField, "descrizione");

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->label = "Descrizione";
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->required = true;
$oRecord->addContent($oField, "descrizione");

//origine
foreach (ObiettiviOrigine::getAttiviAnno($anno) AS $origine) {
    $origine_select[] = array(
        new ffData($origine->id, "Number"),
        new ffData($origine->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_origine";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $origine_select;
$oField->label = "Origine";
$oField->required = true;
$oRecord->addContent($oField, "descrizione");

//tipo
foreach (ObiettiviTipo::getAttiviAnno($anno) AS $tipo) {
    $tipo_select[] = array(
        new ffData($tipo->id, "Number"),
        new ffData($tipo->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_tipo";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $tipo_select;
$oField->label = "Tipo";
$oField->required = true;
$oRecord->addContent($oField, "descrizione");

//area_risultato
foreach (ObiettiviAreaRisultato::getAttiviAnno($anno) AS $area_risultato) {
    $area_risultato_select[] = array(
        new ffData($area_risultato->id, "Number"),
        new ffData($area_risultato->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_area_risultato";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $area_risultato_select;
$oField->label = "Area Risultato";
$oField->required = true;
$oRecord->addContent($oField, "descrizione");

//Area
foreach (ObiettiviArea::getAttiviAnno($anno) AS $area) {
    $area_select[] = array(
        new ffData($area->id, "Number"),
        new ffData($area->descrizione, "Text")
    );
}
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_area";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $area_select;
$oField->label = "Area";
$oField->required = true;
$oRecord->addContent($oField, "descrizione");

$oRecord->addContent(null, true, "indicatori");
$oRecord->groups["indicatori"]["title"] = "Indicatori";

$oField = ffField::factory($cm->oPage);
$oField->id = "indicatori";
$oField->label = "Descrizione indicatori";
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oField->required = true;
$oRecord->addContent($oField, "indicatori");

//in caso di modifica
if (isset($obiettivo)) {
    //******************************************************************************
    //indicatori
    //recupero degli indicatori collegati all'obiettivo
    $grid_fields = array(
        "ID",
        "n_parametro_calcolo",
        "nome",
        "descrizione",
        "valore_target_aziendale_anno",
        "valore_target_aziendale_obiettivo",
    );
    $grid_recordset = array();
    $n_parametro_calcolo = 0;
    foreach ($obiettivo->getIndicatoriAssociati($where = array(), $order = array("ordine" => "ASC")) as $indicatore) {
        $valore_target_aziendale = $indicatore->getValoreTargetAnno($anno);

        count($valore_target_aziendale) ?
                $valore_target_aziendale = $valore_target_aziendale : $valore_target_aziendale = "ND";

        strlen($indicatore->obiettivo_indicatore->valore_target) ?
                $valore_target_indicatore_obiettivo = $indicatore->obiettivo_indicatore->valore_target : $valore_target_indicatore_obiettivo = "ND";

        $grid_recordset[] = array(
            $indicatore->obiettivo_indicatore->id,
            ++$n_parametro_calcolo,
            $indicatore->nome,
            $indicatore->descrizione,
            $valore_target_aziendale,
            $valore_target_indicatore_obiettivo,
        );
    }

    //visualizzazione della grid degli indicatori definiti per l'obiettivo
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "obiettivo-indicatore";
    $oGrid->title = "Indicatori associati all'obiettivo";
    $oGrid->resources[] = "obiettivo-indicatore";
    $oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "indicatori_indicatore");
    $oGrid->order_default = "nome";
    $oGrid->record_id = "obiettivo-indicatore-modify";
    $oGrid->order_method = "labels";
    //costruzione dell'url del record (viene selelezionata la directory corrente con substr (path - ultima_parte_del_path))
    $path_info_parts = explode("/", $cm->path_info);
    $path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))));
    $oGrid->record_url = FF_SITE_PATH . $path_info . "indicatori/obiettivo_indicatore_modify";
    $oGrid->use_paging = false;
    $oGrid->full_ajax = true;

    // *********** FIELDS ****************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_obiettivo_indicatore";
    $oField->data_source = "ID";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "n_parametro_calcolo";
    $oField->base_type = "Text";
    $oField->label = "N parametro per calcolo";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "nome";
    $oField->base_type = "Text";
    $oField->label = "Nome";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "descrizione";
    $oField->base_type = "Text";
    $oField->label = "Descrizione";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "valore_target_aziendale_anno";
    $oField->base_type = "Text";
    $oField->label = "Valore target aziendale indicatore " . $anno->descrizione;
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "valore_target_aziendale_obiettivo";
    $oField->base_type = "Text";
    $oField->label = "Valore target aziendale indicatore-obiettivo " . $anno->descrizione;
    $oGrid->addContent($oField);

    // *********** ADDING TO PAGE ****************
    $oRecord->addContent($oGrid, "indicatori");
    $cm->oPage->addContent($oGrid);

    //******************************************************************************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "formula_calcolo_raggiungimento";
    $oField->label = "Formula per il calcolo del raggiungimento dell'obiettivo sulla base degli indicatori";
    $oField->base_type = "Text";
    $oRecord->addContent($oField, "indicatori");

    //******************************************************************************
    //Grid assegnazione obiettivo_cdr
    //predisposizione dati per la grid	
    //popolamento della grid tramite array		
    $date = $cm->oPage->globals["data_riferimento"]["value"];
    $grid_fields = array(
        "ID",
        "ID_cdr",
        "desc_cdr",
        "peso",
        "data_chiusura_modifiche",
    );
    $grid_recordset = array();

    $associato_a_cdr = false;
    foreach ($obiettivo->getObiettivoCdrAssociati() as $obiettivo_cdr) {
        if($obiettivo_cdr->data_eliminazione == null) {
            $associato_a_cdr = true;
            if($obiettivo_cdr->isObiettivoCdrAziendale()) {
                //recupero descrizione cdr dal piano attivo del tipo di piano con priorità più alta
                $tipo_piano_cdr = Cdr::getTipoPianoPriorita($obiettivo_cdr->codice_cdr, $date->format("Y-m-d"));
                $piano_cdr = PianoCdr::getAttivoInData($tipo_piano_cdr, $date->format("Y-m-d"));
                try {
                    $cdr = Cdr::factoryFromCodice($obiettivo_cdr->codice_cdr, $piano_cdr);
                    $cdr_desc = $cdr->codice . " - " . $cdr->descrizione;
                } catch (Exception $ex) {
                    $cdr_desc = "Codice cdr non valido / obsoleto";
                }

                $peso = $obiettivo_cdr->peso;
                $anagrafica_cdr_assegnato_ob = AnagraficaCdrObiettivi::factoryFromCodice($cdr->codice, $date);
                $peso_tot_cdr = $anagrafica_cdr_assegnato_ob->getPesoTotaleObiettivi($anno);
                if ($peso_tot_cdr == 0) {
                    $peso_perc = 0;
                } else {
                    $peso_perc = 100 / $peso_tot_cdr * $peso;
                }
                $grid_recordset[] = array(
                    $obiettivo_cdr->id,
                    $cdr->id,
                    $cdr_desc,
                    $peso . " / " . $peso_tot_cdr . " (" . number_format($peso_perc, 2) . "%)",
                    $obiettivo_cdr->data_chiusura_modifiche
                );
            }
        }
    }

    $oRecord->addContent(null, true, "obiettivo_cdr");
    $oRecord->groups["obiettivo_cdr"]["title"] = "Cdr Associati all''obiettivo";

    //visualizzazione della grid dei cdr associati all'obiettivo
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "obiettivo-cdr";
    $oGrid->title = "Cdr";
    $oGrid->resources[] = "obiettivo-cdr";
    $oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "obiettivi_obiettivo_cdr");
    $oGrid->order_default = "desc_cdr";
    $oGrid->record_id = "obiettivo-cdr-modify";
    $oGrid->order_method = "labels";
    //costruzione dell'url del record (viene selelezionata la directory corrente con substr (path - ultima_parte_del_path))
    $path_info_parts = explode("/", $cm->path_info);
    $path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))));
    $oGrid->record_url = FF_SITE_PATH . $path_info . "dettagli_obiettivo";
    
    // *********** FIELDS ****************
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_obiettivo_cdr";
    $oField->data_source = "ID";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_cdr";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "desc_cdr";
    $oField->base_type = "Text";
    $oField->label = "Cdr";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "peso";
    $oField->base_type = "Text";
    $oField->label = "Peso / TOT peso cdr";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "data_chiusura_modifiche";
    $oField->base_type = "Text";
    $oField->label = "Data chiusura modifiche";
    $oGrid->addContent($oField);

    // *********** ADDING TO PAGE ****************
    $oRecord->addContent($oGrid, "obiettivo_cdr");
    $cm->oPage->addContent($oGrid);
    //******************************************************************************

    $oRecord->addEvent("on_do_action", "myRiaperturaObiettivo");
    if ($user->hasPrivilege("obiettivi_aziendali_edit") && $associato_a_cdr) {
        $oBt = ffButton::factory($cm->oPage);
        $oBt->id = "action_button_riapertura";
        $oBt->label = "Riapertura obiettivo";
        $oBt->action_type = "submit";
        $oBt->jsaction = "$('#inactive_body').show();$('#conferma_riapertura').show();";
        $oBt->aspect = "link";
        $oBt->class = "fa-unlock";
        $oRecord->addActionButton($oBt);
        $oRecord->addEvent("on_do_action", "myRiaperturaObiettivo");
        $cm->oPage->addContent("
        <div id='inactive_body'></div>
        <div id='conferma_riapertura' class='conferma_azione'>
            <h3>Confermare la riapertura dell'obiettivo<br/>'".$obiettivo->titolo."'?</h3>
            <a id='conferma_si' class='confirm_link'>Conferma</a>
            <a id='conferma_no' class='confirm_link'>Annulla</a>
        </div>
        <script>
            $('#conferma_si').click(function(){
                document.getElementById('frmAction').value = 'obiettivo-riapertura';
                document.getElementById('frmMain').submit();
            });
            $('#conferma_no').click(function(){
                $('#inactive_body').hide();
                $('#conferma_riapertura').hide();
            });
        </script>
    ");
    }
}
//in caso di inserimento viene creato il codice incrementale riseptto all'anno da attribuire all'obiettivo
else {
    $obiettivi_anno = ObiettiviObiettivo::getAll(array("ID_anno_budget" => $anno->id));

    //getall estrae gli obiettivi ordinati per codice in maniera decrementale, per avere l'id da assegnare basta soltanto incrementare di 1 l'ID del primo elemento estratto
    if ($obiettivi_anno == null) {
        $codice_nuovo_obiettivo = 1;
    } else {
        $codice_nuovo_obiettivo = $obiettivi_anno[0]->codice_incr_anno + 1;
    }
    $oRecord->additional_fields["codice_incr_anno"] = $codice_nuovo_obiettivo;
}

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oRecord);

$oRecord->additional_fields["data_ultima_modifica"] = new ffData(date("Y-m-d H:i:s"));
$oRecord->additional_fields["ID_anno_budget"] = new ffData($anno->id);

//propagazione dell'eliminazione sulle relazioni
function editRelations($oRecord, $frmAction) {
    switch ($frmAction) {
        case "delete":
        case "confirmdelete":
            $obiettivo = new ObiettiviObiettivo($oRecord->key_fields["ID_obiettivo"]->value->getValue());
            
            // Eliminazione logica degli obiettivi cdr associati
            foreach ($obiettivo->getObiettivoCdrAssociati() as $obiettivo_cdr) {
                $obiettivo_cdr->logicalDelete();
            }
            // Eliminazione fisica degli indicatori_obiettivo_indicatore associati
            foreach ($obiettivo->getIndicatoriAssociati() as $obiettivo_indicatore) {
                $obiettivo_indicatore->obiettivo_indicatore->delete();
            }
            
            if(isset($_GET["ret_url"]) && !empty($_GET["ret_url"])) {
                $ret_url = $_GET["ret_url"];
            } else {
                $cm = cm::getInstance();
                $path_info_parts = explode("/", $cm->path_info);
                $path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
                $ret_url = FF_SITE_PATH.$path_info."?".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST);
            }
            ffRedirect($ret_url);
            
            break;
    }
}

function myRiaperturaObiettivo($oRecord, $frmAction) {
    if (isset($_REQUEST["frmAction"])) {
        $frmAction = $_REQUEST["frmAction"];
        
        if ($frmAction == "obiettivo-riapertura") {
            $id_obiettivo = $oRecord->key_fields["ID_obiettivo"]->value->getValue();
            $obiettivo = new ObiettiviObiettivo($id_obiettivo);
            try {
                $obiettivo->riaperturaObiettiviCdrCollegati();
                // Redirect a self
                ffRedirect($_SERVER['REQUEST_URI']);
            }
            catch (Exception $e) {
                CoreHelper::setError($oRecord, "Impossibile effettuare la riapertura per obiettivo");
            }
        }
    }
}