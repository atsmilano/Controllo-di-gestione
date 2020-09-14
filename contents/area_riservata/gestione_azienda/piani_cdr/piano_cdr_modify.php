<?php
$clone = false;

if(isset($_REQUEST["clone"]) && $_REQUEST["clone"] == "true") {
    $clone = true;
} 

$piano_cdr = getPianoCdr($clone);
$display_introduzione = false;
$id_piano_cdr = 0;
if($piano_cdr == null) {
    $display_introduzione = false;
    unset($piano_cdr);
} else {
    if(!isset($piano_cdr->data_introduzione)) {
        $display_introduzione = true;
    }
    
    $id_piano_cdr = $piano_cdr->id;
}

$isModifica = false;

//definizione del record
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "piano-cdr";
$oRecord->title = "Piano cdr";
$oRecord->resources[] = "piano-cdr";
$oRecord->src_table = "piano_cdr";
$oRecord->allow_update = false;

if(!$clone) {
    if($id_piano_cdr == 0) {
        //viene permesso solo l'inserimento
        $oRecord->allow_delete = false;
    } else if(isset($piano_cdr) && !isset($piano_cdr->data_introduzione)){
        $isModifica = true;
        //vengono permesse solo cancellazione ed introduzione
        $oRecord->record_exist = true;
        $oRecord->allow_insert = false;
        
    } else {
        //disabilitazione di tutte le operazioni
        $oRecord->allow_insert = false;
        $oRecord->allow_delete = false;
    }
    $oRecord->addEvent("on_done_action", "creaCdrRadice");
} else {
    //In clonazione viene abilitato solo l'inserimento
    $oRecord->record_exist = false;
    $oRecord->allow_delete = false;
    $oRecord->allow_insert = false;
}


$oRecord->addEvent("on_do_action", "checkRelations");
$tipi_piano_cdr = getTipiPianoCdr(true);

$oField = ffField::factory($cm->oPage);
$oField->id = "id_piano_cdr";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "id_tipo_piano_cdr";
$oField->data_source = "ID_tipo_piano_cdr";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $tipi_piano_cdr;
$oField->label = "Piano cdr";
$oField->required = true;

