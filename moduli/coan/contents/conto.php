<?php

$modulo = Modulo::getCurrentModule();

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("conto.html", "main");

if (isset($_GET["ID_conto"]) && isset($_GET["ID_periodo"])) {
    $dateTimeObject = $cm->oPage->globals["data_riferimento"]["value"];
    $date = $dateTimeObject->format("Y-m-d");

    $id_conto = $_GET["ID_conto"];

    $db = ffDB_Sql::factory();
    $sql = "
            SELECT
                coan_conto.codice AS conto_codice,
                coan_conto.descrizione AS conto_desc,
                coan_cdc.codice AS cdc_codice,
                coan_cdc.descrizione AS cdc_desc,
                coan_cdc.codice_cdr AS cdr_codice,
                SUM(coan_consuntivo_periodo.budget) AS coan_tot_budget_cdc,
                SUM(coan_consuntivo_periodo.consuntivo) AS coan_tot_consuntivo_cdc,
                (
                SELECT
                    SUM( coan_consuntivo_periodo.budget ) 
                FROM
                    coan_consuntivo_periodo
                    INNER JOIN coan_cdc AS coan_cdc_join_1 ON coan_consuntivo_periodo.ID_cdc_coan = coan_cdc_join_1.ID
                    INNER JOIN coan_conto ON coan_consuntivo_periodo.ID_conto = coan_conto.ID 
                WHERE
                    coan_consuntivo_periodo.ID_periodo_coan = " . $db->toSql($_GET["ID_periodo"]) . " 
                    AND coan_conto.ID = " . $id_conto . " 
                    AND coan_cdc_join_1.codice_cdr = coan_cdc.codice_cdr
                ) AS coan_tot_budget_cdr,
                (
                SELECT
                    SUM( coan_consuntivo_periodo.consuntivo ) 
                FROM
                    coan_consuntivo_periodo
                    INNER JOIN coan_cdc AS coan_cdc_join_2 ON coan_consuntivo_periodo.ID_cdc_coan = coan_cdc_join_2.ID
                    INNER JOIN coan_conto ON coan_consuntivo_periodo.ID_conto = coan_conto.ID 
                WHERE
                    coan_consuntivo_periodo.ID_periodo_coan = " . $db->toSql($_GET["ID_periodo"]) . " 
                    AND coan_conto.ID = " . $id_conto . " 
                    AND coan_cdc_join_2.codice_cdr = coan_cdc.codice_cdr
                ) AS coan_tot_consuntivo_cdr 
            FROM
                coan_conto
                INNER JOIN coan_consuntivo_periodo ON coan_conto.ID = coan_consuntivo_periodo.ID_conto
                INNER JOIN coan_cdc ON coan_consuntivo_periodo.ID_cdc_coan = coan_cdc.ID 
            WHERE
                coan_conto.ID = " . $id_conto . " 
                AND coan_consuntivo_periodo.ID_periodo_coan = " . $db->toSql($_GET["ID_periodo"]) . "
            GROUP BY
                cdc_codice
            ORDER BY
                coan_tot_consuntivo_cdr DESC,
                coan_tot_budget_cdc DESC
			";
    $db->query($sql);
    if ($db->nextRecord()) {
        $totale_budget = 0;
        $totale_consuntivo = 0;
        $cdr_totale = 0;
        do {
            $budget = (float) $db->getField("coan_tot_budget_cdc", "Text", true);
            $consuntivo = (float) $db->getField("coan_tot_consuntivo_cdc", "Text", true);
            $totale_budget += $budget;
            $totale_consuntivo += $consuntivo;

            $tpl->set_var("conto_codice", $db->getField("conto_codice", "Text", true));
            $tpl->set_var("conto_desc", $db->getField("conto_desc", "Text", true));
            $tpl->set_var("cdc_codice", $db->getField("cdc_codice", "Text", true));
            $tpl->set_var("cdc_desc", $db->getField("cdc_desc", "Text", true));
            //cdr            
            $cdr = AnagraficaCdr::factoryFromCodice($db->getField("cdr_codice", "Text", true), $dateTimeObject);
            $tipo_cdr = new TipoCdr($cdr->id_tipo_cdr);
            $tpl->set_var("cdr", $cdr->codice . " - " . $tipo_cdr->abbreviazione . " " . $cdr->descrizione);

            $tpl->set_var("budget", number_format($budget, 0, ",", "."));
            $tpl->set_var("consuntivo", number_format($consuntivo, 0, ",", "."));
            if ($budget == 0) {
                $tpl->set_var("erosione", "ND");
            }
            else {
                $tpl->set_var("erosione", number_format($consuntivo / $budget * 100, 0, ",", ".") . "%");
            }

            //se viene cambiata l'area di coordinamento viene visualizzato il totale
            if ($cdr_totale !== $cdr->codice) {
                $totale_budget_cdr = $db->getField("coan_tot_budget_cdr", "Text", true);
                $totale_consuntivo_cdr = $db->getField("coan_tot_consuntivo_cdr", "Text", true);

                $tpl->set_var("budget_cdr", number_format($totale_budget_cdr, 0, ",", "."));
                $tpl->set_var("consuntivo_cdr", number_format($totale_consuntivo_cdr, 0, ",", "."));
                if ($totale_budget_cdr == 0)
                    $tpl->set_var("erosione_cdr", "ND");
                else
                    $tpl->set_var("erosione_cdr", number_format($totale_consuntivo_cdr / $totale_budget_cdr * 100, 0, ",", ".") . "%");

                $tpl->parse("SectCdr", false);
                $cdr_totale = $cdr->codice;
            }
            else {
                $tpl->set_var("SectCdr", "");
            }
            $tpl->parse("SectCdc", true);
        } while ($db->nextRecord());
        $tpl->set_var("tot_budget", number_format($totale_budget, 0, ",", "."));
        $tpl->set_var("tot_consuntivo", number_format($totale_consuntivo, 0, ",", "."));
        if ($totale_budget == 0) {
            $tpl->set_var("tot_erosione", "ND");
        }
        else {
            $tpl->set_var("tot_erosione", number_format($totale_consuntivo / $totale_budget * 100, 0, ",", ".") . "%");
        }
    }
    else
        ffErrorHandler::raise("Errore nel passaggio dei parametri");
}
else
    ffErrorHandler::raise("Errore nel passaggio dei parametri");

die($tpl->rpparse("main", true));
