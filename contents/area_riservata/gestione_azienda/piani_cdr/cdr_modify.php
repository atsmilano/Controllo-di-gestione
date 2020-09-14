<?php
//recupero e validazione dei parametri
if (isset($_REQUEST["id_piano_cdr"])) {
    $id_piano_cdr = $_REQUEST["id_piano_cdr"];
    //se il parametro di selezione del cdr padre risulta valido viene utilizzato
    if ($id_piano_cdr != 0) {
        try {
            $piano_cdr = new PianoCdr($id_piano_cdr);
        } catch (Exception $ex) {
            ffErrorHandler::raise($ex->getMessage());
        }
    }
} else
    ffErrorHandler::raise("Errore nel passaggio dei parametri: piano cdr");

if (isset($_REQUEST["id_padre"])) {
    $id_cdr_padre = $_REQUEST["id_padre"];
    //se il parametro di selezione del cdr padre risulta valido viene utilizzato
    if ($id_cdr_padre != 0) {
        try {
            $cdr_padre = new Cdr($id_cdr_padre);
        } catch (Exception $ex) {
            ffErrorHandler::raise($ex->getMessage());
        }
    }
} else
    ffErrorHandler::raise("Errore nel passaggio dei parametri: cdr padre");

if (isset($_REQUEST["keys[id_cdr]"])) {
    $id_cdr = $_REQUEST["keys[id_cdr]"];
    //se il parametro di selezione del cdr risulta valido viene utilizzato
    if ($id_cdr != 0) {
        try {
            $cdr = new Cdr($id_cdr);
        } catch (Exception $ex) {
            ffErrorHandler::raise($ex->getMessage());
        }
    }
} else
    $id_cdr = 0;

//CONTROLLI DI COERENZA
//viene verificato che l'id del piano dei cdr corrisponda al piano del cdr padre e nel caso del cdr figlio
if (
    !(
        (
        $id_cdr_padre == 0 &&
        $id_cdr == 0
        ) ||
        (
        $id_cdr_padre !== 0 &&
        $id_cdr !== 0 &&
        $piano_cdr->id == $cdr_padre->id_piano_cdr &&
        $piano_cdr->id == $cdr->id_piano_cdr
        ) ||
        (
        $id_cdr_padre !== 0 &&
        $piano_cdr->id == $cdr_padre->id_piano_cdr
        ) ||
        (
        $id_cdr !== 0 &&
        $piano_cdr->id == $cdr->id_piano_cdr
        )
    )
) {
    ffErrorHandler::raise("Errore nel passaggio dei parametri: piani dei cdr non corrispondenti");
}
//************************************************************************************************
//definizione del record
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "cdr";
$oRecord->title = "Cdr";
$oRecord->resources[] = "cdr";
$oRecord->src_table = "cdr";

//in caso di inserimento viene abilitata la action e viene salvato l'id del cdr_padre
if (isset($piano_cdr->data_introduzione)) {
    $oRecord->allow_insert = false;
    $oRecord->allow_update = false;
    $oRecord->allow_delete = false;
} else if ($id_cdr == 0) {
    $oRecord->allow_update = false;
    $oRecord->allow_delete = false;
    $oRecord->insert_additional_fields["ID_padre"] = new ffData($cdr_padre->id, "Number");
    $oRecord->insert_additional_fields["ID_piano_cdr"] = new ffData($piano_cdr->id, "Number");
} else {
    //Modifica e cancellazione
    $oRecord->record_exist = true;
    $oRecord->allow_insert = false;
    if (isset($cdr) && $cdr->id_padre == 0) {
        $oRecord->allow_delete = false;
    }
}
$oRecord->addEvent("on_do_action", "checkRelations");

$anagrafiche = getAnagrafiche($piano_cdr, $cdr_padre, $id_cdr, true);
//Viene considerato nelle opzioni anche il cdr.
if ($id_cdr != 0) {
    $anagrafiche[] = array(
        new ffData($cdr->id_anagrafica_cdr, "Number"),
        new ffData($cdr->codice . " - " . $cdr->descrizione, "Text"),
    );
}

