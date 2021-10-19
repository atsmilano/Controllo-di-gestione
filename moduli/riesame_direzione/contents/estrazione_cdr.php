<?php
$user = LoggedUser::getInstance();
//verifica che il cdr sia di responsabilità dell'utente
if (!$user->hasPrivilege("riesame_direzione_view")){
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per visualizzare la pagina");
}

$view_all = false;
if ($user->hasPrivilege("riesame_direzione_admin")){
    $view_all = true;
}
//configurazione xls
$xls_file = "riesame-".date("Ymd");
$nome_foglio_lavoro = "Riesame Direzione";

//recupero dei parametri
$anno = $cm->oPage->globals["anno"]["value"];
$cdr = $cm->oPage->globals["cdr"]["value"];
$piano_cdr = new PianoCdr($cdr->id_piano_cdr);

//recupero di tutti i cdr per cui visualizzare il riesame
$cdr_da_estrarre = array();
if ($view_all == false) {                    
    $cdr_da_estrarre = $cdr->getFigli(); 
    $cdr_da_estrarre[] = $cdr;
}
else {
    $cdr_da_estrarre = $piano_cdr->getCdr();
}    

//matrice dati
$matrice_dati = array(array("Area","Item"));
//recupero di tutti i campi da visualizzare
$campi_riesame_anno = RiesameDirezioneCampo::getCampiAnno($anno);
$i=1;
$id_sezione_attuale = 0;
foreach ($campi_riesame_anno as $campo) {
    //cambio sezione attuale            
    if ($id_sezione_attuale !== $campo->id_sezione) {
        $id_sezione_attuale = $campo->id_sezione;
        $sezione = new RiesameDirezioneSezione($id_sezione_attuale);
    }
    $matrice_dati[$i][0] = $sezione->descrizione;
    $matrice_dati[$i][1] = $campo->descrizione;
    $i++;
}

$j=2;
//dati
foreach ($cdr_da_estrarre as $cdr_estrazione) {      
    $tipo_cdr = new TipoCdr($cdr_estrazione->id_tipo_cdr);
    $matrice_dati[$i=0][$j] = $cdr_estrazione->codice." - ".$tipo_cdr->abbreviazione." ".$cdr_estrazione->descrizione;
    $i++;
    try {
        $riesame = RiesameDirezioneRiesame::factoryFromCdrAnno($cdr_estrazione, $anno);        
    } catch (Exception $ex) {
        $riesame = null;
    }
        
    foreach ($campi_riesame_anno as $campo) {
        if ($riesame !== null) {
            
            switch ($campo->id_tipo_campo) {
                //campo testo
                case 1:
                    $valore_campo = $campo->getValoreCampoRiesame($riesame);
                break;
                //flag
                case 2:
                    $valore_campo = $campo->getValoreCampoRiesame($riesame);
                    if ($valore_campo !== "") {
                        $valore_campo = $valore_campo==1?"Si":"No";
                    }
                    else {
                        $valore_campo = "";
                    }
                break;
            }
        }
        else {
            $valore_campo = "";
        }    
        $matrice_dati[$i][$j] = $valore_campo;
        $i++;       
    }
    $j++;
} 
CoreHelper::simpleExcelWriter($xls_file, array($nome_foglio_lavoro => $matrice_dati));