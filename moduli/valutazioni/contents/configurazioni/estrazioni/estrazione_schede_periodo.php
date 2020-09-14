<?php
ini_set("max_execution_time", VALUTAZIONI_MAX_EXECUTION_TIME);
if(isset($_GET["periodo"])) {
    try {
        $periodo_valutazione = new ValutazioniPeriodo($_GET["periodo"]);
        $anno_budget = new AnnoBudget($periodo_valutazione->id_anno_budget);                          
    } catch (Exception $e) {
        ffErrorHandler::raise($e->getMessage());
    }
}
else {
    ffErrorHandler::raise("Errore nel passaggio dei parametri: periodo.");
}

//definizione e verifica esistenza del percorso di destinazione delle estrazioni
$path = FF_DISK_PATH
            .DIRECTORY_SEPARATOR.'downloads'
            .DIRECTORY_SEPARATOR.FF_ENV
            .DIRECTORY_SEPARATOR.VALUTAZIONI_DOWNLOADABLE_EXTRACTIONS_DIR
            .DIRECTORY_SEPARATOR.$anno_budget->descrizione
            .DIRECTORY_SEPARATOR.$periodo_valutazione->id;
if (!file_exists($path)) {
    mkdir($path, 0775, true);
}
else {
    //se la directory risulta già esistente e il parametro forza l'eliminazione delle schede si procede con l'eliminazione
    if (isset($_GET["del"]) && $_GET["del"] == 1){
        $files_to_delete = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files_to_delete as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
    }
}
//path della pagina attuale
$module = Modulo::getCurrentModule();
$self_path = FF_SITE_PATH."/area_riservata".$module->site_path."/configurazioni/estrazioni/estrazione_schede_periodo?periodo=".$periodo_valutazione->id."&".$cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST);

//per ogni scheda di valutazione del periodo
$valutazioni_periodo = $periodo_valutazione->getValutazioniAttivePeriodo();
$n_schede_totali = count($valutazioni_periodo);
$progressivo = 1;
//progressivo schede
foreach($valutazioni_periodo as $valutazione) {
    $filename_scheda = $valutazione->matricola_valutatore."_".$valutazione->matricola_valutato."_".$valutazione->id.".pdf";
    //viene creata la scheda solamente se non è ancora presente
    //verifica scheda presente in directory
    if (!file_exists($path.DIRECTORY_SEPARATOR.$filename_scheda)) {
        echo("Generazione scheda ".$progressivo." / ".$n_schede_totali);
        //creazione e redirect a self
        //libreria per la generazione dei pdf
        error_reporting(0);

        //libreria per la generazione dei pdf
        require_once(FF_DISK_PATH.DIRECTORY_SEPARATOR."library".DIRECTORY_SEPARATOR."mpdf".DIRECTORY_SEPARATOR.CURRENT_USE_MPDF_VERSION.DIRECTORY_SEPARATOR."mpdf.php");

        $html = $valutazione->generazioneHtmlStampa();        
        //generazione pdf
        $mpdf = new mPDF();       
        $stylesheet = file_get_contents($module->module_theme_dir.DIRECTORY_SEPARATOR ."css".DIRECTORY_SEPARATOR."stampa.css");
        $mpdf->WriteHTML($stylesheet,1);
        $mpdf->WriteHTML($valutazione->generazioneHtmlStampa(),2);                
        $mpdf->Output($path.DIRECTORY_SEPARATOR.$filename_scheda, "F");        
        //viene effettuato un redirect alla pagina per procedere con l'estrazione successiva        
        header("refresh: 0; url=".$self_path);        
        exit();
    }
    else {
        $progressivo ++;
    }
}
if (isset ($_GET["download"]) && $_GET["download"]=="zip"){
    //se non ci sono schede ulteriori viene proposto il download dello zip
    $zip_file = $anno_budget->descrizione."_".$periodo_valutazione->id.".zip";
    
    $zip = new ZipArchive();
    $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    // Creazione iteratore ricorsivo per la directory
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file){
        // tutti i file che non sono directory vengono aggiunti allo zip
        if (!$file->isDir()){            
            $filePath = $file->getRealPath();                
            $zip->addFile($filePath, $file->getFilename());
        }
    }    
    $zip->close();

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($zip_file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($zip_file));
    readfile($zip_file);
}
else {
    echo("<p><a href='".$self_path."download=zip'>Download zip</a></p>");
}
die("<p>Tutte le valutazioni risultano estratte.<p>");