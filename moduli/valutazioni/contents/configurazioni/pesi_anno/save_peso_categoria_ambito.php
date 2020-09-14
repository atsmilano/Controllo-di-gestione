<?php
if (isset($_GET["pesi"])){
    $anno = $cm->oPage->globals["anno"]["value"];
    foreach($_GET["pesi"] as $peso_amb_cat) {
        $id_categoria = intval($peso_amb_cat[1]);
        $id_ambito = intval($peso_amb_cat[2]);
        $peso = $peso_amb_cat[3];
        $metodo = intval($peso_amb_cat[4]) != 0 ? $peso_amb_cat[4] : null;

        //verifica sul peso ( primo controllo effettuato epr evitare calcoli in caso di dato non valido)
        if ($peso !== "" && !ctype_digit($peso)) {
            die(json_encode(array('messaggio' => "I pesi specificati devono essere numeri interi.", 'esito' => "error")));
        } elseif (!(isset($id_ambito) && strlen($id_ambito) > 0 && isset($id_categoria) && strlen($id_categoria) > 0)) {
            die(json_encode(array('messaggio' => "Categoria e/o ambito non valido/e.", 'esito' => "error")));
        }

        $peso_definito = strlen($peso) > 0;
        if(!$peso_definito && !empty($metodo)) {
            die(json_encode(array('messaggio' => "Il peso deve essere specificato se il metodo è impostato", 'esito' => "error")));
        }

        if($peso_definito && empty($metodo)) {
            die(json_encode(array('messaggio' => "Il metodo deve essere specificato se il peso è impostato", 'esito' => "error")));
        }

        try {
            $ambito_categoria_anno = ValutazioniAmbitoCategoriaAnno::factoryFromAmbitoCategoriaAnno($id_ambito, $id_categoria, $anno->id);
            if($peso_definito && !empty($metodo)) { // update
                $ambito_categoria_anno->peso = $peso;
                $ambito_categoria_anno->metodo = $metodo;

                if(!$ambito_categoria_anno->update()) {
                    ValutazioniHelper::setSavePesoError(sprintf(ValutazioniHelper::ERROR_ACTION_PESO, "modificato"));
                }
            } else { // delete
                if(!$ambito_categoria_anno->delete()) {
                    ValutazioniHelper::setSavePesoError(sprintf(ValutazioniHelper::ERROR_ACTION_PESO, "cancellato"));
                }
            }
        } catch (Exception $ex) { // insert
            $ambito_categoria_anno = new ValutazioniAmbitoCategoriaAnno();
            $ambito_categoria_anno->id_categoria = $id_categoria;
            $ambito_categoria_anno->id_ambito = $id_ambito;
            $ambito_categoria_anno->id_anno_budget = $anno->id;
            $ambito_categoria_anno->peso = $peso;
            $ambito_categoria_anno->metodo = $metodo;
            $ambito_categoria_anno->insert();
        }
    }
} else {
    die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: nessuna assegnazione variata.", 'esito' => "error")));
}
//istruzione raggiungibile solamente in caso di operazione effettuata con successo
die(json_encode(array('messaggio' => "Pesi aggiornati con successo.", 'esito' => "success")));