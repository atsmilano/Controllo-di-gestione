<?php
use MappaturaCompetenze\Periodo;

$modulo = core\Modulo::getCurrentModule();

if (isset($_REQUEST["periodo_select"])) {
    $periodo = new Periodo($_REQUEST["periodo_select"]);
        
    //viene caricato il template specifico per la pagina
    $tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");    
    $tpl->load_file("elenco_valutati.html", "main");

    $tpl->set_var("module_path", FF_SITE_PATH . "/area_riservata".$modulo->site_path);
    $tpl->set_var("module_img_path", $modulo->module_theme_full_path . "/images");
    $tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
    
    $tpl->set_var("id_periodo_select", $periodo->id);

    //filtro valutati            
    $date_time_fine_periodo = new DateTime(date($periodo->data_riferimento_fine));  
    //viene estratto tutto il personale con una mappatura associata    
    
    //vengono estratti tutti i dipendenti dei cdr di competenza con almeno una mappatura nel periodo
    $valutati = array();
    $user = LoggedUser::getInstance();
    $personale_utente = MappaturaCompetenze\Personale::factoryFromMatricola($user->matricola_utente_selezionato);
        
    //visualizzazione delle valutazioni con ruolo valutatore / admin    
    if ($user->hasPrivilege("competenze_admin")) {
        $mappature = MappaturaCompetenze\MappaturaPeriodo::getAll(array("ID_periodo"=>$periodo->id));       
    }
    else {
        $mappature = $personale_utente->getMappatureRuoloValutatore($periodo);
    }
    foreach ($mappature as $mappatura) {
        $personale_mappatura = MappaturaCompetenze\Personale::factoryFromMatricola($mappatura->matricola_personale);        
        if (!isset($valutati[$personale_mappatura->matricola])){
            $valutati[$personale_mappatura->matricola] = $personale_mappatura;
        }
    }
    uasort($valutati, "PersonaleCmp");
    //visualizzazione delle valutazioni con ruolo valutato se non presenti (autovalutazione valutore = valutato)    
    if (!isset($valutati[$personale_utente->matricola]))  {
        if ($personale_utente->hasMappatureRuoloValutato($periodo)) {
            $valutati[$personale_utente->matricola] = $personale_utente;            
        }
    }       
    //la mappatura con ruolo valutato viene visualizzata per prima
    if (isset($valutati[$personale_utente->matricola])) {
        $temp_array = $valutati[$personale_utente->matricola];
        unset($valutati[$personale_utente->matricola]);
        array_unshift($valutati , $temp_array);
    }
   
    if (count($valutati)){
        $valutato = false;
        
        foreach ($valutati as $personale) {                                 
            $tpl->set_var("valutato_matricola", $personale->matricola);
            $tpl->set_var("valutato_descrizione", $personale->cognome." ".$personale->nome." (matr. ".$personale->matricola.")");
            $tpl->parse("SectOptionValutati", true);            
        }       
        unset($valutati);
        $tpl->set_var("valutato_select", $periodo->id);
        $tpl->parse("SectSelezioneValutati", true); 
    }
    else {
        $tpl->parse("SectNoValutati", true);
    }           
}
else {
    throw new Exception("Errore nella selezione del periodo");
}
die($tpl->rpparse("main", true));

//metodo per l'ordinamento del personale
function PersonaleCmp($a, $b) {
    if ($a->cognome == $b->cognome) {
        return strcmp($a->nome, $b->nome);
    }
    return strcmp($a->cognome, $b->cognome);
}