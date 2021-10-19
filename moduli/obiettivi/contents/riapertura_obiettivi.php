<?php
$user = LoggedUser::getInstance();

if (!$user->hasPrivilege("obiettivi_aziendali_edit")){
    die(json_encode(array("messaggio" => "Errore: si sta cercando di forzare la riapertura degli obiettivi di un cdr .", "esito" => "error")));	
}

//recupero dei parametri
//anno***********
$anno = $cm->oPage->globals["anno"]["value"];
$cdr_global = $cm->oPage->globals["cdr"]["value"];
$date = $cm->oPage->globals["data_riferimento"]["value"];
$cdr = AnagraficaCdrObiettivi::factoryFromCodice($cdr_global->codice, $date);

//vengono estratti tutti gli obiettivi_cdr dell'anno
foreach ($cdr->getObiettiviCdrAnno($anno) as $obiettivo_cdr) {		
    //obiettivo_cdr viene riaperto
    $obiettivo = new ObiettiviObiettivo($obiettivo_cdr->id_obiettivo);
    $obiettivo_cdr->data_chiusura_modifiche = NULL;
    $obiettivo_cdr->data_ultima_modifica = date("Y-m-d H:i:s");
    $obiettivo_cdr->save();
}
die(json_encode(array("messaggio" => "Riapertura obiettivi CdR effettuata con successo.", "esito" => "success")));