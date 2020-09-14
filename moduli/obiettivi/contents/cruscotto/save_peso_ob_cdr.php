<?php
//viene verificata la possibilità dell'utente di accedere alla pagina
//TODO verifica privilegi utente e possibilità modifica sul codice obiettivo (e verifica su cod cdr padre)
//per ogni variazione del peso, se l'utente ha i privilegi per effettuarla viene salvato un record su db
if (isset($_GET["a"])){
    $tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
    $cdr_padre = $cm->oPage->globals["cdr"]["value"];
    $save = array();
    $delete = array();
	foreach($_GET["a"] as $peso_ob_cdr){
        $riferimento_matrice = $peso_ob_cdr[0];        
        $id_obiettivo = $peso_ob_cdr[1];
        $codice_cdr = $peso_ob_cdr[2];       
        $id_obiettivo_cdr = $peso_ob_cdr[3];
        if (strlen($id_obiettivo_cdr)==0) {
            $id_obiettivo_cdr = null;
        }
        $peso = $peso_ob_cdr[4];				           						
        //verifica sul peso ( primo controllo effettuato epr evitare calcoli in caso di dato non valido)
        if ($peso!=="" && !ctype_digit($peso)){
            die(json_encode(array('messaggio' => "I pesi specificati devono essere numeri interi.", 'esito' => "error")));
        }
        else if ($peso<OBIETTIVI_MIN_PESO && !$peso==0){
            die(json_encode(array('messaggio' => "I pesi specificati devono essere superiori o uguali a ".OBIETTIVI_MIN_PESO.".", 'esito' => "error")));
        }
        else if ($peso>OBIETTIVI_MAX_PESO){
            die(json_encode(array('messaggio' => "I pesi specificati devono essere inferiori o uguali a ".OBIETTIVI_MAX_PESO.".", 'esito' => "error")));
        }
        //se la relazione risulta già esistente viene aggiornata
        if ($id_obiettivo_cdr !== null) {
            try {
                $obiettivo_cdr = new ObiettiviObiettivoCdr($id_obiettivo_cdr);
                if ($peso!=="") {
                    $action = "save";
                }
                else {
                    //l'obiettivo non può essere dissassociato dal cdr padre
                    if ($codice_cdr == $cdr_padre->codice) {
                        $peso = 0;
                        $action = "save";    
                    }         
                    else {
                        $action = "delete";
                    }
                }                    
            } 
            catch (Exception $ex) {
                die(json_encode(array("messaggio" => "Errore nel passaggio dei parametri: id ob_cdr = ".$id_obiettivo_cdr." non valido.", "esito" => "error")));
            }            
        }
        //altrimenti viene creata una nuova relazione
        else {
            $obiettivo_cdr = new ObiettiviObiettivoCdr();
            $obiettivo_cdr->id_tipo_piano_cdr = $tipo_piano_cdr->id;
            $obiettivo_cdr->id_obiettivo = $id_obiettivo;
            $obiettivo_cdr->codice_cdr = $codice_cdr;  
            
            $action = "save";
        }    
       
        $obiettivo_cdr->peso = $peso;
        $obiettivo_cdr->data_ultima_modifica = date("Y-m-d H:i:s");   
        //aggionrmaneto degli array per aggiornamento / eliminazione del dato
        if ($action == "save") {
            $save[] = $obiettivo_cdr;            		        
        }
        else {
            $delete[] = $obiettivo_cdr;                        
        }
    }
    //vengono effettuate le operazioni solamente se non si sono verificati errori bloccanti su nessuno degli obiettivi cdr   
    foreach ($save as $obiettivo_cdr) {
        $obiettivo_cdr->save();
    }   
    if (!empty($delete)) {
        $date = $cm->oPage->globals["data_riferimento"]["value"];
        foreach($delete as $obiettivo_cdr){
            foreach ($obiettivo_cdr->getDipendenze($date) as $obiettivo_cdr_dipendenza) {
                $obiettivo_cdr_dipendenza->logicalDelete();
            }
            $obiettivo_cdr->logicalDelete();
        }
    }
}
else {
	die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: nessuna assegnazione variata.", 'esito' => "error")));
}
//istruzione raggiungibile solamente in caso di operazione effettuata con successo
die(json_encode(array('messaggio' => "Pesi aggiornati con successo.", 'esito' => "success")));