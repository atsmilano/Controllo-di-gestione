<?php
$user = LoggedUser::Instance();
if (!$user->hasPrivilege("deleghe_admin")){
    ffErrorHandler::raise("L'utente non possiede i privilegi d'accesso alla pagina");
}

$isEdit = false;
if (isset($_REQUEST["keys[ID]"])) {
    try {
        $delega = new DelegaAccesso($_REQUEST["keys[ID]"]);
        $isEdit = true;
    } catch (Exception $ex) {
        ffErrorHandler::raise($ex->getMessage());
    }
}

$anno = $cm->oPage->globals["anno"]["value"];

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "delega-modify";
$oRecord->title = $isEdit ? "Modifica delega": "Nuova delega";
$oRecord->resources[] = "delega";
$oRecord->src_table  = "delega_accesso";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

foreach (Personale::getAll() as $dipendente) {
    $dipendenti[] = array(
        new ffData($dipendente->matricola, "Number"),
        new ffData($dipendente->cognome . " " . $dipendente->nome . " (matr. " . $dipendente->matricola . ")", "Text"),
    );
}

$oField = ffField::factory($cm->oPage);
$oField->id = "matricola_utente";
$oField->base_type = "Text";
$deleganti = array();
$oField->extended_type = "Selection";
$oField->multi_pairs = $dipendenti;
$oField->label = "Delegante:";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "matricola_delegato";
$oField->base_type = "Text";
$deleganti = array();
$oField->extended_type = "Selection";
$oField->multi_pairs = $dipendenti;
$oField->label = "Delegato:";
$oField->required = true;
$oRecord->addContent($oField);

//******************************************************************************
//moduli associati alla delega
$oField = ffField::factory($cm->oPage);
$oField->id = "moduli_delega";
$oField->label = "Moduli accessibili tramite delega";
$oField->data_type = "callback";
$oField->data_source = "preload_relations";
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$moduli = Modulo::getActiveModulesFromDisk(null);
if (count($moduli) > 0)
{
	foreach($moduli as $modulo)	{
		$multipairs[] = array (new ffData($modulo->id, "Text"), new ffData($modulo->dir_path , "Text"));			
	}		
	$oField->multi_pairs = $multipairs;
	$oField->control_type = "input";
	$oField->widget = "checkgroup";
	$oField->grouping_separator = ",";
	$oField->store_in_db = false;
}
else 
{
	$oField->label = "Nessun modulo definito";
	$oField->data_type = "";	
	$oField->control_type = "label";
	$oField->store_in_db = false;
}
$oRecord->addContent($oField);
//precompilazione dei moduli della delega
function preload_relations($form_fields, $key, $first_access) {	
    $moduli_delega = "";
    if($first_access) {        		
        if(isset($_REQUEST["keys"]["ID"])) {
            $delega = new DelegaAccesso($_REQUEST["keys"]["ID"]);
			foreach ($delega->getModuliDelega() as $modulo_delega){					
				if(strlen($moduli_delega)) 
					$moduli_delega .= ",";						
				$moduli_delega .= ($modulo_delega->id); 
			}			
        }
        return new ffdata($moduli_delega);
    } 
	else 	
        return $form_fields[$key]->value;    	
}

//eventi record
$oRecord->addEvent("on_do_action","checkRelations");
$oRecord->addEvent("on_done_action","saveRelations");

$cm->oPage->addContent($oRecord);

function checkRelations($oRecord, $frmAction) {    
    switch($frmAction){        
        case "insert":
        case "update":
            foreach (DelegaAccesso::getAll() as $delega) {
                if ($oRecord->key_fields["ID"]->value->getValue() != $delega->id
                    &&    
                    $oRecord->form_fields["matricola_utente"]->value->getValue() == $delega->matricola_utente
                    && $oRecord->form_fields["matricola_delegato"]->value->getValue() == $delega->matricola_delegato ) {
                    $oRecord->tplDisplayError("Esiste già una delega per i dipendenti delegante e delegato selezionati.");
                    return true;
                }
            }        
        break;
        //l'eliminazione del dato viene definita in maniera personalizzata        
        case "confirmdelete":
            $delega_accesso = new DelegaAccesso($oRecord->key_fields["ID"]->value->getValue());
            $delega_accesso->delete();                        
            $oRecord->skip_action = true;
		break;
    }
}

//salvataggio delle relazioni
function saveRelations($oRecord, $frmAction) {	
	//gestione delle azioni sul record
	switch($frmAction) {
		case "insert":            						
		case "update":   
            if($oRecord->form_fields["moduli_delega"]->getValue() !== "")
                $moduli_delega = explode(",", $oRecord->form_fields["moduli_delega"]->getValue());	
            else
                $moduli_delega = array();

            $id_delega = $oRecord->key_fields["ID"]->value->getValue();
            if(isset($id_delega) && $id_delega != "") {
                $delega_accesso = new DelegaAccesso($id_delega);
            }
            $delega_accesso->saveModuliDelega($moduli_delega);            
		break;		        		
	}
}