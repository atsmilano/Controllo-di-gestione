<?php
$user = LoggedUser::getInstance();

$modulo = Modulo::getCurrentModule();
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("elenco_mappature_periodo.html", "main");

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

$cm->oPage->addContent($tpl);

//grid mappature
$date_time_fine_periodo = new DateTime(date($periodo->data_riferimento_fine));
$grid_mappature = array();
//Mappature dall'alto di competenza
//viene estratto tutto il personale profilato di competenza del responsabile del cdr
$grid_mappatura = array();
foreach (\MappaturaCompetenze\MappaturaPeriodo::getAll(array("ID_periodo"=>$periodo->id , "ID_tipo_mappatura"=>1, "matricola_valutatore"=>$user->matricola_utente_selezionato)) as $mappatura){
    $profilo = new \MappaturaCompetenze\Profilo($mappatura->id_profilo);    
    $personale = \Personale::factoryFromMatricola($mappatura->matricola_personale);    
    $profilo_desc = $profilo->descrizione;
    $cdr = \AnagraficaCdr::factoryFromCodice($profilo->codice_cdr, $date_time_fine_periodo);
    $tipo_cdr = new TipoCdr($cdr->id_tipo_cdr);
    $grid_mappatura[] = array(
                $mappatura->id,
                $personale->cognome . " - " . $personale->nome . " (matr." . $personale->matricola . ")",
                $profilo_desc,
                $cdr->codice . "-" . $tipo_cdr->abbreviazione . " " . $cdr->descrizione,
                $mappatura->datetime_ultimo_salvataggio,
    );
}
if (count($grid_mappatura) > 0) {
    $grid_mappature[] = array("ID_tipo_mappatura"=>1, "title"=>"Mappatura periodica", "grid_mappatura"=>$grid_mappatura);
}

//TODO mappatura periodica con ruolo valutato

//autovalutazione
$grid_mappatura = array();
$mappatura = \MappaturaCompetenze\MappaturaPeriodo::getByFields(array("ID_periodo"=>$periodo->id , "ID_tipo_mappatura"=>2, "matricola_valutatore"=>$user->matricola_utente_selezionato, "matricola_personale"=>$user->matricola_utente_selezionato));
if ($mappatura !== null) {
    $profilo = new \MappaturaCompetenze\Profilo($mappatura->id_profilo);    
    $personale = \Personale::factoryFromMatricola($mappatura->matricola_personale);
    $profilo_desc = $profilo->descrizione;
    $cdr = \AnagraficaCdr::factoryFromCodice($profilo->codice_cdr, $date_time_fine_periodo);
    $tipo_cdr = new TipoCdr($cdr->id_tipo_cdr);
    $grid_mappatura[] = array(
                $mappatura->id,
                $personale->cognome . " - " . $personale->nome . " (matr." . $personale->matricola . ")",
                $profilo_desc,
                $cdr->codice . "-" . $tipo_cdr->abbreviazione . " " . $cdr->descrizione,
                $mappatura->datetime_ultimo_salvataggio,
    );
    $grid_mappature[] = array("ID_tipo_mappatura"=>2, "title"=>"Autovalutazione", "grid_mappatura"=>$grid_mappatura);
}

//mappature dal basso di competenza
$grid_mappatura = array();
foreach (\MappaturaCompetenze\MappaturaPeriodo::getAll(array("ID_periodo"=>$periodo->id , "ID_tipo_mappatura"=>3, "matricola_valutatore"=>$user->matricola_utente_selezionato)) as $mappatura){
    $profilo = new \MappaturaCompetenze\Profilo($mappatura->id_profilo);    
    $personale = \Personale::factoryFromMatricola($mappatura->matricola_personale);
    $profilo_desc = $profilo->descrizione;
    $cdr = \AnagraficaCdr::factoryFromCodice($profilo->codice_cdr, $date_time_fine_periodo);
    $tipo_cdr = new TipoCdr($cdr->id_tipo_cdr);
    $grid_mappatura[] = array(
                $mappatura->id,
                $personale->cognome . " - " . $personale->nome . " (matr." . $personale->matricola . ")",
                $profilo_desc,
                $cdr->codice . "-" . $tipo_cdr->abbreviazione . " " . $cdr->descrizione,
                $mappatura->datetime_ultimo_salvataggio,
    );
}
if (count($grid_mappatura) > 0) {
    $grid_mappature[] = array("ID_tipo_mappatura"=>3, "title"=>"Mappatura dal basso", "grid_mappatura"=>$grid_mappatura);
}

