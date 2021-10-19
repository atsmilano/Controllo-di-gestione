<?php
$user = LoggedUser::getInstance();

//recupero dei parametri
//anno***********
$anno = $cm->oPage->globals["anno"]["value"];

//viene caricato il template specifico per la pagina
$modulo = Modulo::getCurrentModule();

$tpl = ffTemplate::factory($modulo->module_theme_dir . DIRECTORY_SEPARATOR . "tpl");
$tpl->load_file("matrice_pesi.html", "main");

$tpl->set_var("module_theme_path", $modulo->module_theme_dir . DIRECTORY_SEPARATOR);
$tpl->set_var("globals", $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST));
$valutazioneAnnoBudget = new ValutazioniAnnoBudget($anno->id);

$categorie = $valutazioneAnnoBudget->getCategorieAnno();

$riga = 0;
$colonna = 1;
$modifica_sez_cat_display = "display: none";
//Parsing Header matrice sezione categoria anno
foreach($categorie as $categoria) {
    $tpl->set_var("codice_y", $categoria->abbreviazione);
    $tpl->set_var("descr_x", $categoria->descrizione);
    $tpl->set_var("riga", 0);
    $tpl->set_var("colonna", $colonna++);

    $tpl->set_var("rowspan", 1);
    $tpl->set_var("colspan", 1);
    $tpl->parse("XData", true);
}

$riga = 1;
$sezioni = ValutazioniSezione::getAll();
$tpl->set_var("x_title", "tipologia scheda");
$tpl->set_var("x_data", "categoria");
$tpl->set_var("y_data", "sezione");

//Parsing body matrice sezione categoria anno
foreach($sezioni as $sezione) {
    $tpl->set_var("codice_y", $sezione->codice);
    $tpl->set_var("descr_y", $sezione->descrizione);
    $tpl->set_var("riga", $riga++);
    $colonna = 1;
    
    foreach($categorie as $categoria) {
        try {
            $peso = $sezione->getPesoAnno($valutazioneAnnoBudget, $categoria);
        } catch (Exception $ex) {
            $peso = "";
        }
        try {
            $sezione_peso_anno = ValutazioniSezionePesoAnno::factoryFromSezioneCategoriaAnno($sezione->id, $categoria->id, $valutazioneAnnoBudget->id);
            $modificabile_class = $sezione_peso_anno->canUpdate() ? "categoria_sezione_modificabile" : "categoria_sezione_non_modificabile";
        } catch (Exception $ex) {
            $modificabile_class = "categoria_sezione_modificabile";
        }

        if($modificabile_class == "categoria_sezione_modificabile") {
            $modifica_sez_cat_display = "";
        }

        $tpl->set_var("colonna", $colonna++);
        $tpl->set_var("peso_x_y", $peso);
        $tpl->set_var("id_y", $sezione->id);
        $tpl->set_var("modificabile_class", $modificabile_class);
        $tpl->set_var("style_peso", "width:100%");
        $tpl->set_var("id_x", $categoria->id);

        $tpl->parse("Peso", false);
        $tpl->set_var("rowspan", 1);
        $tpl->set_var("colspan", 1);
        $tpl->parse("XDataYData", true);

    }
    $tpl->set_var("colonna", 0);
    $tpl->parse("YData", true);
    $tpl->parse("XDataYData", false);

    //Viene svuotato il ParsedBlock SezioneCategoria prima del parsing della prosima matrice
    $tpl->ParsedBlocks["XDataYData"] = "";
    unset($modificabile_class);
}
$tpl->set_var("modifica_sez_cat_display", $modifica_sez_cat_display);
$tpl->parse ("AzioniMatrice", true);


$tpl->parse ("MatricePesi", true);
$tpl->parse("Script", true);

//Vengono svuotati i parsed blocks da riutilizzare
$tpl->ParsedBlocks["Peso"] = "";
$tpl->ParsedBlocks["YData"] = "";
$tpl->ParsedBlocks["XData"] = "";

