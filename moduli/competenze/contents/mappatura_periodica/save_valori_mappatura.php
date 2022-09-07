<?php
$user = LoggedUser::getInstance();

//TODO gestione privilegi (matricola_valutatore)

if (isset($_REQUEST["keys[ID_mappatura_periodo]"])) {
    try {
        $mappatura_periodo =  new \MappaturaCompetenze\MappaturaPeriodo($_REQUEST["keys[ID_mappatura_periodo]"]);
        $profilo = new \MappaturaCompetenze\Profilo($mappatura_periodo->id_profilo);
    } catch (Exception $ex) {
        ffErrorHandler::raise("Errore nel passaggio dei parametri");
    }
}
else {
    ffErrorHandler::raise("Errore nel passaggio dei parametri");
}

//per ogni variazione dei valori attesi, se l'utente ha i privilegi per effettuarla viene salvato un record su db
if (isset($_GET["ct"]) && isset($_GET["cs"])){
    //recupero dei valori attesi ammessi
    $valori_attesi_ammessi = $profilo->getValoriAssegnabili();
    foreach ($_GET["ct"] as $id_competenza_trasversale) {
        //selezione competenza trasversale per il profilo
        $mappatura_competenza_trasversale = \MappaturaCompetenze\ProfiloMappaturaCompetenzaPeriodo::getByFields(array("ID_mappatura_periodo"=>$mappatura_periodo->id, "ID_tipo_competenza"=>1, "ID_competenza"=>$id_competenza_trasversale["idc"]));
        $fields_to_save = array("ID_valore");
        if ($mappatura_competenza_trasversale == null) {
            $mappatura_competenza_trasversale = new \MappaturaCompetenze\ProfiloMappaturaCompetenzaPeriodo();
            $mappatura_competenza_trasversale->id_mappatura_periodo = $mappatura_periodo->id;
            $mappatura_competenza_trasversale->id_tipo_competenza = 1;
            $mappatura_competenza_trasversale->id_competenza = $id_competenza_trasversale["idc"];
            $fields_to_save = array_merge($fields_to_save,array("ID_mappatura_periodo", "ID_tipo_competenza", "ID_competenza"));
        }  
        $found = false;
        foreach ($valori_attesi_ammessi as $valore_mappatura_ammesso) {
            if ($valore_mappatura_ammesso->id == $id_competenza_trasversale["idva"]) {
                $found = true;
                break;
            }
        }
        if ($found == false) {
            die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: valore", 'esito' => "error")));
        }
        //verifica eventuale variazione della competenza trasversale
        if ($mappatura_competenza_trasversale->id_valore !== $id_competenza_trasversale["idva"]) {
            $mappatura_competenza_trasversale->id_valore = $id_competenza_trasversale["idva"];
            $mappatura_competenza_trasversale->save($fields_to_save);
        }
    }
    foreach ($_GET["cs"] as $id_competenza_specifica) {
        //selezione competenza specifica per il profilo
        $mappatura_competenza_specifica = \MappaturaCompetenze\ProfiloMappaturaCompetenzaPeriodo::getByFields(array("ID_mappatura_periodo"=>$mappatura_periodo->id, "ID_tipo_competenza"=>2, "ID_competenza"=>$id_competenza_specifica["idc"]));
        $fields_to_save = array("ID_valore");
        if ($mappatura_competenza_specifica == null) {
            $mappatura_competenza_specifica = new \MappaturaCompetenze\ProfiloMappaturaCompetenzaPeriodo();
            $mappatura_competenza_specifica->id_mappatura_periodo = $mappatura_periodo->id;
            $mappatura_competenza_specifica->id_tipo_competenza = 2;
            $mappatura_competenza_specifica->id_competenza = $id_competenza_specifica["idc"];
            $fields_to_save = array_merge($fields_to_save,array("ID_mappatura_periodo", "ID_tipo_competenza", "ID_competenza"));
        }           
        $found = false;
        foreach ($valori_attesi_ammessi as $valore_mappatura_ammesso) {
            if ($valore_mappatura_ammesso->id == $id_competenza_specifica["idva"]) {
                $found = true;
                break;
            }
        }
        if ($found == false) {
            die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: valore", 'esito' => "error")));
        }
        //verifica eventuale variazione della competenza specifica
        if ($mappatura_competenza_specifica->id_valore !== $id_competenza_specifica["idva"]) {
            $mappatura_competenza_specifica->id_valore = $id_competenza_specifica["idva"];
            $mappatura_competenza_specifica->save($fields_to_save);
        }
    }
    //salvatagigo dell'ora ultima modifica
    $mappatura_periodo->datetime_ultimo_salvataggio = date("Y-m-d H:i:s");
    $mappatura_periodo->save(array("datetime_ultimo_salvataggio"));
}
else {
    die(json_encode(array('messaggio' => "Errore nel passaggio dei parametri: nessuna assegnazione", 'esito' => "error")));
}
//istruzione raggiungibile solamente in caso di operazione effettuata con successo
die(json_encode(array('messaggio' => "Mappatura aggiornata con successo", 'esito' => "success")));