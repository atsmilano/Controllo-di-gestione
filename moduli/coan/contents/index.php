<?php

//librerie jquery per tooltip
CoreHelper::includeJqueryUi();
$modulo = core\Modulo::getCurrentModule();

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("coan.html", "main");

$tpl->set_var("module_img_path", $modulo->module_theme_full_path . "/images");
$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
$tpl->set_var("ret_url", urlencode($_SERVER["REQUEST_URI"]));

//estrazione dei periodi
$order = array(
    array("fieldname"=>"data_inizio", "direction"=>"DESC"),
    array("fieldname"=>"data_fine", "direction"=>"DESC"),
);
$periodi_anno = CoanPeriodo::getAll(array(), $order);
if (count($periodi_anno) > 0) {
    $db = ffDB_Sql::factory();
    //generazione del filtro sul periodo
    if (isset($_REQUEST["periodo_select"])) {
        $periodo_selezionato = $_REQUEST["periodo_select"];
    } else {
        $periodo_selezionato = $periodi_anno[0]->id;
    }
    $periodo = false;
    for ($i = 0; $i < count($periodi_anno); $i++) {
        $anno_coan = new ValutazioniAnnoBudget($periodi_anno[$i]->id_anno_budget);
        if ($periodi_anno[$i]->id == $periodo_selezionato) {
            $tpl->set_var("periodo_selected", "selected='selected'");
            $periodo = $periodi_anno[$i];
        } else {
            $tpl->set_var("periodo_selected", "");
        }
        $tpl->set_var("periodo_id", $periodi_anno[$i]->id);
        $tpl->set_var("periodo_anno_budget", $periodi_anno[$i]->id_anno_budget);
        $tpl->set_var("periodo_descrizione", $anno_coan->descrizione . " - " . $periodi_anno[$i]->descrizione);
        $tpl->set_var("periodo_data_inizio", $periodi_anno[$i]->data_inizio);
        $tpl->set_var("periodo_data_fine", $periodi_anno[$i]->data_fine);

        $tpl->parse("SectOptionPeriodo", true);
    }
    if ($periodo == false) {
        $periodo = $periodi_anno[0];
    }

    $anno_budget = new AnnoBudget($periodo->id_anno_budget);

    $tpl->set_var("periodo_selezionato", $periodo->id);

    //predisposizione filtro query
    $query_filter = "1=1";

    //generazione dei filtri su cdr che hanno conti per nell'anno        
    //$cdr_associati_anno = CoanCdc::getCdrAssociatiAnno($anno); 
       
    //considerando l'anno corrente viene recuperata la data di riferimento dai globals     
    $piano_cdr = PianoCdr::getAttivoInData(TipoPianoCdr::getPrioritaMassima(), $periodo->data_fine);    
    $cdr_radice_piano = $piano_cdr->getCdrRadice();
    $cdr_anno = $cdr_radice_piano->getGerarchia();

    if (count($cdr_anno) > 0) {
        //generazione del filtro sul cdr
        $cdr = 0;
        if (isset($_REQUEST["cdr_coan_select"])) {
            $cdr_selezionato = $_REQUEST["cdr_coan_select"];
        } else {
            $cdr_selezionato = 0;
        }
        foreach ($cdr_anno as $cdr_associato) {
            if ($cdr_associato["cdr"]->id == $cdr_selezionato) {
                $tpl->set_var("cdr_coan_selected", "selected='selected'");
                $cdr = $cdr_associato["cdr"];
            } else {
                $tpl->set_var("cdr_coan_selected", "");
            }
            $tpl->set_var("cdr_coan_id", $cdr_associato["cdr"]->id);
            $indent = "";
            for ($i = 0; $i < $cdr_associato["livello"]; $i++) {
                $indent .= "----";
            }
            $tpl->set_var("cdr_coan_indent", $indent);
            $tipo_cdr = new TipoCdr($cdr_associato["cdr"]->id_tipo_cdr);
            $tpl->set_var("cdr_coan_descrizione", $cdr_associato["cdr"]->codice . " - " . $tipo_cdr->abbreviazione . " " . $cdr_associato["cdr"]->descrizione);
            $tpl->parse("SectOptionCdrCoan", true);
        }
    } else {
        ffErrorHandler::raise("Nessun CDR disponibile");
    }
    if (isset($_REQUEST["cdr_ramo_select"]) && $_REQUEST["cdr_ramo_select"] == "true") {
        $cdr_ramo_select = true;
        $tpl->set_var("filter_cdr_ramo_checked", "checked");
    } else {
        $cdr_ramo_select = false;
    }
    //costruzione della condizione per la query
    if ($cdr !== 0) {
        $query_filter .= " AND (coan_cdc.codice_cdr = " . $db->toSql($cdr->codice);
        //vengono estratti tutti i cdc degli eventuali figli del cdr nel caso sia selezionata l'opzione
        if ($cdr_ramo_select == false) {
            foreach ($cdr->getGerarchia() as $cdr_figlio) {
                $query_filter .= " OR coan_cdc.codice_cdr = " . $db->toSql($cdr_figlio["cdr"]->codice);
            }
        }
        $query_filter .= ")";
    }

    //generazione dei filtri sui cdc standard regionali
    $cdc_standard_regionali = CoanCdcStandardRegionale::getAttiviAnno($anno_budget);
    if (count($cdc_standard_regionali) > 0) {
        //generazione del filtro sul cdc standard regionale
        $cdc_standard_regionale = 0;
        if (isset($_REQUEST["cdc_standard_regionale_select"])) {
            $cdc_standard_selezionato = $_REQUEST["cdc_standard_regionale_select"];
        } else {
            $cdc_standard_selezionato = 0;
        }
        foreach ($cdc_standard_regionali as $cdc_standard) {
            if ($cdc_standard->id == $cdc_standard_selezionato) {
                $tpl->set_var("cdc_standard_regionale_selected", "selected='selected'");
                $cdc_standard_regionale = $cdc_standard;
            } else {
                $tpl->set_var("cdc_standard_regionale_selected", "");
            }
            $tpl->set_var("cdc_standard_regionale_id", $cdc_standard->id);
            $tpl->set_var("cdc_standard_regionale_descrizione", $cdc_standard->descrizione);
            $tpl->set_var("cdc_standard_regionale_codice", $cdc_standard->codice);
            $tpl->parse("SectOptionCdcStandardRegionale", true);
        }
    } else {
        ffErrorHandler::raise("Nessun CDC standard regionale disponibile");
    }
    //costruzione della condizione per la query
    if ($cdc_standard_regionale !== 0) {
        $query_filter .= " AND coan_cdc.ID_cdc_standard_regionale = " . $db->toSql($cdc_standard_regionale->id);
    }

    //generazione dei filtri sui distretti     
    $distretti = CoanDistretto::getAttiviAnno($anno_budget);
    if (count($distretti) > 0) {
        //generazione del filtro su distretto
        $distretto = 0;
        if (isset($_REQUEST["distretto_select"])) {
            $distretto_selezionato = $_REQUEST["distretto_select"];
        } else {
            $distretto_selezionato = 0;
        }
        foreach ($distretti as $distr) {
            if ($distr->id == $distretto_selezionato) {
                $tpl->set_var("distretto_selected", "selected='selected'");
                $distretto = $distr;
            } else {
                $tpl->set_var("distretto_selected", "");
            }
            $tpl->set_var("distretto_id", $distr->id);
            $tpl->set_var("distretto_descrizione", $distr->descrizione);
            $tpl->parse("SectOptionDistretto", true);
        }
    } else {
        ffErrorHandler::raise("Nessun distretto disponibile");
    }
    //costruzione della condizione per la query
    if ($distretto !== 0) {
        $query_filter .= " AND coan_cdc.ID_distretto = " . $db->toSql($distretto->id);
    }
    
    //preload di tutti i dati
    //selezione di tutti i fp di livello uno    
    $sql = "
        SELECT 
            coan_fp_primo.ID AS fp_primo_ID,
            coan_fp_primo.descrizione AS fp_primo_descrizione,
            coan_fp_primo.codice AS fp_primo_codice,

            coan_fp_secondo.ID AS fp_secondo_ID,
            coan_fp_secondo.descrizione AS fp_secondo_descrizione,
            coan_fp_secondo.codice AS fp_secondo_codice,

            coan_fp_terzo.ID AS fp_terzo_ID,
            coan_fp_terzo.descrizione AS fp_terzo_descrizione,
            coan_fp_terzo.codice AS fp_terzo_codice,

            coan_fp_quarto.ID AS fp_quarto_ID,
            coan_fp_quarto.descrizione AS fp_quarto_descrizione,
            coan_fp_quarto.codice AS fp_quarto_codice,

            coan_conto.ID AS conto_ID,
            coan_conto.descrizione AS conto_descrizione,
            coan_conto.codice AS conto_codice,

            SUM(coan_consuntivo_periodo.budget) AS budget,
            SUM(coan_consuntivo_periodo.consuntivo) AS consuntivo
        FROM coan_fp_primo
            INNER JOIN coan_fp_secondo ON coan_fp_primo.ID = coan_fp_secondo.ID_fp_primo
            INNER JOIN coan_fp_terzo ON coan_fp_secondo.ID = coan_fp_terzo.ID_fp_secondo
            INNER JOIN coan_fp_quarto ON coan_fp_terzo.ID = coan_fp_quarto.ID_fp_terzo
            INNER JOIN coan_conto ON coan_fp_quarto.ID = coan_conto.ID_fp_quarto			
            INNER JOIN coan_consuntivo_periodo ON coan_conto.ID = coan_consuntivo_periodo.ID_conto
            INNER JOIN (
                SELECT coan_cdc.* 
                FROM coan_cdc 
                WHERE " . $query_filter . "
            ) AS coan_cdc ON coan_consuntivo_periodo.ID_cdc_coan = coan_cdc.ID
        WHERE coan_consuntivo_periodo.ID_periodo_coan = " . $db->toSql($periodo->id) . "			
        GROUP BY coan_conto.ID
        ORDER BY
            coan_fp_primo.ID,
            coan_fp_secondo.ID,
            coan_fp_terzo.ID,
            coan_fp_quarto.ID,
            coan_conto.codice ASC
    ";

    $db->query($sql);
    if ($db->nextRecord()) {
        //inizializzazione degli array
        $fp1 = array();
        $fp1_prec = false;
        $fp2 = array();
        $fp2_prec = false;
        $fp3 = array();
        $fp3_prec = false;
        $fp4 = array();
        $fp4_prec = false;
        $conto = array();
        do {
            $budget = $db->getField("budget", "Text", true);
            $consuntivo = $db->getField("consuntivo", "Text", true);
            //aggiunta di fp primo livello nel caso non sia già presente
            if ($db->getField("fp_primo_ID", "Number", true) !== $fp1_prec) {
                $fp1_prec = $db->getField("fp_primo_ID", "Number", true);
                $fp1[] = array(
                    "ID" => $db->getField("fp_primo_ID", "Number", true),
                    "descrizione" => $db->getField("fp_primo_descrizione", "Text", true),
                    "codice" => $db->getField("fp_primo_codice", "Text", true),
                    "totale_budget" => $budget,
                    "totale_consuntivo" => $consuntivo
                );
            } else {
                $fp1[count($fp1) - 1]["totale_budget"] += $budget;
                $fp1[count($fp1) - 1]["totale_consuntivo"] += $consuntivo;
            }

            //aggiunta di fp secondo livello nel caso non sia già presente
            if ($db->getField("fp_secondo_ID", "Number", true) !== $fp2_prec) {
                $fp2_prec = $db->getField("fp_secondo_ID", "Number", true);
                $fp2[] = array(
                    "ID" => $db->getField("fp_secondo_ID", "Number", true),
                    "descrizione" => $db->getField("fp_secondo_descrizione", "Text", true),
                    "codice" => $db->getField("fp_secondo_codice", "Text", true),
                    "totale_budget" => $budget,
                    "totale_consuntivo" => $consuntivo,
                    "ID_fp_primo" => $db->getField("fp_primo_ID", "Number", true)
                );
            } else {
                $fp2[count($fp2) - 1]["totale_budget"] += $budget;
                $fp2[count($fp2) - 1]["totale_consuntivo"] += $consuntivo;
            }

            //aggiunta di fp terzo livello nel caso non sia già presente
            if ($db->getField("fp_terzo_ID", "Number", true) !== $fp3_prec) {
                $fp3_prec = $db->getField("fp_terzo_ID", "Number", true);
                $fp3[] = array(
                    "ID" => $db->getField("fp_terzo_ID", "Number", true),
                    "descrizione" => $db->getField("fp_terzo_descrizione", "Text", true),
                    "codice" => $db->getField("fp_terzo_codice", "Text", true),
                    "totale_budget" => $budget,
                    "totale_consuntivo" => $consuntivo,
                    "ID_fp_secondo" => $db->getField("fp_secondo_ID", "Number", true)
                );
            } else {
                $fp3[count($fp3) - 1]["totale_budget"] += $budget;
                $fp3[count($fp3) - 1]["totale_consuntivo"] += $consuntivo;
            }

            //aggiunta di fp quarto livello nel caso non sia già presente
            if ($db->getField("fp_quarto_ID", "Number", true) !== $fp4_prec) {
                $fp4_prec = $db->getField("fp_quarto_ID", "Number", true);
                $fp4[] = array(
                    "ID" => $db->getField("fp_quarto_ID", "Number", true),
                    "descrizione" => $db->getField("fp_quarto_descrizione", "Text", true),
                    "codice" => $db->getField("fp_quarto_codice", "Text", true),
                    "totale_budget" => $budget,
                    "totale_consuntivo" => $consuntivo,
                    "ID_fp_terzo" => $db->getField("fp_terzo_ID", "Number", true)
                );
            } else {
                $fp4[count($fp4) - 1]["totale_budget"] += $budget;
                $fp4[count($fp4) - 1]["totale_consuntivo"] += $consuntivo;
            }

            //aggiunta dei conti		
            $conti[] = array(
                "ID" => $db->getField("conto_ID", "Number", true),
                "descrizione" => $db->getField("conto_descrizione", "Text", true),
                "codice" => $db->getField("conto_codice", "Text", true),
                "ID_fp_quarto" => $db->getField("fp_quarto_ID", "Number", true),
                "budget" => $budget,
                "consuntivo" => $consuntivo
            );
        } while ($db->nextRecord());

        //visualizzazione dei dati
        //fp primo livello
        for ($i = 0; $i < count($fp1); $i++) {
            $tpl->set_var("id_fp1", $fp1[$i]["ID"]);
            $tpl->set_var("cod_fp1", $fp1[$i]["codice"]);
            $tpl->set_var("desc_fp1", $fp1[$i]["descrizione"]);
            $tpl->set_var("totale_budget_fp1", number_format($fp1[$i]["totale_budget"], 0, ",", "."));
            $tpl->set_var("totale_consuntivo_fp1", number_format($fp1[$i]["totale_consuntivo"], 0, ",", "."));
            if ($fp1[$i]["totale_budget"] == 0) {
                $tpl->set_var("erosione_fp1", "ND");
            } else {
                $tpl->set_var("erosione_fp1", number_format($fp1[$i]["totale_consuntivo"] / $fp1[$i]["totale_budget"] * 100, 0, ",", ".") . "%");
            }
            //fp secondo livello
            for ($j = 0; $j < count($fp2); $j++) {
                if ($fp1[$i]["ID"] == $fp2[$j]["ID_fp_primo"]) {
                    $tpl->set_var("id_fp2", $fp2[$j]["ID"]);
                    $tpl->set_var("cod_fp2", $fp2[$j]["codice"]);
                    $tpl->set_var("desc_fp2", $fp2[$j]["descrizione"]);
                    $tpl->set_var("totale_budget_fp2", number_format($fp2[$j]["totale_budget"], 0, ",", "."));
                    $tpl->set_var("totale_consuntivo_fp2", number_format($fp2[$j]["totale_consuntivo"], 0, ",", "."));
                    if ($fp2[$j]["totale_budget"] == 0) {
                        $tpl->set_var("erosione_fp2", "ND");
                    } else {
                        $tpl->set_var("erosione_fp2", number_format($fp2[$j]["totale_consuntivo"] / $fp2[$j]["totale_budget"] * 100, 0, ",", ".") . "%");
                    }
                    //fp terzo livello
                    for ($k = 0; $k < count($fp3); $k++) {
                        if ($fp2[$j]["ID"] == $fp3[$k]["ID_fp_secondo"]) {
                            $tpl->set_var("id_fp3", $fp3[$k]["ID"]);
                            $tpl->set_var("cod_fp3", $fp3[$k]["codice"]);
                            $tpl->set_var("desc_fp3", $fp3[$k]["descrizione"]);
                            $tpl->set_var("totale_budget_fp3", number_format($fp3[$k]["totale_budget"], 0, ",", "."));
                            $tpl->set_var("totale_consuntivo_fp3", number_format($fp3[$k]["totale_consuntivo"], 0, ",", "."));
                            if ($fp3[$k]["totale_budget"] == 0)
                                $tpl->set_var("erosione_fp3", "ND");
                            else
                                $tpl->set_var("erosione_fp3", number_format($fp3[$k]["totale_consuntivo"] / $fp3[$k]["totale_budget"] * 100, 0, ",", ".") . "%");
                            //fp quarto livello
                            for ($z = 0; $z < count($fp4); $z++) {
                                if ($fp3[$k]["ID"] == $fp4[$z]["ID_fp_terzo"]) {
                                    $tpl->set_var("id_fp4", $fp4[$z]["ID"]);
                                    $tpl->set_var("cod_fp4", $fp4[$z]["codice"]);
                                    $tpl->set_var("desc_fp4", $fp4[$z]["descrizione"]);
                                    $tpl->set_var("totale_budget_fp4", number_format($fp4[$z]["totale_budget"], 0, ",", "."));
                                    $tpl->set_var("totale_consuntivo_fp4", number_format($fp4[$z]["totale_consuntivo"], 0, ",", "."));
                                    if ($fp4[$z]["totale_budget"] == 0)
                                        $tpl->set_var("erosione_fp4", "ND");
                                    else
                                        $tpl->set_var("erosione_fp4", number_format($fp4[$z]["totale_consuntivo"] / $fp4[$z]["totale_budget"] * 100, 0, ",", ".") . "%");
                                    //conti
                                    for ($c = 0; $c < count($conti); $c++) {
                                        if ($fp4[$z]["ID"] == $conti[$c]["ID_fp_quarto"]) {
                                            $tpl->set_var("id_conto", $conti[$c]["ID"]);
                                            $tpl->set_var("cod_conto", $conti[$c]["codice"]);
                                            $tpl->set_var("desc_conto", $conti[$c]["descrizione"]);
                                            $tpl->set_var("budget", number_format($conti[$c]["budget"], 0, ",", "."));
                                            $tpl->set_var("consuntivo", number_format($conti[$c]["consuntivo"], 0, ",", "."));
                                            if ($conti[$c]["budget"] == 0)
                                                $tpl->set_var("erosione", "ND");
                                            else
                                                $tpl->set_var("erosione", number_format($conti[$c]["consuntivo"] / $conti[$c]["budget"] * 100, 0, ",", ".") . "%");


                                            $tpl->parse("SectConto", true);
                                        }
                                    }
                                    $tpl->parse("SectFp4", true);
                                    $tpl->set_var("SectConto", "");
                                }
                            }
                            $tpl->parse("SectFp3", true);
                            $tpl->set_var("SectFp4", "");
                        }
                    }
                    $tpl->parse("SectFp2", true);
                    $tpl->set_var("SectFp3", "");
                }
            }
            $tpl->parse("SectFp1", true);
            $tpl->set_var("SectFp2", "");
        }
    }
    else {
        $tpl->parse("SectNoConti", false);
    }
    $tpl->parse("ReportCoan", false);
} else {
    $tpl->parse("NoPeriodi", false);
}

$cm->oPage->addContent($tpl);