<?php
CoreHelper::includeJqueryUi();
$user = LoggedUser::getInstance();

//recupero dei parametri
//anno***********
$anno = $cm->oPage->globals["anno"]["value"];
$date = $cm->oPage->globals["data_riferimento"]["value"];

$cdr = new Cdr($cm->oPage->globals["cdr"]["value"]->id);
$anagrafica_cdr = AnagraficaCdrObiettivi::factoryFromCodice($cdr->codice, $date);

$peso_tot_obiettivi = $anagrafica_cdr->getPesoTotaleObiettivi($anno);

$db = ffDb_Sql::factory();

//predisposizione dati per la grid	
//popolamento della grid tramite array		
$chiusura_obiettivi = true;
$mostra_riapertura_obiettivi = false;
//vengono estratti tutti gli obiettivi dell'annohost/budget
$grid_fields = array(
    "ID",
    "codice",
    "titolo",
    "tipo",
    "area_risultato",
    "area",
    "peso",
);
$grid_recordset = array();
$obiettivi_cdr = $anagrafica_cdr->getObiettiviCdrAnno($anno);
foreach ($obiettivi_cdr as $obiettivo_cdr) {
    $obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);

    $obiettivo_aperto = (
        $obiettivo_cdr->data_chiusura_modifiche == null ||
        strtotime(date("Y-m-d")) < strtotime($obiettivo_cdr->data_chiusura_modifiche)
    );

    //basta un obiettivo_cdr ancora attualmente aperto perchè venga visualizzata la chiusura di tutti gli obiettivi_cdr
    if ($chiusura_obiettivi == true && $obiettivo_aperto) {
        $chiusura_obiettivi = false;
    }

    //basta che almeno un obiettivo cdr sia chiuso per visualizzare il pulsante di riapertura
    if(!$mostra_riapertura_obiettivi && !$obiettivo_aperto) {
        $mostra_riapertura_obiettivi = true;
    }

    //vengono recuperate le descrizioni delle chiavi esterne
    $tipo = new ObiettiviTipo($obiettivo->id_tipo);
    $area_risultato = new ObiettiviAreaRisultato($obiettivo->id_area_risultato);
    $area = new ObiettiviArea($obiettivo->id_area);
    $peso = $obiettivo_cdr->peso;
    if ($peso_tot_obiettivi !== 0) {
        $peso_perc = 100 / $peso_tot_obiettivi * $peso;
    } else {
        $peso_perc = 0;
    }

    //asterisco sul codice dell'obiettivo nel caso ci siano azioni definite
    if (strlen($obiettivo_cdr->azioni) > 0) {
        $azioni_definite = "*";
    } else {
        $azioni_definite = "";
    }

    //asterisco sul codice dell'obiettivo nel caso ci siano azioni definite
    if ($obiettivo_cdr->isReferenteObiettivoTrasversale()){
        $coreferente = " (referente)";
    }
    else if ($obiettivo_cdr->isCoreferenza()) {
        $coreferente = " (trasversale)";
    } else {
        $coreferente = "";
    }

    $grid_recordset[] = array(
        $obiettivo_cdr->id,
        $obiettivo->codice . $azioni_definite . $coreferente,
        $obiettivo->titolo,
        $tipo->descrizione,
        $area_risultato->descrizione,
        $area->descrizione,
        $peso . " (" . number_format($peso_perc, 2) . "% su peso obiettivi cdr)",
    );
}
//visualizzazione della grid dei cdr associati all'obiettivo
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "obiettivo-cdr";
$oGrid->title = "Obiettivi del cdr (peso totale " . $peso_tot_obiettivi . ")";
$oGrid->resources[] = "obiettivo-cdr";
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset, "obiettivi_obiettivo_cdr");
$oGrid->order_default = "codice";
$oGrid->record_id = "obiettivo-modify";
$oGrid->order_method = "labels";
$currentModule = core\Modulo::getCurrentModule();
$oGrid->record_url = MODULES_SITE_PATH . $currentModule->site_path . "/dettagli_obiettivo";
$oGrid->use_paging = false;