if($clone) {
    $oField->default_value = new ffData($piano_cdr->id_tipo_piano_cdr, "Number");
    $oField->properties["disabled"] = "disabled";
}
if ($isModifica) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "data_definizione";
$oField->base_type = "Date";
$oField->label = "Data definizione";
$oField->widget = "datepicker";
$oField->required = true;
if ($isModifica) {
    $oField->control_type = "label";
    $oField->store_in_db = false;
}
$oRecord->addContent($oField);
if($display_introduzione && !$clone) {

    $oBt = ffButton::factory($cm->oPage);
    $oBt->id = "introduzione";
    $oBt->label = "Introduzione piano";
    $oBt->action_type = "submit";
    $oBt->jsaction = "$('#conferma_introduzione').show();";
    $oBt->aspect = "link";
    $oBt->class = "fa-edit";
    $oRecord->addActionButton($oBt);

    $oRecord->addContent("
        <div id='conferma_introduzione' style='display:none'>
            <h3>Confermare l'introduzione del piano cdr definito in data " . DateTime::createFromFormat("Y-m-d", $piano_cdr->data_definizione)->format("d/m/Y") . "?</h3>
            <a id='conferma_si_introduzione' class='conferma_si confirm_link'>Conferma</a>
            <a id='conferma_no_introduzione' class='conferma_no confirm_link'>Annulla</a>
        </div>
        <script>
            $('.conferma_si').click(function(){
                if ($('#conferma_introduzione').is(':visible')) {
                    //document.getElementById('frmAction').value = 'piano-cdr_introduzione';
                    ff.ajax.ctxDoAction('piano_cdr_action_dialog', 'piano-cdr_introduzione', 'piano-cdr_');
                }
                
                document.getElementById('frmMain').submit();
                
            });
            $('.conferma_no').click(function(){
                //$('#inactive_body').hide();

                if ($('#conferma_introduzione').is(':visible')) {
                    $('#conferma_introduzione').hide();
                }
                
            });
           
        </script>
    ");
}

$cm->oPage->addContent($oRecord);

if($clone) {
    $oField = ffField::factory($cm->oPage);
    $oField->id = "clone-error";
    $oField->store_in_db = false;
    $oField->base_type = "Text";
    $oRecord->addHiddenField($oField);

    $oBt = ffButton::factory($cm->oPage);
    $oBt->id = "clone";
    $oBt->label = "Clonazione piano";
    $oBt->action_type = "submit";
    $oBt->jsaction = "clone();";
    $oBt->aspect = "link";
    $oBt->class = "fa-edit";
    $oRecord->addActionButton($oBt);

    $oRecord->addContent("
        <script>
            function clone() {
                ff.ajax.ctxDoAction('piano_cdr_action_dialog', 'piano-cdr_clone', 'piano-cdr_');
            }
        </script>
    ");    
}

$cm->oPage->addContent($oRecord);

function getTipiPianoCdr($selection) {
    $tipi_piano_cdr = TipoPianoCdr::getAll();
    $tipi_piano = array();

    foreach($tipi_piano_cdr as $tipo_piano_cdr) {
        if($selection) {
            $tipi_piano[] = array(
                new ffData($tipo_piano_cdr->id, "Number"),
                new ffData($tipo_piano_cdr->descrizione, "Text"),
            );
        } else {
            $tipi_piano[] = $tipo_piano_cdr->id;
        }
    }
    return $tipi_piano;
}

function checkRelations($oRecord, $frmAction) {
    $data_definizione_form = $oRecord->form_fields["data_definizione"]->value->getValue();
    $id_tipo_piano_cdr_form = $oRecord->form_fields["id_tipo_piano_cdr"]->value->getValue();

    //in clonazione, non si possono effettuare modifiche o cancellazioni    
    $piano_cdr = getPianoCdr();
    //Se il piano è già stato introdotto, non si possono eseguire operazioni.
    if(isset($piano_cdr) && isset($piano_cdr->data_introduzione)) {
        return true;
    }
    
    $str_error_data_definizione = "Impossibile inserire un piano con data di definizione antecedente o uguale alla data di definizione dell'ultimo piano creato";
    switch ($frmAction) {        
        case "confirmdelete":
            foreach($piano_cdr->getCdr() as $cdr) {
                foreach ($cdr->getCdc() as $cdc) {
                    $cdc->delete();
                }
                //vengono eliminati tutti i cdr del piano prima di effettuare l'eliminazione.
                $cdr->delete();
            }
            break;
        
        // In cascata viene verificata la data di introduzione di un piano per evitare sovrapposizioni
        case "update":        
            //condizione che non dovrebbe verificarsi, aggiunta per robustezza.                        
            if($piano_cdr != null && $frmAction != "update") {
                return true;
            }
            
            $last_data_definizione = getLastDataDefinizione($id_tipo_piano_cdr_form);
            $data_definizione = strtotime(DateTime::createFromFormat("d/m/Y", $data_definizione_form)->format("Y-m-d"));

            //Se non ci sono piani introdotti oppure la data di definizione del piano da inserire
            //è precedente alla data di definizione dell'ultimo piano introdotto, viene segnalato un errore.
            if($last_data_definizione != null && $data_definizione <= $last_data_definizione) {
                $oRecord->strError = isset($oRecord->strError) && $oRecord->strError != "" 
                    ? $oRecord->strError : $str_error_data_definizione;
                return true;
            }
            
            break;
        case "insert":
            //condizione che non dovrebbe verificarsi, aggiunta per robustezza.                        
            if($piano_cdr != null && $frmAction != "insert") {
                return true;
            }
            
            $last_data_definizione = getLastDataDefinizione($id_tipo_piano_cdr_form);
            $data_definizione = strtotime(DateTime::createFromFormat("d/m/Y", $data_definizione_form)->format("Y-m-d"));

            //Se non ci sono piani introdotti oppure la data di definizione del piano da inserire
            //è precedente o pari alla data di definizione dell'ultimo piano introdotto, viene segnalato un errore.
            if($last_data_definizione != null && $data_definizione <= $last_data_definizione) {
                $oRecord->strError = isset($oRecord->strError) && $oRecord->strError != "" 
                    ? $oRecord->strError : $str_error_data_definizione;
                return true;
            }

            break;
        case "piano-cdr_clone":
            $cm = Cm::getInstance();
            $piano_cdr = getPianoCdr(true);
    
            //Se il piano cdr da clonare non è settato, viene interrotta l'esecuzione
            if($piano_cdr == null) {
                return true;
            }
            $last_data_definizione = getLastDataDefinizione($id_tipo_piano_cdr_form);

            //La data viene ricevuta in formato d/m/Y, è necessario convertirla in Y-m-d
            $data_definizione = strtotime(DateTime::createFromFormat("d/m/Y", $data_definizione_form)->format("Y-m-d"));
            //Se non ci sono piani introdotti oppure la data di definizione del piano da inserire
            //è precedente o pari alla data di definizione dell'ultimo piano introdotto, viene segnalato un errore.
            if($last_data_definizione != null && $data_definizione <= $last_data_definizione) {
                $oRecord->strError = isset($oRecord->strError) && $oRecord->strError != "" 
                    ? $oRecord->strError : $str_error_data_definizione;
                return true;
            }

            if(clonaPiano($oRecord)) {
                return true;
            }

            $oRecord->addContent("<script>document.getElementById('frmMain').submit();</script>");
            return true;
            break;
            
        case "piano-cdr_introduzione": 
            if(!empty($frmAction) && $frmAction == "piano-cdr_introduzione") {
                $id_piano_cdr_form = $oRecord->key_fields["id_piano_cdr"]->value->getValue();
                $piano_cdr = new PianoCdr($id_piano_cdr_form);
                $piano_cdr->introduzionePiano();
            } else {
                return true;
            }
            break;
            
        default:
            break;
    }
}

function getPianoCdr($clone=false) {
    $id = $clone ? "id_piano_cdr_old" : "id_piano_cdr";
    if (isset($_REQUEST["keys[".$id."]"])) {
        $id_piano_cdr = $_REQUEST["keys[".$id."]"];
        //se il parametro di selezione del cdr risulta valido viene utilizzato
        if ($id_piano_cdr != 0) {
            try {
                $piano_cdr = new PianoCdr($id_piano_cdr);
                return $piano_cdr;
            } catch (Exception $ex) {
                ffErrorHandler::raise($ex->getMessage());
            }
        } 
    }
    return null;
}

//Restituisce la data di definizione più recente tra i piani di una determinata tipologia.
function getLastDataDefinizione($id_tipo_piano_cdr) {
    $filters = array(
        "ID_tipo_piano_cdr" => $id_tipo_piano_cdr
    );
    return strtotime(PianoCdr::getAll($filters)[0]->data_definizione);
}

function creaCdrRadice($oRecord, $frmAction) {
    $data_definizione_form = $oRecord->form_fields["data_definizione"]->value->getValue();
    if($frmAction == "insert") {
        $data_definizione = DateTime::createFromFormat("d/m/Y", $data_definizione_form);
        $id_anagrafica = AnagraficaCdr::getAnagraficaInData($data_definizione)[0]->id;
        try {
            $cdr = new Cdr();
            $cdr->id_anagrafica_cdr = $id_anagrafica;
            $cdr->id_padre = 0;
            $cdr->id_piano_cdr = $oRecord->key_fields["id_piano_cdr"]->getValue();
            $cdr->save();
        } catch (Exception $ex) {
            ffErrorHandler::raise($ex);
        }
    }  
}

//Clona i cdr e i cdc del piano selezionato
function clonaPiano($oRecord) {
    $data_definizione_form = DateTime::createFromFormat("d/m/Y", $oRecord->form_fields["data_definizione"]->value->getValue())->format("Y-m-d");
    $id_tipo_piano_cdr_form = $oRecord->form_fields["id_tipo_piano_cdr"]->value->getValue();
    $piano_old = getPianoCdr(true);

    if ($piano_old === null) {
        $oRecord->strError = "Impossibile recuperare il piano per la clonazione";
        return true;
    }
    
    //viene recuperato il padre
    $cdr_piano_padre = Cdr::getAll(
        array(
            "ID_piano_cdr" => $piano_old->id,
            "ID_padre" => 0
        )
    );

    //creazione di un nuovo piano cdr
    $piano_cdr_new = new PianoCdr();
    $piano_cdr_new->id_tipo_piano_cdr = $id_tipo_piano_cdr_form;
    $piano_cdr_new->data_definizione = $data_definizione_form;

    try {
        $id_piano_cdr_new  = $piano_cdr_new->save()->value_numeric_integer;
        clonaPianoActions($id_piano_cdr_new, $cdr_piano_padre[0]);
    }
    catch(Exception $exc) {
        $oRecord->strError = $exc->getMessage();
        return true;
    }
}

function clonaPianoActions($id_piano_cdr_new, $cdr_old, $last_inserted_id = 0) {
    
    $cdr_new = new Cdr();
    $cdr_new->id_anagrafica_cdr = $cdr_old->id_anagrafica_cdr;
    $cdr_new->id_piano_cdr = $id_piano_cdr_new;
    $cdr_new->id_padre = $last_inserted_id;
    $cdr_new->id = $cdr_new->save();
    
    // viene recuperato l'ID appena inserito
    $last_inserted_id = $cdr_new->id->value_numeric_integer;
    
    // vengono clonati i CdC
    foreach ($cdr_old->getCdc() as $cdc_cdr) {
        $cdc = new Cdc();
        $cdc->id_anagrafica_cdc = $cdc_cdr->id_anagrafica_cdc;
        $cdc->id_cdr = $last_inserted_id;
        $cdc->save();
    }
    
    // vengono clonati i figli
    foreach($cdr_old->getFigli() as $cdr_figlio) {
        clonaPianoActions($id_piano_cdr_new, $cdr_figlio, $last_inserted_id);
    }
}
