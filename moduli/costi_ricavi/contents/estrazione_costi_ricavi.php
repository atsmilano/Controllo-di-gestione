<?php
if (isset ($_GET["anno"])) {
	$anno = new AnnoBudget($_GET["anno"]);
    $data_riferimento = CoreHelper::getDataRiferimentoBudget($anno);
    
    $xls_file = "costi-ricavi-".$anno->descrizione."_".date("Ymd");
	$nome_foglio_lavoro = "Costi Ricavi";
    
    //inizializzazione matrice e intestazioni	
    $matrice_dati = array(
        array(
            "Fp codice",
            "Fp descrizione",
            "Conto codice",
            "Conto descrizione",
            "Codice Cdr",
            "Descrizione Cdr",
            "Periodo",
            "Tipo periodo",	
            "Conto Campo 1",
            "Conto Campo 2",
            "Conto Campo 3",
            "Conto Campo 4",
            "Fp-cdr Campo 1",
            "Fp-cdr Campo 2",
            "Fp-cdr Campo 3",
        )
    );
    
    $conti = array();
    foreach(CostiRicaviConto::getAll() as $conto) {
        $conti[$conto->id] = $conto;
    }
    
    $fpp = array();
    foreach(CostiRicaviFp::getFpAnno($anno) as $fp) {
        $fpp[$fp->id] = $fp;
    }      

    foreach (CostiRicaviPeriodo::getAll(array("ID_anno_budget"=>$anno->id)) as $periodo_anno) {        
        foreach (CostiRicaviImportoPeriodo::getAll() as $importo_periodo) {
            $record = array();
            if ($importo_periodo->id_periodo == $periodo_anno->id) {                
                $conto = $conti[$importo_periodo->id_conto];                
                $fp = $fpp[$conto->id_fp];
                try{
                    $cdr = AnagraficaCdr::factoryFromCodice($conto->codice_cdr, $data_riferimento);
                } catch(Exception $e){

                }

                $filters = array (
                                "ID_periodo" => $periodo_anno->id,
                                "ID_fp" => $fp->id,
                                "codice_cdr" => $conto->codice_cdr,			
                                );
                $valutazione_fp = CostiRicaviValutazioneFpCdr::getAll($filters);

                $record[] = $fp->codice;
                $record[] = $fp->descrizione;
                $record[] = $conto->codice;
                $record[] = $conto->descrizione;
                $record[] = $cdr->codice;
                $record[] = $cdr->descrizione;
                $record[] = $periodo_anno->descrizione;
                $record[] = $periodo_anno->id_tipo_periodo;	
                $record[] = $importo_periodo->campo_1!==null?$importo_periodo->campo_1:0;
                $record[] = $importo_periodo->campo_2!==null?$importo_periodo->campo_2:0;
                $record[] = $importo_periodo->campo_3!==null?$importo_periodo->campo_3:0;
                $record[] = $importo_periodo->campo_4!==null?$importo_periodo->campo_4:0;
                $record[] = $valutazione_fp[0]->campo_1;
                $record[] = $valutazione_fp[0]->campo_2;
                $record[] = $valutazione_fp[0]->campo_3;

                $matrice_dati[] = $record;
                unset($record);
            }
        }
    } 
    CoreHelper::simpleExcelWriter($xls_file, array($nome_foglio_lavoro => $matrice_dati));
}
else {
	mod_notifier_add_message_to_queue("Impossibile effettuare l'estrazione: errore nel passaggio dei parametri.", MOD_NOTIFIER_ERROR);	
}   