//operazioni di inserimento ed eliminazione non permesse
$oGrid->display_new = false;
$oGrid->display_delete_bt = false;

$oGrid->addEvent("on_before_parse_row", "initGrid");

//******************************************************************************
//pulsante per la chiusura delle modifiche da parte del responsabile
if (count($obiettivi_cdr)){    
    if ($chiusura_obiettivi == true) {
        $cm->oPage->addContent('<div class="stato_chiusura_obiettivi"><h4>Tutti gli obiettivi risultano chiusi</h4>');
        $cm->oPage->addContent('</div>');

    } else if (!$user->hasPrivilege("resp_cdr_selezionato")) {
        $cm->oPage->addContent('<div class="stato_chiusura_obiettivi"><h4>Obiettivi in attesa di chiusura</h4></div>');
    }
    //TODO percorso tema assoluto in dialog
    else {
        $cm->oPage->addContent("
            <div id='chiusura_obiettivi' class='chiusura_button'>
                <h4>Confermare chiusura obiettivi</h4>
            </div>
            <div id='chiusura_obiettivi_confirm_dialog'>
                <p id='dialog_desc'>
                    Chiudere tutti gli obiettivi? Una volta confermata l'operazione non sarà più possibile modificare azioni ed assegnazioni
                    ed il personale associato potrà visualizzare gli obiettivi di propria competenza.
                </p>
                <div id='loading'>
                    <img src=\"..\..\\themes\ats\images\loader.gif\">
                    <span>Chiusura obiettivi in corso...</span>					
                </div>
            </div>
            <script>
                $( '#loading' ).hide();
                $('#chiusura_obiettivi').on('click', function(e) {
                    e.preventDefault();
                    $('#chiusura_obiettivi_confirm_dialog').dialog('open');
                });

                $('#chiusura_obiettivi_confirm_dialog').dialog({
                    autoOpen: false,
                    modal: true,
                    title: 'Chiusura obiettivi',
                    buttons : {
                        'Conferma chiusura' : function() {
                            chiudiObiettivi();            
                        },
                        'Annulla' : function() {
                            $(this).dialog('close');
                        }
                    },
                    dialogClass: 'chiusura_obiettivi_confirm_dialog',
                    close: function() {
                        window.location.href = '".$_SERVER['REQUEST_URI']."'
                    }
                });

                function chiudiObiettivi(){		
                    $('#chiusura_obiettivi_confirm_dialog').dialog('option', 'buttons', {});	
                    $('#dialog_desc').empty();
                    $( '#loading' ).show();

                    var locationPathname = window.location.pathname;
                    var lastSlashPos = locationPathname.lastIndexOf('/')+1;
                    var url = locationPathname.substr(0, lastSlashPos) + 'chiusura_obiettivi';

                    // Invio richiesta in post								
                    var posting = $.post( url, {
                        cdr: " . $cdr->id . ",
                        anno: " . $anno->id . ","
        );
        if ($cm->oPage->globals["dipendente"]["value"] !== null) {
            $dipendente_selezionato = $cm->oPage->globals["dipendente"]["value"];
            $cm->oPage->addContent("dipendente: " . $dipendente_selezionato->id . ",");
        }
        $cm->oPage->addContent("
                        });

                    posting.done(function( data ) {				
                        response = JSON.parse(data);
                        $('#dialog_desc').empty().append(response.messaggio);
                        if (response.esito === 'success'){
                            $('#chiusura_obiettivi').empty().append('<h4>Tutti gli obiettivi risultano chiusi</h4>');
                            $('#chiusura_obiettivi').addClass('stato_chiusura_obiettivi');
                            $('#chiusura_obiettivi').removeClass('chiusura_button');
                            $('#riapertura_obiettivi').show();
                        }
                    });		

                    posting.fail(function() {	
                        $('#dialog_desc').empty().append('Errore durante la chiusura degli obiettivi');
                    });

                    posting.always(function() { 
                        $( '#loading' ).hide();
                    });
                }
            </script>
        ");
    }
}
else {
    $cm->oPage->addContent('<div class="stato_chiusura_obiettivi"><h4>Nessun obiettivo assegnato al CdR nell\'anno</h4></div>');
}

if($mostra_riapertura_obiettivi && $user->hasPrivilege("obiettivi_aziendali_edit")) {
    $cm->oPage->addContent("
        <div id='riapertura_obiettivi' class='riapertura_button'>
            <h4>Riapertura obiettivi per il CdR</h4>
        </div>
        <div id='riapertura_obiettivi_confirm_dialog'>
            <p id='dialog_desc'>
                Confermare la riapertura degli obiettivi per il CdR?
            </p>
            <div id='loading'>
                <img src=\"..\..\\themes\ats\images\loader.gif\">
                <span>Riapertura obiettivi in corso...</span>					
            </div>
        </div>
        <script>
            $( '#loading' ).hide();
            $('#riapertura_obiettivi').on('click', function(e) {
                e.preventDefault();
                $('#riapertura_obiettivi_confirm_dialog').dialog('open');
            });
            $('#riapertura_obiettivi_confirm_dialog').dialog({
                autoOpen: false,
                modal: true,
                title: 'Riapertura obiettivi',
                buttons : {
                    'Conferma riapertura' : function() {
                        riapriObiettivi();            
                    },
                    'Annulla' : function() {
                        $(this).dialog('close');
                    }
                },
                dialogClass: 'riapertura_obiettivi_confirm_dialog',
                close: function() {
                    window.location.href = '".$_SERVER['REQUEST_URI']."'
                }
            });
            
            function riapriObiettivi() {		
                $('#riapertura_obiettivi_confirm_dialog').dialog('option', 'buttons', {});
                $('#dialog_desc').empty();
                $('#loading').show();

                var locationPathname = window.location.pathname;
                var lastSlashPos = locationPathname.lastIndexOf('/')+1;
                var url = locationPathname.substr(0, lastSlashPos) + 'riapertura_obiettivi';
                
                var posting = $.post(url, {
                    cdr: " . $cdr->id . ",
                    anno: " . $anno->id . "
                });
                
                posting.done(function( data ) {				
                    response = JSON.parse(data);
                    $('#dialog_desc').empty().append(response.messaggio);
                    if (response.esito === 'success'){
                        $('#riapertura_obiettivi').hide();
                    }
                });		

                posting.fail(function() {	
                    $('#dialog_desc').empty().append('Errore durante la riapertura degli obiettivi');
                });

                posting.always(function() { 
                    $( '#loading' ).hide();
                });
            }
        </script> 
    ");
}

//******************************************************************************
// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_obiettivo_cdr";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oField->label = "id";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice";
$oField->base_type = "Text";
$oField->label = "Codice";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "titolo";
$oField->base_type = "Text";
$oField->label = "Titolo";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "tipo";
$oField->base_type = "Text";
$oField->label = "Tipo";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "area_risultato";
$oField->base_type = "Text";
$oField->label = "Area Risultato";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "area";
$oField->base_type = "Text";
$oField->label = "Area";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "peso";
$oField->base_type = "Text";
$oField->label = "Peso";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

function initGrid($oGrid) {
    $cm = cm::getInstance();
    $obiettivo_cdr = new ObiettiviObiettivoCdr($oGrid->key_fields["ID_obiettivo_cdr"]->value->getValue());
    $obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);
    $tipo_obiettivo = new ObiettiviTipo($obiettivo->id_tipo);
    if ($tipo_obiettivo->class !== null) {
        $class = "row_obiettivo_cdr_".$obiettivo_cdr->id;
        $oGrid->row_class = $class;
        $cm->oPage->addContent("<script>$('.".$class."').css('background-color','#".$tipo_obiettivo->class."');</script>");
    } else {
        $oGrid->row_class = "";
    }
}