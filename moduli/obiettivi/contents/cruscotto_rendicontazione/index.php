<?php
$cm->oPage->tplAddJs("jquery.min.js", "jquery.min.js", FF_THEME_DIR . "/library/jqplot/1.0.9");
$cm->oPage->tplAddJs("jquery.jqplot.min.js", "jquery.jqplot.min.js", FF_THEME_DIR . "/library/jqplot/1.0.9");

$cm->oPage->tplAddJs("jqplot.meterGaugeRenderer.js", "jqplot.meterGaugeRenderer.js", FF_THEME_DIR . "/library/jqplot/1.0.9/plugins");
$cm->oPage->tplAddJs("jqplot.barRenderer.js", "jqplot.barRenderer.js", FF_THEME_DIR . "/library/jqplot/1.0.9/plugins");

$cm->oPage->tplAddJs("jqplot.canvasTextRenderer.js", "jqplot.canvasTextRenderer.js", FF_THEME_DIR . "/library/jqplot/1.0.9/plugins");
$cm->oPage->tplAddJs("jqplot.categoryAxisRenderer.js", "jqplot.categoryAxisRenderer.js", FF_THEME_DIR . "/library/jqplot/1.0.9/plugins");
$cm->oPage->tplAddJs("jqplot.dateAxisRenderer.js", "jqplot.dateAxisRenderer.js", FF_THEME_DIR . "/library/jqplot/1.0.9/plugins");
$cm->oPage->tplAddJs("jqplot.pointLabels.js", "jqplot.pointLabels.js", FF_THEME_DIR . "/library/jqplot/1.0.9/plugins");
$cm->oPage->tplAddJs("jquery-scrollto.js", "jquery-scrollto.js", FF_THEME_DIR . "/library/scrollto");

$cm->oPage->tplAddCss("jquery.jqplot.css", array("file" => "jquery.jqplot.css", "path" => FF_THEME_DIR . "/library/jqplot/1.0.9"));
$cm->oPage->tplAddCss("jquery.jqplot.min.css", array("file" => "jquery.jqplot.min.css", "path" => FF_THEME_DIR . "/library/jqplot/1.0.9"));

CoreHelper::includeJqueryUi();

$modulo = core\Modulo::getCurrentModule();

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("cruscotto_rendicontazione.html", "main");

/* ---------------------------------------------------------------------------- */
//estrazione dei periodi per l'anno
$anno = $cm->oPage->globals["anno"]["value"];
$periodi_rendicontazione = ObiettiviPeriodoRendicontazione::getAll(array("ID_anno_budget" => $anno->id), array(array("fieldname"=>"ordinamento_anno", "direction"=>"DESC")));

$date = $cm->oPage->globals["data_riferimento"]["value"];
$anagrafica_cdr = AnagraficaCdrObiettivi::factoryFromCodice($cm->oPage->globals["cdr"]["value"]->codice, $date);

$active_tab = 0;
$show_dettaglio_obiettivi_cdr = false;
if ($anagrafica_cdr !== null) {    
    $show_dettaglio_obiettivi_cdr = true;
    if (!$anagrafica_cdr->hasObiettiviAziendali($anno)){
        $active_tab = 2;
    }    
}
$tpl->set_var("active_tab", $active_tab);    

$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));

//se è stato definito almeno un periodo per l'anno
if (count($periodi_rendicontazione) > 0) {
    //generazione del filtro sul periodo
    if (isset($_REQUEST["periodo_select"])) {
        $periodo_selezionato = $_REQUEST["periodo_select"];
    } else {
        $periodo_selezionato = $periodi_rendicontazione[0]->id;
    }
    $periodo = false;
    for ($i = 0; $i < count($periodi_rendicontazione); $i++) {
        if ($periodi_rendicontazione[$i]->id == $periodo_selezionato) {
            $tpl->set_var("periodo_selected", "selected='selected'");
            $periodo = $periodo_selezionato;
        } else
            $tpl->set_var("periodo_selected", "");
        $tpl->set_var("periodo_id", $periodi_rendicontazione[$i]->id);
        $tpl->set_var("periodo_descrizione", $periodi_rendicontazione[$i]->descrizione);

        $tpl->parse("SectOptionPeriodi", true);
    }
    if ($periodo == false) {
        $periodo = $periodi_rendicontazione[0]->id;
    }
    unset($periodo_selezionato);
    unset($periodi_rendicontazione);
    $tpl->set_var("periodo_select", $periodo);
    $tpl->parse("SectSelezionePeriodi", true);
    if ($show_dettaglio_obiettivi_cdr == true) {
        $tpl->parse("SectObiettiviCdrTab", true);
    }
}
else {
    $tpl->parse("SectNoPeriodi", true);
}
/* ---------------------------------------------------------------------------- */

//***********************
//Adding contents to page
$cm->oPage->addContent($tpl->rpparse("main", true));