//Al cambiamento di valore del menu a tendina, mediante ajax viene reperito il responsabile dell'anagrafica cdr.
$ajax_resp = '
    <script>
        $("#loading-responsabile").hide();
        //$("#data_intro").hide();
        //document.getElementById("cdr_responsabile").disabled = true;
        
        function ajax_responsabile() {
            var idAnagrafica = $("#cdr_ID_anagrafica_cdr").val();
            $("#loading-responsabile").show();
            $.ajax({
                url: window.location.pathname + "/controller.php",
                type: "get",
                data: {
                    id_anagrafica: idAnagrafica,
                    data_definizione: \'' . $piano_cdr->data_definizione . '\',
                    id_cdr_padre:' . ($cdr_padre != null ? $cdr_padre->id : 0) . '
                },
                success: function(data) {
                    response = JSON.parse(data);
                    if(response.esito === "success") {
                        document.getElementById("cdr_responsabile").disabled = false;
                        document.getElementById("cdr_responsabile").value = response.messaggio;
                        document.getElementById("cdr_responsabile").disabled = true;
                        $("#loading-responsabile").hide();
                    } else {
                        alert(response.messaggio);
                    }
                }, 
                error: function(xhr) {
                    alert("Errore nell\'elaborazione della richiesta");
                }
            });  
        }
        $("#cdr_ID_anagrafica_cdr").change(function(){
            ajax_responsabile();
        });   
';

//Se il cdr esiste, viene aggiunto il codice per il calcolo del responsabile all'apertura della pagina
if ($id_cdr != 0) {
    $ajax_resp .= '
        document.getElementById("cdr_responsabile").value = "";
        $(document).ready(function () {
            var idCdr = document.getElementById("cdr_keys[id_cdr]").value;
            if(idCdr != 0 && idCdr != "" && idCdr != null) {
                ajax_responsabile();
            }
        });';
}
$ajax_resp .= "</script>";

$cm->oPage->addContent($ajax_resp);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_anagrafica_cdr";
$oField->data_source = "ID_anagrafica_cdr";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = $anagrafiche;
$oField->label = "Anagrafica";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "responsabile";
$oField->label = "Responsabile";
$oField->store_in_db = false;
$oField->properties["disabled"] = "disabled";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "id_cdr";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$cm->oPage->addContent($oRecord);
$cm->oPage->addContent('<div id="loading-responsabile">');
$cm->oPage->addContent('<img id="loading-img" src="' . FF_SITE_PATH . '/themes/ats/images/loader.gif" ></div>');

function checkRelations($oRecord, $frmAction) {
    $id_cdr = $oRecord->key_fields["id_cdr"]->value->getValue() != ""
            ? $oRecord->key_fields["id_cdr"]->value->getValue()
            : 0;
    $cdr_padre = null;
    //Si prova ad instanziare il piano ed il cdr padre, in caso di insuccesso le operazioni sul db non vanno eseguite
    try {
        $piano_cdr = new PianoCdr($_REQUEST["id_piano_cdr"]);

        if ($_REQUEST["id_padre"] != 0) {
            $cdr_padre = new Cdr($_REQUEST["id_padre"]);
        }
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage()); //condizione per debug   
    }
    
    //Viene verificata la possibilità di effettuare operazioni sulla base della data di introduzione
    if (isset($frmAction) && $frmAction == "insert" || $frmAction == "update" || $frmAction == "delete") {
        if ($piano_cdr->data_introduzione != null || $piano_cdr->data_introduzione != "") {
            $oRecord->strError = isset($oRecord->strError) && $oRecord->strError != "" ? $oRecord->strError : "Impossibile modificare un piano già introdotto";
            return true;
        }
    }

    //In caso di inserimento e modifica vine verificato che l'anagrafica passata come parametro sia una scelta possibile
    if (isset($frmAction) && ($frmAction == "insert" || $frmAction == "update")) {
        $anagrafiche = getAnagrafiche($piano_cdr, $cdr_padre, $id_cdr, false);
        $id_anagrafica = $oRecord->form_fields["ID_anagrafica_cdr"]->value->getValue();
        if (!in_array($id_anagrafica, $anagrafiche)) {
            return true;
        }
    }

    if ($frmAction == "confirmdelete") {
        $id_cdr = $_REQUEST["keys[id_cdr]"];
        //Se non si riesce ad istanziare l'oggetto cdr, non sarà possibile eliminare il cdr dal db.
        if (isset($id_cdr) && $id_cdr != 0) {
            try {
                $cdr = new Cdr($id_cdr);
            } catch (Exception $ex) {
                ffErrorHandler::raise($ex);
                return true;
            }
        } else {
            return true;
        }
        //Se il cdr selezionato è il cdr radice, allora non sarà possibile eliminarlo
        if ($cdr->id_padre == 0) {
            return true;
        }
        
        //Vengono eliminati i cdc afferenti al cdr        
        foreach($cdr->getCdc() as $cdc) {
             $cdc->delete();
        }
        //Vengono eliminati i cdr di tutto il ramo gerarchico
        try {
            $cdr = new Cdr($oRecord->key_fields["id_cdr"]->value->getValue());
            $cdr->useSql = true;
            $gerarchia = $cdr->getGerarchia();
            foreach ($gerarchia as $nodo) {
                if ($nodo['livello'] != 0) {
                    $cdr = new Cdr($nodo['cdr']->id);
                    $cdr->delete();
                }
            }
        } catch (Exception $ex) {
            ffErrorHandler::raise($ex);
        }
    }
}

