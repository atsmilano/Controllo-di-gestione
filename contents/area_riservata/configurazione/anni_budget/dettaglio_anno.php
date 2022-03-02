<?php
$user = LoggedUser::getInstance();
if (!$user->hasPrivilege("anni_budget_admin")){
    ffErrorHandler::raise("L'utente non possiede i privilegi d'accesso alla pagina");
}

$isEdit = false;
if (isset($_REQUEST["keys[ID]"])) {
    try {
        $anno = new AnnoBudget($_REQUEST["keys[ID]"]);
        $isEdit = true;
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "anno-modify";
$oRecord->title = $isEdit ? "Modifica anno budget": "Nuovo anno budget";
$oRecord->resources[] = "anno";
$oRecord->src_table  = "anno_budget";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
$oField->addValidator("number", array(true, 2010, 2099, true, true));
$oRecord->addContent($oField);

$si_no_multipairs = array (
                            array(new ffData("1", "Number"), new ffData("Si", "Text")),
                            array(new ffData("0", "Number"), new ffData("No", "Text")),
                        );

$oField = ffField::factory($cm->oPage);
$oField->id = "attivo";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->control_type = "radio";
$oField->multi_pairs = $si_no_multipairs;
$oField->label = "Attivo";           
$oField->default_value = new ffData($anno->attivo==true?"Si":"No", "Text");
$oField->required = true;
$oRecord->addContent($oField);
$oRecord->addEvent("on_do_action", "checkEsistente");
$oRecord->addEvent("on_done_action", "updatePredefiniti");

$oField = ffField::factory($cm->oPage);
$oField->id = "predefinito";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->control_type = "radio";
$oField->multi_pairs = $si_no_multipairs;
$oField->label = "Predefinito";           
$oField->default_value = new ffData($anno->attivo==true?"Si":"No", "Text");
$oField->required = true;
$oRecord->addContent($oField);
    
$cm->oPage->addContent($oRecord);

function checkEsistente($oRecord, $frmAction) {  
    switch($frmAction)
    {        
        case "insert":	
            foreach (AnnoBudget::getAll() as $anno) {
                if ($anno->descrizione == $oRecord->form_fields["descrizione"]->value->getValue()) {
                    CoreHelper::setError($oRecord, $anno->descrizione . " già presente");               
                    return true;
                }
            }
        case "update":
            foreach (AnnoBudget::getAll() as $anno) {
                if ($anno->id !== $oRecord->key_fields["ID"]->value->getValue() && $anno->descrizione == $oRecord->form_fields["descrizione"]->value->getValue()) {
                    CoreHelper::setError($oRecord, $anno->descrizione . " già presente");               
                    return true;
                }
            }
        break;	
    }
}

function updatePredefiniti($oRecord, $frmAction) {    
    //se l'anno viene definito come predefinito viene valorizzato il campo predefinito di tutti gli altri anni a false
    switch($frmAction)
    {        
        case "insert":							                   							
        case "update":	
            if($oRecord->form_fields["predefinito"]->getValue() == true) {
                foreach (AnnoBudget::getAll() as $anno) {
                    if ($anno->id !== $oRecord->key_fields["ID"]->value->getValue()) {
                        if ($anno->predefinito == true) {
                            $anno->predefinito = false;
                            $anno->save(array("predefinito"));
                        }
                    }
                }
            }
        break;	
    }
}