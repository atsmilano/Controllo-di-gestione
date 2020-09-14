<?php
//estrazione di tutti i file mod_config.xml e common.php nelle sottocartelle (a qualsiasi livello dell'albero)
$anno = $cm->oPage->globals["anno"]["value"];
//nel caso in cui ci si trovi in questo ciclo significa che il modulo è valido, vengono caricati i files delle classi e common
//vengono prima di tutto caricate le classi perchè potrebbero essere utili alla creazione dei menu di moduli dipendenti
//(ad esempio per visualizzare un menu in base alla visibilità dei sottomenu)

foreach ($cm->oPage->globals["modules"]["value"] as $module) {	
    //in caso di utente non amministratore con matricola selezionata differente da quella dell'utente collegato (selezione dipendente)
    //viene verificato che l'utente abbia la delega per quel modulo
    $view_module = true;
    if ($user->hasPrivilege("user_selection") == false && $user->hasPrivilege("delega_accesso") == true 
            && ($user->matricola_utente_collegato !== $user->matricola_utente_selezionato)){        
        if(!$user->hasDelegaModuloAnno($module)) {
            $view_module = false;
        }
    }    
    if ($view_module == true) {
        foreach (glob(MODULES_DISK_PATH.DIRECTORY_SEPARATOR.$module->dir_path.DIRECTORY_SEPARATOR.MODULES_CLASSES_DIR.DIRECTORY_SEPARATOR."*.php") as $filename){
            require($filename);
        }        
        //inclusione file common
        require(MODULES_DISK_PATH.$module->dir_path.DIRECTORY_SEPARATOR.MODULES_COMMON_FILE);               
    }
}