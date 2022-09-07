<?php
$user = LoggedUser::getInstance();
$cdr = $cm->oPage->globals["cdr"]["value"]->cloneAttributesToNewObject("MappaturaCompetenze\CdrGestione");
//verifica privilegi utente
if (!$user->hasPrivilege("competenze_admin") && !$user->hasPrivilege("competenze_cdr_gestione")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione dell'associazione dei profili per il CdR.");	
}
$modulo = Modulo::getCurrentModule();
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("matrice_associazione_profili_personale.html", "main");

$tpl->set_var("module_theme_path", $modulo->module_theme_full_path);
$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));

//filtro periodi
//se è stato definito almeno un periodo
$periodi_mappatura = MappaturaCompetenze\Periodo::getAll();
if (count($periodi_mappatura) > 0) {
    //generazione del filtro sul periodo
    if (isset($_REQUEST["periodo_select"])) {
        $id_periodo_selezionato = $_REQUEST["periodo_select"];
    } else {
        $id_periodo_selezionato = $periodi_mappatura[0]->id;
    }
    $periodo = false;
    foreach ($periodi_mappatura as $periodo_mappatura) {
        if ($periodo_mappatura->id == $id_periodo_selezionato) {
            $tpl->set_var("periodo_selected", "selected='selected'");
            $periodo = new MappaturaCompetenze\Periodo($id_periodo_selezionato);
        } else {
            $tpl->set_var("periodo_selected", "");
        }
        $tpl->set_var("periodo_id", $periodo_mappatura->id);
        $tpl->set_var("periodo_descrizione", $periodo_mappatura->descrizione);

        $tpl->parse("SectOptionPeriodi", true);
    }
    if ($periodo == false) {
        $periodo = $periodo_mappatura;
    }
    unset($id_periodo_selezionato);
    unset($periodi_mappatura);
    $tpl->set_var("periodo_select", $periodo->id);
    $tpl->parse("SectSelezionePeriodi", true);
}
else {
    $tpl->parse("SectNoPeriodi", true);
}

$profili_assegnazione = $periodo->getProfiliResponsabileCdrPeriodo($user->matricola_utente_selezionato, $cdr);
$personale_afferente_in_data = array();
$date_time_fine_periodo = new DateTime(date($periodo->data_riferimento_fine));
foreach ($cdr->getGerarchiaRamoCdrGestioneData($date_time_fine_periodo) as $cdr_gestione) {
    foreach ($cdr_gestione->getPersonaleCdcAfferentiInData($date_time_fine_periodo) as $personale_afferente) {
        $personale_afferente_in_data[] = $personale_afferente;
    }    
}
//generazione della matrice
if (count($profili_assegnazione) && count($personale_afferente_in_data)>0){   
    foreach ($profili_assegnazione as $profilo) {   
        $tpl->set_var("profilo_descrizione", $profilo->descrizione);
        $tpl->parse("SectProfili", true);
    }
    foreach ($personale_afferente_in_data as $cdc_personale) {
        $personale = Personale::factoryFromMatricola($cdc_personale->matricola_personale);
        $tpl->set_var("personale_nome", $personale->cognome." ".$personale->nome." (matr. ".$personale->matricola.")");                
        $tpl->set_var("id_personale", $personale->id);
        foreach ($profili_assegnazione as $profilo) {            
            $tpl->set_var("id_profilo", $profilo->id);
            if (\MappaturaCompetenze\MappaturaPeriodo::getByFields(array("ID_periodo"=>$periodo->id , "ID_tipo_mappatura"=>1, "ID_profilo"=>$profilo->id, "matricola_valutatore"=>$user->matricola_utente_selezionato, "matricola_personale"=>$personale->matricola))!== null) {
                $tpl->set_var("profilo_personale_checked", "checked");
            }   
            else {
                $tpl->set_var("profilo_personale_checked", "");
            }
            $tpl->parse("SectAssegnazione", true);
        }
        $tpl->parse("SectPersonale", true);
        $tpl->set_var("SectAssegnazione", null);
    } 
    $tpl->parse("SectAssegnazioneActions", true);
}
else {
    $tpl->parse("SectNoMatriceAssegnazione", true);
}
$tpl->parse("SectMatriceAssegnazione", true);

$cm->oPage->addContent($tpl);