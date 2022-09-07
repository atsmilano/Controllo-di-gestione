<?php

//viene verificata la possibilità dell'utente di accedere alla pagina
//TODO verifica privilegi utente e possibilità modifica
//per ogni variazione del peso, se l'utente ha i privilegi per effettuarla viene salvato un record su db
if (isset($_GET["b"])) {
    $save = array();
    $delete = array();
    foreach ($_GET["b"] as $peso_ob_cdr_personale) {
        $riferimento_matrice = $peso_ob_cdr_personale[0];
        $id_obiettivo_cdr = $peso_ob_cdr_personale[1];
        $matricola_personale = $peso_ob_cdr_personale[2];
        $id_obiettivo_cdr_personale = $peso_ob_cdr_personale[3];
        if (strlen($id_obiettivo_cdr_personale) == 0) {
            $id_obiettivo_cdr_personale = null;
        }
        $peso = $peso_ob_cdr_personale[4];
        try {
            //verifica sul peso ( primo controllo effettuato epr evitare calcoli in caso di dato non valido)
            if ($peso !== "" && !ctype_digit($peso)) {
                die(json_encode(array('messaggio' => "I pesi specificati devono essere numeri interi.", 'esito' => "error")));
            }
            else if ($peso < OBIETTIVI_MIN_PESO && !$peso == 0) {
                die(json_encode(array('messaggio' => "I pesi specificati devono essere superiori o uguali a " . OBIETTIVI_MIN_PESO . ".", 'esito' => "error")));
            }
            else if ($peso > OBIETTIVI_MAX_PESO) {
                die(json_encode(array('messaggio' => "I pesi specificati devono essere inferiori o uguali a " . OBIETTIVI_MAX_PESO . ".", 'esito' => "error")));
            }
            //se la relazione risulta già esistente viene aggiornata
            if ($id_obiettivo_cdr_personale !== null) {
                try {
                    $obiettivo_cdr_personale = new ObiettiviObiettivoCdrPersonale($id_obiettivo_cdr_personale);
                    if ($peso !== "") {
                        $action = "save";
                    }
                    else {
                        $action = "delete";
                    }
                } catch (Exception $ex) {
                    die(json_encode(array("messaggio" => "Errore nel passaggio dei parametri: id ob_cdr_personale = " . $id_obiettivo_cdr_personale . " non valido.", "esito" => "error")));
                }
            }
            //altrimenti viene creata una nuova relazione
            else {
                $obiettivo_cdr_personale = new ObiettiviObiettivoCdrPersonale();
                $obiettivo_cdr_personale->id_obiettivo_cdr = $id_obiettivo_cdr;
                $obiettivo_cdr_personale->matricola_personale = $matricola_personale;

                $action = "save";
            }            
            if ($action == "save") {
                $obiettivo_cdr_personale->peso = $peso;
                $obiettivo_cdr_personale->data_ultima_modifica = date("Y-m-d H:i:s");
                $save[] = $obiettivo_cdr_personale;
            }
            else {
                $delete[] = $obiettivo_cdr_personale;
            }
        } catch (Exception $ex) {
            die(json_encode(array("messaggio" => "Errore nel passaggio dei parametri: id ob_cdr_personale = " . $id_obiettivo_cdr_personale . " non valido.", "esito" => "error")));
        }
    }
    //vengono effettuate le operazioni solamente se non si sono verificati errori bloccanti su nessuno degli obiettivi cdr   
    foreach ($save as $obiettivo_cdr_personale) {
        $obiettivo_cdr_personale->save();
    }
    foreach ($delete as $obiettivo_cdr_personale) {
        $obiettivo_cdr_personale->logicalDelete();
    }
}
else {
    die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: nessuna assegnazione variata.", 'esito' => "error")));
}
//istruzione raggiungibile solamente in caso di operazione effettuata con successo
die(json_encode(array('messaggio' => "Pesi aggiornati con successo.", 'esito' => "success")));
