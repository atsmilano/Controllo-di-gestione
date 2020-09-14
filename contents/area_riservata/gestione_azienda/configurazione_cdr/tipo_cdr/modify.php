<?php
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "tipo-cdr-modify";
$oRecord->title = "Tipologia cdr";
$oRecord->resources[] = "tipo-cdr";
$oRecord->src_table = "tipo_cdr";
$oRecord->addEvent("on_do_action","checkRelations");
$oRecord->addEvent("on_done_action","myUpdate");

//istanza e gestione della visualizzazione delle action
if(isset($_REQUEST["keys"]["ID"]))
{	
	try {
		$tipo_cdr = new TipoCdr($_REQUEST["keys"]["ID"]);	
	}
	catch (Exception $ex) {
		ffErrorHandler::raise($ex->getMessage());
	}
	
	if (!$tipo_cdr->canDelete()){
        $oRecord->allow_delete = false;
    }
}

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->label = "ID";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oField->required = true;
$oField->addValidator("text", array(true, 1, 50));
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "abbreviazione";
$oField->base_type = "Text";
$oField->label = "Abbreviazione";
$oField->required = true;
$oField->addValidator("text", array(true, 1, 10));
$oRecord->addContent($oField);

//tipi cdr definibili come padri
$oField = ffField::factory($cm->oPage);
$oField->id = "padri";
$oField->label = "Tipi cdr definibili come padri";
$oField->data_type = "callback";
$oField->data_source = "preload_relations";
$oField->base_type = "Text";
$oField->extended_type = "Selection";
$tipi_cdr_padre = TipoCdr::getAll();
if (count($tipi_cdr_padre) > 0)
{
	foreach($tipi_cdr_padre as $tipo_cdr_padre)	{
		$multipairs[] = array (new ffData($tipo_cdr_padre->id, "Text"), new ffData($tipo_cdr_padre->descrizione , "Text"));			
	}		
	$oField->multi_pairs = $multipairs;
	$oField->control_type = "input";
	$oField->widget = "checkgroup";
	$oField->grouping_separator = ",";
	$oField->store_in_db = false;
}
else 
{
	$oField->label = "Nessun tipo cdr definito";
	$oField->data_type = "";	
	$oField->control_type = "label";
	$oField->store_in_db = false;
}
$oRecord->addContent($oField);
//precompilazione dei tipi padre
function preload_relations($form_fields, $key, $first_access)
{	
    if($first_access) 
	{        
		$tipo_cdr = new TipoCdr($_REQUEST["keys"]["ID"]);
        if(isset($_REQUEST["keys"]["ID"])) 
		{
			foreach ($tipo_cdr->getPadri() as $tipo_cdr_padre)
			{					
				if(strlen($padri)) 
					$padri .= ",";						
				$padri .= ($tipo_cdr_padre->id); 
			}			
        }
        return new ffdata($padri);
    } 
	else 	
        return $form_fields[$key]->value;    	
}

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oRecord);

function checkRelations($oRecord, $frmAction) {    
    switch($frmAction){
        case "delete":
        case "confirmdelete":
        $tipo_cdr = new TipoCdr($oRecord->key_fields["ID"]->value->getValue());
        if(!$tipo_cdr->canDelete()){
            $oRecord->tplDisplayError("Il tipo CdR è utilizzato per almeno un CdR: impossibile eliminare.");
            return true;
        }
        break;
    }
}

//salvataggio delle relazioni (obiettivo_responsabile, obiettivo_dirigente, obiettivo_comparto)
function myUpdate($oRecord, $frmAction)
{
	if($oRecord->form_fields["padri"]->getValue() !== "")
		$tipi_cdr_padre = explode(",", $oRecord->form_fields["padri"]->getValue());	
	else
		$tipi_cdr_padre = array();
	//gestione delle azioni sul record
	switch($frmAction)
    {
		case "insert":							
			$tipo_cdr = new TipoCdr($oRecord->key_fields["ID"]->value->getValue());
			//vengono inserite tute le relazioni					
			foreach ($tipi_cdr_padre as $tipo_cdr_padre)
			{
				TipoCdrPadre::saveNew(array(
											"ID_tipo_cdr" => $tipo_cdr->id,
											"ID_tipo_cdr_padre" => $tipo_cdr_padre,
											));
			}
		break;								
		case "update":		
			$tipo_cdr = new TipoCdr($oRecord->key_fields["ID"]->value->getValue());
			//*************
			//obiettivi_cdr			
			//eliminazione delle relazioni non mantenute								
			foreach ($tipo_cdr->getPadriRelation() as $padre_db)
			{												
				$mantenuto = false;				
				foreach ($tipi_cdr_padre as $tipo_cdr_padre)
				{
					if ($tipo_cdr_padre == $padre_db->id_tipo_cdr_padre)
					{
						$mantenuto = true;
						break;
					}
				}
				
				if ($mantenuto == false)
					$padre_db->delete ();
			}
			//************************************
			//creazione o mantenimento delle relazioni selezionate
			//obiettivo_cdr_personale					
			foreach ($tipi_cdr_padre as $tipo_cdr_padre)
			{												
				//si prova ad istanziare la classe, in caso di eccezione (id non presente) viene salvata una nuova relazione
				try{
					TipoCdrPadre::instanceFromRelation($tipo_cdr, new TipoCdr($tipo_cdr_padre));
				} catch (Exception $ex) {
					TipoCdrPadre::saveNew(array(
											"ID_tipo_cdr" => $tipo_cdr->id,
											"ID_tipo_cdr_padre" => $tipo_cdr_padre,
											));
				}		
			}
		break;		
		case "delete":			
		case "confirmdelete":
            //propagazione sulla relazione tipo_cdr_padre
			foreach (TipoCdrPadre::getAll() as $tipo_cdr_padre) {												
				if ($tipo_cdr_padre->id_tipo_cdr == $oRecord->key_fields["ID"]->value->getValue())	
					$tipo_cdr_padre->delete ();
			}				
		break;
	}
}