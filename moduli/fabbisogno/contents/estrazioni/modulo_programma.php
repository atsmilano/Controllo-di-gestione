<?php
$modulo = core\Modulo::getCurrentModule();
$user = LoggedUser::getInstance();
$anno = $cm->oPage->globals["anno"]["value"];
$date = $cm->oPage->globals["data_riferimento"]["value"];

//recupero della richiesta
if (isset($_REQUEST["ID"])) {
    try {
        $richiesta = new FabbisognoFormazione\Richiesta($_REQUEST["ID"]);
        if ($richiesta->id_anno_budget !== $anno->id) {
            ffErrorHandler::raise("Errore nel passaggio dei parametri.");
        }
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}
else {
    ffErrorHandler::raise("Errore nel passaggio dei parametri: nessuna richiesta.");
}

//******************************************************************************
//verifica privilegi utente
$personale = \FabbisognoFormazione\Personale::factoryFromMatricola($user->matricola_utente_selezionato);

if (!($user->hasPrivilege("fabbisogno_admin") || $user->hasPrivilege("fabbisogno_operatore_formazione"))) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla richiesta di fabbisogno.");
}

//inclusione PhpWord
$phpword_path = FF_DISK_PATH.DIRECTORY_SEPARATOR."library".DIRECTORY_SEPARATOR."PhpWord";
require_once($phpword_path.DIRECTORY_SEPARATOR."PhpWord.php");

require_once($phpword_path.DIRECTORY_SEPARATOR."IOFactory.php");
require_once($phpword_path.DIRECTORY_SEPARATOR."Settings.php");
require_once($phpword_path.DIRECTORY_SEPARATOR."TemplateProcessor.php");

require_once($phpword_path.DIRECTORY_SEPARATOR."Exception".DIRECTORY_SEPARATOR."Exception.php");
require_once($phpword_path.DIRECTORY_SEPARATOR."Exception".DIRECTORY_SEPARATOR."CopyFileException.php");

require_once($phpword_path.DIRECTORY_SEPARATOR."Shared".DIRECTORY_SEPARATOR."Text.php");
require_once($phpword_path.DIRECTORY_SEPARATOR."Shared".DIRECTORY_SEPARATOR."ZipArchive.php");

//template
$nome_modello = "";

switch($richiesta->id_tipologia){
    //Formazione residenziale
    case 1:
    case 2:        
    case 3:        
    case 4: 
        $nome_tpl_modello = "A037-MD002 Programma evento residenziale.docx";        
    break;
    //Formazione sul campo
    case 5:
    case 6:
    case 7:
        $nome_tpl_modello = "A037-MD003 Progetto Formazione sul campo.docx";
    break;
    //Formazione a distanza
    case 9:        
    case 8:
    case 11:
        $nome_tpl_modello = "A037-MD018 Programma formazione a distanza.docx";
    break;
    case 10:
        //recupero del modulo selezionato per la formazione blended
        $id_modulo = null;
        if (isset($_REQUEST["ID_modulo"])) {
            $id_modulo = $_REQUEST["ID_modulo"];
            if ($id_modulo == 1) {
                $nome_tpl_modello = "A037-MD002 Programma evento residenziale.docx";
            }
            else if($id_modulo == 2){
                $nome_tpl_modello = "A037-MD003 Progetto Formazione sul campo.docx";
            }
            else if ($id_modulo == 3){
                $nome_tpl_modello = "A037-MD018 Programma formazione a distanza.docx";
            }
        }
        if ($id_modulo == null) {
            ffErrorHandler::raise("Errore nel passaggio dei parametri: nessun modello selezionato per la formazione blended.");
        }
    break;
}

$word_models_dir = $modulo->module_theme_dir . DIRECTORY_SEPARATOR . "modelli_programma";
$templateProcessor = new PhpOffice\PhpWord\TemplateProcessor($word_models_dir.DIRECTORY_SEPARATOR.$nome_tpl_modello);

$checkedBox='<w:sym w:font="Wingdings" w:char="F0FE"/>';
$unCheckedBox = '<w:sym w:font="Wingdings" w:char="F0A8"/>';