$modifica_amb_cat_display = "display: none";
//Parsing Header matrice ambito categoria anno
foreach($categorie as $categoria) {
    $tpl->set_var("codice_y", $categoria->abbreviazione);
    $tpl->set_var("descr_x", $categoria->descrizione);
    $tpl->set_var("riga", 0);
    $tpl->set_var("colonna", $colonna++);

    $tpl->set_var("rowspan", 2);
    $tpl->set_var("colspan", 2);
    $tpl->parse("XData", true);
    $tpl->parse("HeaderPesoMetodo", true);
}

$tpl->ParsedBlocks["AzioniMatrice"] = "";

$riga = 1;
$ambiti = ValutazioniAmbito::getAll();

$tpl->set_var("x_title", "tipologia scheda");
$tpl->set_var("x_data", "categoria");
$tpl->set_var("y_data", "ambito");

//recupero id e descrizione dei metodi di valutazione
$metodi_valutazione = array();
foreach(ValutazioniAmbito::$metodi_valutazione as $metodo_valutazione) {
    $tpl->set_var("id_metodo", $metodo_valutazione["ID"]);
    $tpl->set_var("descr_metodo", $metodo_valutazione["descrizione"]);
    $tpl->parse("OptionsMetodo", true);

    //Costruisco un array di metodi di valutazione le cui chiavi sono gli ID
    $metodi_valutazione[$metodo_valutazione["ID"]] = $metodo_valutazione;
}
//Parsing body matrice sezione categoria anno
foreach($ambiti as $ambito) {

    $codice_sezione = "";
    foreach($sezioni as $sezione) {
        if($sezione->id == $ambito->id_sezione) {
            $codice_sezione = $sezione->codice;
            break;
        }
    }

    $tpl->set_var("codice_y", $codice_sezione . "." . $ambito->codice);
    $tpl->set_var("descr_y", $ambito->descrizione);
    $tpl->set_var("riga", $riga++);
    $colonna = 1;

    foreach($categorie as $categoria) {
        $peso = $ambito->getPesoAmbitoCategoriaAnno($categoria, $valutazioneAnnoBudget);            
        if ($peso == 0) {
            $peso = "";
        }

        try {
            $ambito_categoria_anno = ValutazioniAmbitoCategoriaAnno::factoryFromAmbitoCategoriaAnno($ambito->id, $categoria->id, $valutazioneAnnoBudget->id);
            $modificabile_class = $ambito_categoria_anno->canUpdate() ? "categoria_ambito_modificabile" : "categoria_ambito_non_modificabile";
        } catch (Exception $ex) {
            $modificabile_class = "categoria_ambito_modificabile";
        }

        if($modificabile_class == "categoria_ambito_modificabile") {
            $modifica_amb_cat_display = "";
        }

        $tpl->set_var("colonna", $colonna++);
        $tpl->set_var("peso_x_y", $peso);
        $tpl->set_var("id_y", $ambito->id);
        $tpl->set_var("modificabile_class", $modificabile_class);
        $tpl->set_var("style_peso", "width:50%");
        $tpl->set_var("id_x", $categoria->id);

        //Viene recuperato il metodo di valutazione di ambito categoria anno
        $id_metodo_valutazione = $ambito->getMetodoValutazioneAmbitoCategoriaAnno($categoria, $valutazioneAnnoBudget);
        $descr_metodo_selected = "";
        if($id_metodo_valutazione != 0) {
            $descr_metodo_selected = $metodi_valutazione[$id_metodo_valutazione]["descrizione"];
        }

        $tpl->set_var("metodo_valutazione_x_y", $descr_metodo_selected);
        $tpl->parse ("PesoMetodo", false);
        $tpl->set_var("rowspan", 2);
        $tpl->set_var("colspan", 2);
        $tpl->parse("XDataYData", true);

    }
    $tpl->set_var("colonna", 0);
    $tpl->parse("YData", true);
    $tpl->parse("XDataYData", false);

    //Viene svuotato il ParsedBlock CategoriaAmbito
    $tpl->ParsedBlocks["XDataYData"] = "";
    unset($modificabile_class);
}
$tpl->set_var("modifica_amb_cat_display", $modifica_amb_cat_display);
$tpl->parse ("AzioniMatrice", true);
$tpl->parse ("MatricePesi", true);
$tpl->parse("Script", true);

//***********************
die($tpl->rpparse("main", true));