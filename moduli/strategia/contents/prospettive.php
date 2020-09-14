<?php
$user = LoggedUser::Instance();
//verifica privilegi utente
if (!$user->hasPrivilege("strategia_prospettive_edit")) {
	ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alle prospettive strategiche.");	
}

//******************************************************************************
//popolamento della grid tramite array		
$source_sql = "";
$db = ffDB_Sql::factory();
foreach (StrategiaProspettiva::getAll() as $prospettiva) {	
	//viene visualizzato il dipendente solamente nel caso in cui abbia un'afferenza ad almeno un cdc di quelli attivi per il periodo e il piano	
	if (strlen($source_sql))
		$source_sql .= "UNION ";		

	$source_sql .= "SELECT			
					".$db->toSql($prospettiva->id)." AS ID,
					".$db->toSql($prospettiva->descrizione)." AS descrizione,
					".$db->toSql($prospettiva->anno_introduzione)." AS anno_introduzione,
					".$db->toSql($prospettiva->anno_termine)." AS anno_termine
				";
}
//visualizzazione della grid (nel caso in cui ci siano prospettive)
if (strlen($source_sql) > 0){
	$oGrid = ffGrid::factory($cm->oPage);
	$oGrid->id = "prospettive";
	$oGrid->title = "Prospettive strategiche";
	$oGrid->resources[] = "prospettiva";
	$oGrid->source_SQL = "	SELECT *
							FROM (".$source_sql.") AS prospettiva
							[WHERE]
							[HAVING]
							[ORDER]";
	$oGrid->order_default = "anno_introduzione";
	$oGrid->record_id = "prospettiva-modify";
	$oGrid->order_method = "labels";
	$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/prospettiva_modify";
	$oGrid->full_ajax = true;		
	$oGrid->display_new = false;	
	$oGrid->display_delete_bt = false;
    $oGrid->display_edit_url = false;
	$oGrid->display_search = false;

	// *********** FIELDS ****************
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID";
	$oField->base_type = "Number";
	$oField->label = "id";
	$oField->url = "";
	$oGrid->addKeyField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "descrizione";
	$oField->base_type = "Text";
	$oField->label = "Descrizione";
	$oField->encode_entities = false;
	$oGrid->addContent($oField);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "anno_introduzione";
	$oField->base_type = "Number";		
	$oField->label = "Anno Introduzione";
	$oGrid->addContent($oField);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "anno_termine";
	$oField->base_type = "Number";		
	$oField->label = "Anno Termine";
	$oGrid->addContent($oField);

	// *********** ADDING TO PAGE ****************
	$cm->oPage->addContent($oGrid);
}