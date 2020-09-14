<?php
if (isset($_REQUEST["id_anagrafica"]) && isset($_REQUEST["data_definizione"]) && isset($_REQUEST["id_cdr_padre"])) {
    $id_anagrafica = $_REQUEST["id_anagrafica"];
    $id_cdr_padre = $_REQUEST["id_cdr_padre"];
    
    $data_definizione = DateTime::createFromFormat("Y-m-d", $_REQUEST["data_definizione"]);
   
    if($data_definizione == false) {
        die(json_encode(array('messaggio'=>'Data di definizione non valida.', 'esito' => 'error')));
    }
    
    $codice = "";
    try {
        //Vengono selezionate solo le anagrafiche attive in data di definizione del piano
        $anagrafiche_cdr = AnagraficaCdr::getAnagraficaInData($data_definizione);
  
        foreach($anagrafiche_cdr as $anagrafica_cdr) {
            if((string)$anagrafica_cdr->id == $id_anagrafica) {
                $codice = $anagrafica_cdr->codice;
                break;
            }
        }
        
        //Si cerca di trovare il responsabile diretto del cdr, in caso contrario il messaggio non viene inizializzato
        $messaggio = "";        
        if($codice != "") {
            // Aggiunto try-catch per ignorare l'exception derivata da factoryFromCodiceCdr se il responsabile diretto non esiste.
            try {
                $responsabile_cdr = ResponsabileCdr::factoryFromCodiceCdr($codice, $data_definizione);
                $messaggio =  $responsabile_cdr->cognome . " " . $responsabile_cdr->nome . " (matr. ".$responsabile_cdr->matricola_responsabile.")"; 
            } catch(Exception $ex) {
                
            }
        } else {
            throw new Exception("Impossibile recuperare il codice cdr per l'anagrafica " . $id_anagrafica);
        }
        
        //Se il messaggio non è ancora stato inizializzato, non esiste un responsabile diretto
        //quindi si risale l'albero alla ricerca di un padre con responsabile diretto.
        if($messaggio == "") {
            $cdr_padre = new Cdr($id_cdr_padre);
            $responsabile_cdr = $cdr_padre->getResponsabile($data_definizione);
            $messaggio = $responsabile_cdr->cognome . " " . $responsabile_cdr->nome . " (matr. ".$responsabile_cdr->matricola_responsabile.")";
        }
        
    } catch (Exception $ex) {
       die(json_encode(array('messaggio' => $ex->getMessage(), 'esito' => "error")));
    }
    
    //Arrivato a questo punto, la ricerca è andata a buon fine e viene restituito il messaggio.
    die(json_encode(array(
        'messaggio' => $messaggio, 
        'esito' => 'success'
    )));
}