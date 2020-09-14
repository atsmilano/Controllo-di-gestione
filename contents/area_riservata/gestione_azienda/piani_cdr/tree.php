<?php
$dateTimeObject = $cm->oPage->globals["data_riferimento"]["value"];

//viene caricato il template specifico per la pagina
$tpl = ffTemplate::factory((__DIR__) . "/tpl");
$tpl->load_file("tree.html", "main");

$error = false;
if (isset($_REQUEST["id_padre"]))
    $id_cdr_padre = $_REQUEST["id_padre"];
else
    ffErrorHandler::raise("Errore nel passaggio dei parametri: cdr");

//se il parametro di selezione del cdr padre risulta valido viene utilizzato
if ($id_cdr_padre != 0) {
    try {
        $cdr_padre = new Cdr($id_cdr_padre);
        $id_cdr_padre = $cdr_padre->id;
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

//viene passato l'id del piano cdr
if (isset($_REQUEST["id_piano_cdr"]))
    $id_piano_cdr = $_REQUEST["id_piano_cdr"];
else
    ffErrorHandler::raise("Errore nel passaggio dei parametri: id_piano_cdr");

try {
    $piano_cdr = new PianoCdr($id_piano_cdr);
} catch (Exception $ex) {
    ffErrorHandler::raise($ex->getMessage());
}
$tpl->set_var("id_piano_cdr", $id_piano_cdr);

//vengono estratti tutti i figli del cdr

if ($cdr_padre != null) {
    //ricerca dei figli tra i piani attivi
    $cdr_padre->useSql = true;
    $cdr_figli = $cdr_padre->getFigli();
}
//se il cdr padre ha id = 0 viene estratto il cdr radice del piano selezionato
else {
    $cdr_figli = array();
    $cdr_figli[] = $piano_cdr->getCdrRadice();
}

if (count($cdr_figli) == 1 && !isset($cdr_figli[0])) {
    $cdr_figli = array();
}

$tpl->set_var("id_cdr_padre", $id_cdr_padre);

if (count($cdr_figli) > 0) {
    foreach ($cdr_figli as $cdr_figlio) {
        //viene definita il numero di caratteri dove troncare la descrizione del cdr
        define("DESCLN", 60);
        define("RESPLN", 30);
        //vengono valorizzate le variabili opportune del template
        //id
        $tpl->set_var("id_cdr_figlio", $cdr_figlio->id);
        //tipo cdr
        $tipo_cdr = new TipoCdr($cdr_figlio->id_tipo_cdr);
        //descrizione
        $desc = $tipo_cdr->abbreviazione . " " . $cdr_figlio->codice . " - " . $cdr_figlio->descrizione;
        $short_desc = substr($desc, 0, DESCLN);
        if (strlen($desc) > DESCLN) {
            $short_desc .= "...";
        }
        $tpl->set_var("descrizione", str_pad($short_desc, DESCLN));
        $tpl->set_var("descrizione_completa", $desc);

        //numero di cdc associati
        $tpl->set_var("n_cdc", count($cdr_figlio->getCdc()));

        //Responsabile
        $responsabile = $cdr_figlio->getResponsabile($dateTimeObject);
        
        $short_resp = substr($responsabile->matricola_responsabile . " - " . $responsabile->cognome . " " . $responsabile->nome, 0, RESPLN);
        $tpl->set_var("responsabile", $short_resp);

        //Viene visualizzata l'icona di espansione dell'albero per il cdr in base al numero dei figli
        if (count($cdr_figlio->getFigli()) > 0) {
            $tpl->parse("ExpandTree", false);
            $tpl->set_var("NoExpandTree", "");
        } else {
            $tpl->parse("NoExpandTree", false);
            $tpl->set_var("ExpandTree", "");
        }
        $tpl->parse("CdrTree", true);
    }
} else {
    $tpl->parse("NoCdr", false);
}

//restituzione html
die($tpl->rpparse("main", false));
