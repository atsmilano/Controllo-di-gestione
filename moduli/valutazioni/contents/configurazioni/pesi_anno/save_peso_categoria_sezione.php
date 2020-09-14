<?php
if (isset($_GET["pesi"])){
    $anno = $cm->oPage->globals["anno"]["value"];

    foreach($_GET["pesi"] as $peso_sez_cat) {
        $id_categoria = intval($peso_sez_cat[1]);
        $id_sezione = intval($peso_sez_cat[2]);
        $peso = $peso_sez_cat[3];

        //verifica sul peso ( primo controllo effettuato per evitare calcoli in caso di dato non valido)
        if ($peso !== "" && !ctype_digit($peso)) {
            die(json_encode(array('messaggio' => "I pesi specificati devono essere numeri interi.", 'esito' => "error")));
        } elseif (!(isset($id_sezione) && strlen($id_sezione) > 0 && isset($id_categoria) && strlen($id_categoria) > 0)) {
            die(json_encode(array('messaggio' => "Categoria e/o sezione non valida/e.", 'esito' => "error")));
        }

        try {
            $sezione_categoria_anno = ValutazioniSezionePesoAnno::factoryFromSezioneCategoriaAnno($id_sezione, $id_categoria, $anno->id);
            if($peso != "") { // update
                if(!$sezione_categoria_anno->update()) {
                    ValutazioniHelper::setSavePesoError(sprintf(ValutazioniHelper::ERROR_ACTION_PESO, "modificato"));
                }
            } else { // delete
                if(!$sezione_categoria_anno->delete()) {
                    ValutazioniHelper::setSavePesoError(sprintf(ValutazioniHelper::ERROR_ACTION_PESO, "cancellato"));
                }
            }
        } catch (Exception $ex) { // insert
            $sezione_categoria_anno = new ValutazioniSezionePesoAnno();
            $sezione_categoria_anno->id_categoria = $id_categoria;
            $sezione_categoria_anno->id_sezione = $id_sezione;
            $sezione_categoria_anno->id_anno_budget = $anno->id;
            $sezione_categoria_anno->peso = $peso;
            $sezione_categoria_anno->insert();
        }
    }

} else {
    die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: nessuna assegnazione variata.", 'esito' => "error")));
}
//istruzione raggiungibile solamente in caso di operazione effettuata con successo
die(json_encode(array('messaggio' => "Pesi aggiornati con successo.", 'esito' => "success")));