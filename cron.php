<?php
//lo script viene eseguito da riga di comando (richiamato da crontab con parametro environment gestito da config.php)
// ES. php c:\path\budget\cron.php --env=FF_ENV_XXX
if(php_sapi_name() == "cli") {
    //recupero del parametro per l'environment
    //inclusione del corretto file di configurazione per l'environment considerato
    require_once (__DIR__.DIRECTORY_SEPARATOR."config.php");
    require_once (__DIR__.DIRECTORY_SEPARATOR."conf".DIRECTORY_SEPARATOR."core_init.php");
    //inclusione ff
    require_once (__DIR__.DIRECTORY_SEPARATOR."ff".DIRECTORY_SEPARATOR."main.php");
    //recupero dell'anno budget alla data corrente
    $anno_budget = AnnoBudget::getAll(array("descrizione"=>date('Y')));
    //recupero dei moduli attivi
    if ($anno_budget !== null) {
        $active_modules = \core\Modulo::getActiveModulesFromDisk($anno_budget[0]);                     
        //per ogni modulo attivo vengono eseguiti gli script presenti nella directory delle operazioni pianificate se presenti
        foreach ($active_modules as $module) { 
            //recupero e definizione delle costanti dei moduli (sovrascritte eventualmente da environments)
            foreach ($module->getEnvConstants() as $const =>$value){
                define($const, $value);
            } 
            $load_module_classes = true;
            foreach (glob(MODULES_DISK_PATH.$module->dir_path.DIRECTORY_SEPARATOR.MODULES_CRON_DIR.DIRECTORY_SEPARATOR."*.php") as $cron_filename){
                //inclusione delle classi dei moduli con dei cron definiti
                if ($load_module_classes == true){
                    foreach (glob(MODULES_DISK_PATH.$module->dir_path.DIRECTORY_SEPARATOR.MODULES_CLASSES_DIR.DIRECTORY_SEPARATOR."*.php") as $class_filename){                
                        require($class_filename);
                    }     
                }
                $load_module_classes = false;
                require($cron_filename);                
            } 
        }
    }
}
die;