//checkbox tipologia
switch($richiesta->id_tipologia){
    //Formazione residenziale
    case 1:
    case 2:
        $templateProcessor->setValue('checkBoxRES1',$checkedBox);
        $templateProcessor->setValue('checkBoxRES2',$unCheckedBox);
        $templateProcessor->setValue('checkBoxRES3',$unCheckedBox);
        $templateProcessor->setValue('checkBoxBlended',$unCheckedBox);
    break;
    case 3:
        $templateProcessor->setValue('checkBoxRES2',$checkedBox);
        $templateProcessor->setValue('checkBoxRES1',$unCheckedBox);
        $templateProcessor->setValue('checkBoxRES3',$unCheckedBox);
        $templateProcessor->setValue('checkBoxBlended',$unCheckedBox);
    break;
    case 4:
        $templateProcessor->setValue('checkBoxRES3',$checkedBox);
        $templateProcessor->setValue('checkBoxRES1',$unCheckedBox);
        $templateProcessor->setValue('checkBoxRES2',$unCheckedBox);
        $templateProcessor->setValue('checkBoxBlended',$unCheckedBox);        
    break;
    //Formazione sul campo
    case 5:
        $templateProcessor->setValue('checkBoxFSC1',$checkedBox);    
        $templateProcessor->setValue('checkBoxFSC2',$unCheckedBox);    
        $templateProcessor->setValue('checkBoxFSC3',$unCheckedBox);
        $templateProcessor->setValue('checkBoxBlended',$unCheckedBox);
    break;
    case 6:
        $templateProcessor->setValue('checkBoxFSC2',$checkedBox);
        $templateProcessor->setValue('checkBoxFSC1',$unCheckedBox);     
        $templateProcessor->setValue('checkBoxFSC3',$unCheckedBox);
        $templateProcessor->setValue('checkBoxBlended',$unCheckedBox);
    break;
    case 7:
        $templateProcessor->setValue('checkBoxFSC3',$checkedBox);
        $templateProcessor->setValue('checkBoxFSC1',$unCheckedBox);    
        $templateProcessor->setValue('checkBoxFSC2',$unCheckedBox);
        $templateProcessor->setValue('checkBoxBlended',$unCheckedBox);
    break;

    //Formazione a distanza
    case 9:
        $templateProcessor->setValue('checkBoxFAD1',$checkedBox);
        $templateProcessor->setValue('checkBoxFAD2',$unCheckedBox);    
        $templateProcessor->setValue('checkBoxFAD3',$unCheckedBox);
        $templateProcessor->setValue('checkBoxBlended',$unCheckedBox);
    break;
    case 8:
        $templateProcessor->setValue('checkBoxFAD2',$checkedBox);
        $templateProcessor->setValue('checkBoxFAD1',$unCheckedBox);   
        $templateProcessor->setValue('checkBoxFAD3',$unCheckedBox);
        $templateProcessor->setValue('checkBoxBlended',$unCheckedBox);
    break;
    case 11:
        $templateProcessor->setValue('checkBoxFAD3',$checkedBox);
        $templateProcessor->setValue('checkBoxFAD1',$unCheckedBox);
        $templateProcessor->setValue('checkBoxFAD2',$unCheckedBox);
        $templateProcessor->setValue('checkBoxBlended',$unCheckedBox);
    break;

    //Formazione blended
    case 10:
        switch ($id_modulo) {
            case 1:
                $templateProcessor->setValue('checkBoxRES1',$checkedBox);
                $templateProcessor->setValue('checkBoxRES2',$unCheckedBox);
                $templateProcessor->setValue('checkBoxRES3',$unCheckedBox);
                $templateProcessor->setValue('checkBoxBlended',$checkedBox);
            break;
            case 2:
                $templateProcessor->setValue('checkBoxFSC1',$unCheckedBox);    
                $templateProcessor->setValue('checkBoxFSC2',$checkedBox);    
                $templateProcessor->setValue('checkBoxFSC3',$unCheckedBox);
                $templateProcessor->setValue('checkBoxBlended',$checkedBox);
            break;
            case 3:
                $templateProcessor->setValue('checkBoxFAD1',$unCheckedBox);
                $templateProcessor->setValue('checkBoxFAD2',$unCheckedBox);    
                $templateProcessor->setValue('checkBoxFAD3',$checkedBox);
                $templateProcessor->setValue('checkBoxBlended',$checkedBox);
            break;
        }
    break;
}
$templateProcessor->setValue('titolo', $richiesta->titolo);
$templateProcessor->setValue('descrizione', $richiesta->descrizione);
$templateProcessor->setValue('obiettivi_specifici', $richiesta->obiettivi_formativi);

