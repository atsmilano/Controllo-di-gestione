<?php
$cm->oPage->tplAddJs("jquery.jqplot.js", "jquery.jqplot.js", FF_THEME_DIR . "/library/jqplot/1.0.9");

$cm->oPage->tplAddJs("Chart.min.js", "Chart.min.js", FF_THEME_DIR . "/library/chartjs");
$cm->oPage->tplAddJs("Chart.bundle.min.js", "Chart.bundle.min.js", FF_THEME_DIR . "/library/chartjs");
$cm->oPage->tplAddJs("chartjs-plugin-piechart-outlabels.js", "chartjs-plugin-piechart-outlabels.js", FF_THEME_DIR . "/library/chartjs");

$cm->oPage->tplAddJs("jqplot.meterGaugeRenderer.js", "jqplot.meterGaugeRenderer.js", FF_THEME_DIR . "/library/jqplot/1.0.9/plugins");
$cm->oPage->tplAddJs("jqplot.barRenderer.js", "jqplot.barRenderer.js", FF_THEME_DIR . "/library/jqplot/1.0.9/plugins");

$cm->oPage->tplAddJs("jqplot.canvasTextRenderer.js", "jqplot.canvasTextRenderer.js", FF_THEME_DIR . "/library/jqplot/1.0.9/plugins");
$cm->oPage->tplAddJs("jqplot.categoryAxisRenderer.js", "jqplot.categoryAxisRenderer.js", FF_THEME_DIR . "/library/jqplot/1.0.9/plugins");
$cm->oPage->tplAddJs("jqplot.dateAxisRenderer.js", "jqplot.dateAxisRenderer.js", FF_THEME_DIR . "/library/jqplot/1.0.9/plugins");
$cm->oPage->tplAddJs("jqplot.pointLabels.js", "jqplot.pointLabels.js", FF_THEME_DIR . "/library/jqplot/1.0.9/plugins");

$cm->oPage->tplAddCss("jquery.jqplot.css", array("file" => "jquery.jqplot.css", "path" => FF_THEME_DIR . "/library/jqplot/1.0.9"));
$cm->oPage->tplAddCss("jquery.jqplot.min.css", array("file" => "jquery.jqplot.min.css", "path" => FF_THEME_DIR . "/library/jqplot/1.0.9"));

$cm->oPage->tplAddCss("Chart.min.css", array("file" => "Chart.min.css", "path" => FF_THEME_DIR . "/library/chartjs"));

CoreHelper::includeJqueryUi();
$modulo = core\Modulo::getCurrentModule();

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("cruscotto_valutazione.html", "main");

/* ---------------------------------------------------------------------------- */
//estrazione dei periodi per l'anno
$anno = $cm->oPage->globals["anno"]["value"];
$periodi_valutazione = ValutazioniPeriodo::getAll(array("ID_anno_budget" => $anno->id));
//se è stato definito almeno un periodo per l'anno
if (count($periodi_valutazione) > 0) {
    //generazione del filtro sul periodo
    if (isset($_REQUEST["periodo_select"])) {
        $periodo_selezionato = $_REQUEST["periodo_select"];
    } else {
        $periodo_selezionato = $periodi_valutazione[0]->id;
    }
    $periodo = false;
    for ($i = 0; $i < count($periodi_valutazione); $i++) {
        if ($periodi_valutazione[$i]->id == $periodo_selezionato) {
            $tpl->set_var("periodo_selected", "selected='selected'");
            $periodo = $periodo_selezionato;
        } else
            $tpl->set_var("periodo_selected", "");
        $tpl->set_var("periodo_id", $periodi_valutazione[$i]->id);
        $tpl->set_var("periodo_descrizione", $periodi_valutazione[$i]->descrizione);

        $tpl->parse("SectOptionPeriodi", true);
    }
    if ($periodo == false) {
        $periodo = $periodi_valutazione[0]->id;
    }
    unset($periodo_selezionato);
    unset($periodi_valutazione);
    $tpl->set_var("periodo_select", $periodo);
    $tpl->parse("SectSelezionePeriodi", true);
}
else {
    $tpl->parse("SectNoPeriodi", true);
}
/* ---------------------------------------------------------------------------- */

$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));

//***********************
//Adding contents to page
$cm->oPage->addContent($tpl->rpparse("main", true));