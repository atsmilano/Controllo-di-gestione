<?php
$user = LoggedUser::Instance();
if (!$user->hasPrivilege("resp_cdr_selezionato")){
	die(json_encode(array("messaggio" => "Errore: si sta cercando di forzare la chiusura degli obiettivi di un cdr senza responsabilità.", "esito" => "error")));	
}

//recupero dei parametri
//anno***********
$anno = $cm->oPage->globals["anno"]["value"];
$cdr_global = $cm->oPage->globals["cdr"]["value"];
$date = $cm->oPage->globals["data_riferimento"]["value"];
$cdr = AnagraficaCdrObiettivi::factoryFromCodice($cdr_global->codice, $date);

//vengono estratti tutti gli obiettivi_cdr dell'anno
foreach ($cdr->getObiettiviCdrAnno($anno) as $obiettivo_cdr) {		
	//obiettivo_cdr  viene chiuso con la data corrente				
    $obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);
    if (($obiettivo_cdr->data_chiusura_modifiche == null || strtotime(date("Y-m-d")) < strtotime($obiettivo_cdr->data_chiusura_modifiche))){
        $obiettivo_cdr->data_chiusura_modifiche = date("Y-m-d");
        $obiettivo_cdr->save();
    }
}
die(json_encode(array("messaggio" => "Chiusura obiettivi effettuata con successo.", "esito" => "success")));