//obiettivi formativi
$obiettivo_formativo = new \FabbisognoFormazione\ObiettivoRiferimento($richiesta->id_obiettivo_riferimento);
switch($obiettivo_formativo->id_area_riferimento){
    case 1:
        $templateProcessor->setValue('checkBoxObTecnici',$checkedBox);
        $templateProcessor->setValue('checkBoxObProcesso',$unCheckedBox);
        $templateProcessor->setValue('checkBoxObSistema',$unCheckedBox);
    break;
    case 2:
        $templateProcessor->setValue('checkBoxObTecnici',$unCheckedBox);
        $templateProcessor->setValue('checkBoxObProcesso',$checkedBox);
        $templateProcessor->setValue('checkBoxObSistema',$unCheckedBox);
    break;
    case 3:
        $templateProcessor->setValue('checkBoxObTecnici',$unCheckedBox);
        $templateProcessor->setValue('checkBoxObProcesso',$unCheckedBox);
        $templateProcessor->setValue('checkBoxObSistema',$checkedBox);       
    break;
}

$obiettivo_riferimento = new \FabbisognoFormazione\ObiettivoRiferimento($richiesta->id_obiettivo_riferimento);
$templateProcessor->setValue('n_obiettivo_riferimento', $obiettivo_riferimento->codice);
$templateProcessor->setValue('desc_obiettivo_riferimento', $obiettivo_riferimento->descrizione);

$responsabile_scientifico = \Personale::factoryFromMatricola($richiesta->matricola_responsabile_scientifico);    
$templateProcessor->setValue('resp_scientifico_cognome_nome', $responsabile_scientifico->cognome." ".$responsabile_scientifico->nome);

$referente_segreteria = \Personale::factoryFromMatricola($richiesta->matricola_referente_segreteria);
$templateProcessor->setValue('segreteria_cognome_nome', $referente_segreteria->cognome." ".$referente_segreteria->nome);
$templateProcessor->setValue('segreteria_telefono', $richiesta->telefono_segreteria_organizzativa);
$templateProcessor->setValue('segreteria_mail', $richiesta->mail_segreteria_organizzativa);

if ($richiesta->costi_edizione > 0) {
    $templateProcessor->setValue('checkBoxOneriNo', $unCheckedBox);
    $templateProcessor->setValue('checkBoxOneriSi', $checkedBox);
    $templateProcessor->setValue('costiEdizione', $richiesta->costi_edizione);
}
else {    
    $templateProcessor->setValue('checkBoxOneriNo', $checkedBox);
    $templateProcessor->setValue('checkBoxOneriSi', $unCheckedBox);
    $templateProcessor->setValue('costiEdizione', 0);
}

switch($richiesta->id_tipologia){
    //Formazione residenziale
    case 1:
    case 2:        
    case 3:        
    case 4: 
    //Formazione a distanza
    case 9:        
    case 8:
    case 10:
    case 11:
        if ($richiesta->id_tipologia !== 10 || ($richiesta->id_tipologia == 10 && ($id_modulo == 1 || $id_modulo == 3))){
            if ($richiesta->quota_iscrizione_destinatari_esterni > 0) {
                $templateProcessor->setValue('checkBoxQuotaNo', $unCheckedBox);
                $templateProcessor->setValue('checkBoxQuotaSi', $checkedBox);
                $templateProcessor->setValue('quotaIscrizione', $richiesta->quota_iscrizione_destinatari_esterni);
            }
            else {    
                $templateProcessor->setValue('checkBoxQuotaNo', $checkedBox);
                $templateProcessor->setValue('checkBoxQuotaSi', $unCheckedBox);
                $templateProcessor->setValue('quotaIscrizione', 0);
            }
        }               
    break;
}

$templateProcessor->setValue('n_ore', $richiesta->n_ore);

//file download
header('Content-Disposition: attachment; filename="Modulo_'.$richiesta->id."_".$date->format("Ymd").'.doc"');
$templateProcessor->saveAs('php://output');
die();