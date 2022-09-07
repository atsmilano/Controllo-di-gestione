<?php
//viene verificata la correttezza del parametro ed i privilegi dell'utente su di esso
$user = LoggedUser::getInstance();

//TODO privilegi per mappatura
if (isset($_REQUEST["keys[ID_mappatura_periodo]"])) {
    
    try {
        $mappatura_periodo = new MappaturaCompetenze\MappaturaPeriodo($_REQUEST["keys[ID_mappatura_periodo]"]);
        $profilo = new MappaturaCompetenze\Profilo($mappatura_periodo->id_profilo);               
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }   
}
else {
    ffErrorHandler::raise("Errore nel passaggio dei parametri.");
}

$modulo = Modulo::getCurrentModule();
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("tabella_mappatura.html", "main");

$tpl->set_var("ret_url", $_REQUEST["ret_url"]);
$tpl->set_var("module_theme_path", $modulo->module_theme_full_path);
$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
$tpl->set_var("id_mappatura_periodo", $mappatura_periodo->id);

//viene estratto l'array di valori attesi selezionabili
$valori = $profilo->getValoriAssegnabili();

//intestazione per la mappatura
$valutatore = \Personale::factoryFromMatricola($mappatura_periodo->matricola_valutatore); 
$valutato = \Personale::factoryFromMatricola($mappatura_periodo->matricola_personale);
$tpl->set_var("desc_valutatore", $valutatore->cognome." ".$valutatore->nome." (matr. ".$valutatore->matricola.")");
$tpl->set_var("desc_valutato", $valutato->cognome." ".$valutato->nome." (matr. ".$valutato->matricola.")");
$periodo_mappatura = new \MappaturaCompetenze\Periodo($mappatura_periodo->id_periodo);
$tpl->set_var("desc_periodo", $periodo_mappatura->descrizione." (".$periodo_mappatura->data_riferimento_inizio." - ".$periodo_mappatura->data_riferimento_fine.")");
$tipi_mappatura = \MappaturaCompetenze\MappaturaPeriodo::getTipiMappatura();
$tpl->set_var("desc_tipo_mappatura", $tipi_mappatura[array_search($mappatura_periodo->id_tipo_mappatura, array_column($tipi_mappatura, 'ID'))]["descrizione"]);
$tpl->parse("SectIntestazione", true);
$tpl->set_var("colspan_sezione", 4);
$tpl->parse("SectMappaturaIntestazione", true);

//competenze trasversali
foreach ($profilo->getCompetenzeTrasversaliProfilo() as $competenza_trasversale_profilo) {
    $competenza_trasversale = new \MappaturaCompetenze\CompetenzaTrasversale($competenza_trasversale_profilo->id_competenza_trasversale);
    $tpl->set_var("id_competenza", "competenza_trasversale_".$competenza_trasversale->id);
    $tpl->set_var("nome_competenza", $competenza_trasversale->nome);
    $tpl->set_var("descrizione_competenza", $competenza_trasversale->descrizione);    
    $tpl->set_var("tipo_competenza", "trasversale");
    
    foreach ($valori as $valore_atteso) {        
        if ($competenza_trasversale_profilo->id_valore_atteso == $valore_atteso->id ) {
            $tpl->set_var("valore_atteso_valore", $valore_atteso->valore);
            $tpl->set_var("valore_atteso_descrizione", $valore_atteso->descrizione);
            break;
        }
    } 
    $tpl->parse("SectValoriAttesiView", true);
        
    foreach ($valori as $valore_mappatura) {
        $tpl->set_var("valore_mappatura_id", $valore_mappatura->id);
        $filters = array(                       
                        "ID_mappatura_periodo" => $mappatura_periodo->id,
                        "ID_tipo_competenza" => 1,
                        "ID_competenza" => $competenza_trasversale_profilo->id_competenza_trasversale,
                        );
        $mappatura_competenza_trasversale = \MappaturaCompetenze\ProfiloMappaturaCompetenzaPeriodo::getByFields($filters);        
        if ($mappatura_competenza_trasversale->id_valore == $valore_mappatura->id ) {
            $tpl->set_var("valore_mappatura_selected", "selected");
        }
        else {
            $tpl->set_var("valore_mappatura_selected", "");
        }
        $tpl->set_var("valore_mappatura_valore", $valore_mappatura->valore);
        $tpl->set_var("valore_mappatura_descrizione", $valore_mappatura->descrizione);
        $tpl->parse("SectOptionsValoriMappatura", true);        
    }        
    
    $tpl->parse("SectValoriMappaturaEdit", true);
    $tpl->parse("SectCompetenza", true);
    $tpl->set_var("SectValoriMappaturaEdit", null);
    $tpl->set_var("SectOptionsValoriMappatura", null);
    $tpl->set_var("SectValoriAttesiView", null);
}
$tpl->set_var("nome_sezione", "Competenze trasversali");
$tpl->parse("SectSezione", true);
$tpl->set_var("SectCompetenza", null);

//competenze specifiche
foreach ($profilo->getCompetenzeSpecificheProfilo() as $competenza_specifica_profilo) {
    $competenza_specifica = new \MappaturaCompetenze\CompetenzaSpecifica($competenza_specifica_profilo->id_competenza_specifica);
    $tpl->set_var("id_competenza", "competenza_specifica_".$competenza_specifica_profilo->id_competenza_specifica);
    $tpl->set_var("nome_competenza", $competenza_specifica->nome);
    $tpl->set_var("descrizione_competenza", $competenza_specifica->descrizione);
    $tpl->set_var("tipo_competenza", "specifica");
    
    foreach ($valori as $valore_atteso) {        
        if ($competenza_specifica_profilo->id_valore_atteso == $valore_atteso->id ) {
            $tpl->set_var("valore_atteso_valore", $valore_atteso->valore);
            $tpl->set_var("valore_atteso_descrizione", $valore_atteso->descrizione);
            break;
        }
    } 
    $tpl->parse("SectValoriAttesiView", true);
    
    foreach ($valori as $valore_mappatura) {
        $tpl->set_var("valore_mappatura_id", $valore_mappatura->id);
        
        $filters = array(                     
                        "ID_mappatura_periodo" => $mappatura_periodo->id,
                        "ID_tipo_competenza" => 2,
                        "ID_competenza" => $competenza_specifica_profilo->id_competenza_specifica,
                        );
        $mappatura_competenza_specifica = \MappaturaCompetenze\ProfiloMappaturaCompetenzaPeriodo::getByFields($filters);        
        if ($mappatura_competenza_specifica->id_valore == $valore_mappatura->id ) {
            $tpl->set_var("valore_mappatura_selected", "selected");
        }
        else {
            $tpl->set_var("valore_mappatura_selected", "");
        }
        $tpl->set_var("valore_mappatura_valore", $valore_mappatura->valore);
        $tpl->set_var("valore_mappatura_descrizione", $valore_mappatura->descrizione);
        $tpl->parse("SectOptionsValoriMappatura", true);        
    }        
    
    $tpl->parse("SectValoriMappaturaEdit", true);
    $tpl->parse("SectCompetenza", true);
    $tpl->set_var("SectValoriMappaturaEdit", null);
    $tpl->set_var("SectOptionsValoriMappatura", null);
    $tpl->set_var("SectValoriAttesiView", null);
}
$tpl->set_var("nome_sezione", "Competenze specifiche");
$tpl->parse("SectSezione", true);
$tpl->set_var("SectCompetenza", null);

$tpl->parse("SectMappaturaActions", true);

$cm->oPage->addContent($tpl);