//TODO mappature dal basso con ruolo valutato

//mappature tra pari di competenza
$grid_mappatura = array();
foreach (\MappaturaCompetenze\MappaturaPeriodo::getAll(array("ID_periodo"=>$periodo->id , "ID_tipo_mappatura"=>4, "matricola_valutatore"=>$user->matricola_utente_selezionato)) as $mappatura){
    $profilo = new \MappaturaCompetenze\Profilo($mappatura->id_profilo);    
    $personale = \Personale::factoryFromMatricola($mappatura->matricola_personale);
    $profilo_desc = $profilo->descrizione;
    $cdr = \AnagraficaCdr::factoryFromCodice($profilo->codice_cdr, $date_time_fine_periodo);
    $tipo_cdr = new TipoCdr($cdr->id_tipo_cdr);
    $grid_mappatura[] = array(
                $mappatura->id,
                $personale->cognome . " - " . $personale->nome . " (matr." . $personale->matricola . ")",
                $profilo_desc,
                $cdr->codice . "-" . $tipo_cdr->abbreviazione . " " . $cdr->descrizione,
                $mappatura->datetime_ultimo_salvataggio,
    );
}
if (count($grid_mappatura) > 0) {
    $grid_mappature[] = array("ID_tipo_mappatura"=>4, "title"=>"Mappatura tra pari", "grid_mappatura"=>$grid_mappatura);
}

//TODO mappature tra pari con ruolo valutat

if (count($grid_mappature) > 0) {
    foreach ($grid_mappature as $grid_mappatura) {
        $grid_fields = array(
            "ID_mappatura_periodo",
            "personale",            
            "profilo",
            "cdr",
            "datetime_ultimo_salvataggio",
        );
        $oGrid = ffGrid::factory($cm->oPage);
        $oGrid->id = "mappature-periodiche-".$grid_mappatura["ID_tipo_mappatura"];
        $oGrid->title = $grid_mappatura["title"];
        $oGrid->resources[] = "profilo-personale";
        $oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, 
                                                            $grid_mappatura["grid_mappatura"], 
                                                            "competenze_mappatura");
        $oGrid->order_default = "personale";
        $oGrid->record_id = "profilo-personale-modify";
        $oGrid->order_method = "labels";
        $oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/mappatura_periodica";
        $oGrid->display_navigator = false;
        $oGrid->use_paging = false;
        $oGrid->display_search = false;

        //operazioni di inserimento ed eliminazione non permesse
        $oGrid->display_new = false;
        $oGrid->display_delete_bt = false;

        // *********** FIELDS ****************
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_mappatura_periodo";
        $oField->base_type = "Number";
        $oField->label = "id";
        $oGrid->addKeyField($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "personale";
        $oField->base_type = "Text";
        $oField->label = "Valutato";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "profilo";
        $oField->base_type = "Text";
        $oField->label = "Profilo";
        $oGrid->addContent($oField);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "cdr";
        $oField->base_type = "Text";
        $oField->label = "CdR";
        $oGrid->addContent($oField);
        
        $oField = ffField::factory($cm->oPage);
        $oField->id = "datetime_ultimo_salvataggio";
        $oField->base_type = "DateTime";
        $oField->label = "Ultimo salvataggio";
        $oGrid->addContent($oField);

        // *********** ADDING TO PAGE ****************
        $cm->oPage->addContent($oGrid);     
    }
}
else {
    $tpl->parse("SectNoPersonale", true);
}