function getAnagrafiche($piano_cdr, $cdr_padre, $id_cdr, $selection) {
    $id_anagrafiche_piano = array();
    foreach ($piano_cdr->getCdr() as $cdr) {
        $id_anagrafiche_piano[] = $cdr->id_anagrafica_cdr;
    }

    $tipo_cdr_figli = array();
    //Vengono recuperate le anagrafiche dei figli
    if ($cdr_padre != null) {
        $tipo_cdr_padre = new TipoCdr($cdr_padre->id_tipo_cdr);
        foreach ($tipo_cdr_padre->getFigli() as $tipo_figli) {
            $tipo_cdr_figli[] = $tipo_figli->id;
        }
    } else {
        foreach (TipoCdr::getAll() as $tipo_figli) {
            $tipo_cdr_figli[] = $tipo_figli->id;
        }
    }
    
    $tipo_padri_cdr_figli = array();
    $tipi_cdr_possibili = array();
    if($id_cdr != 0) {
        $cdr = new Cdr($id_cdr);
        $cdr->useSql = true;
        $i=0;
        foreach($cdr->getFigli() as $cdr_figlio) {           
            $tipo_cdr_figlio = new TipoCdr($cdr_figlio->id_tipo_cdr);
            $tipo_padri_cdr_figli[$i] = array();
            foreach($tipo_cdr_figlio->getPadri() as $padri_possibili) {
                array_push($tipo_padri_cdr_figli[$i], $padri_possibili->id);
            }
            $i++;
        }

        foreach($tipo_padri_cdr_figli as $tipo_padri_cdr_figlio) {
            if(sizeof($tipi_cdr_possibili) == 0) {
                $tipi_cdr_possibili = $tipo_padri_cdr_figlio;
            } else {
                $tipi_cdr_possibili = array_intersect($tipi_cdr_possibili, $tipo_padri_cdr_figlio);
            }
        }
    } 
        
    $anagrafiche = array();
    foreach (AnagraficaCdr::getAnagraficaInData(DateTime::createFromFormat("Y-m-d", $piano_cdr->data_definizione)) as $anagraficheAttive) {
        //Vincolo sui figli di un cdr, seleziono solo le anagrafiche possibili
        //Vincolo sull'unicità del cdr nel piano, seleziono solo le anagrafiche possibili
        $condition = in_array($anagraficheAttive->id_tipo_cdr, $tipo_cdr_figli) 
                && !in_array($anagraficheAttive->id, $id_anagrafiche_piano);
        if($id_cdr != 0 && sizeof($tipi_cdr_possibili) > 0) {
            $condition = $condition && in_array($anagraficheAttive->id_tipo_cdr, $tipi_cdr_possibili);
        }
        if($condition) {
            
            if ($selection) {
                $anagrafiche[] = array(
                    new ffData($anagraficheAttive->id, "Number"),
                    new ffData($anagraficheAttive->codice . " - " . $anagraficheAttive->descrizione, "Text"),
                );
            } else {
                $anagrafiche[] = $anagraficheAttive->id;
            }
        }
    }

    return $anagrafiche;
}