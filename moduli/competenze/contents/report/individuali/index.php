<?php
use MappaturaCompetenze\Periodo;

$modulo = core\Modulo::getCurrentModule();

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("report_individuali.html", "main");

$tpl->set_var("module_img_path", $modulo->module_theme_full_path . "/images");
$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));

//filtro periodi
//se è stato definito almeno un periodo
$periodi_mappatura = Periodo::getAll();
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
            $periodo = new Periodo($id_periodo_selezionato);
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