<?php   
$user = LoggedUser::getInstance();
if (!$user->hasPrivilege("moduli_admin")) {
    ffErrorHandler::raise("L'utente non possiede i privilegi d'accesso alla pagina");
}

$anno = $cm->oPage->globals["anno"]["value"];
$moduli = Modulo::getActiveModulesFromDisk();

//recupero degli indicatori collegati all'obiettivo
$grid_fields = array(
                "ID",
                "ordine_caricamento",
                "dir_path",
                "anno_inizio",
                "anno_fine",
);
$grid_recordset = array();
foreach ($moduli as $modulo) {    
    $grid_recordset[] = array(                                
                            $modulo->id,
                            $modulo->ordine_caricamento,
                            $modulo->dir_path,
                            $modulo->anno_inizio,
                            $modulo->anno_fine,
                        );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "moduli";
$oGrid->title = "Moduli disponibili";
$oGrid->resources[] = "obiettivo-cdr";
//viene passato un parametro fittizio per il nome della tabella per il funzionamento del metodo
$oGrid->source_SQL = CoreHelper::getGridSqlFromArray($grid_fields, $grid_recordset, "anno_budget");
$oGrid->order_default = "ID";
$oGrid->record_id = "modulo-modify";
$oGrid->order_method = "labels";	
$oGrid->record_url = "";		
//parametri aggiuntivi
$oGrid->display_navigator = false;
$oGrid->use_paging = false;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';
$oGrid->display_new = false;	
$oGrid->display_edit_url = false;
$oGrid->display_delete_bt = false;		

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->label = "ID";		
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ordine_caricamento";
$oField->base_type = "Number";
$oField->label = "Ordine caricamento";		
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "dir_path";
$oField->base_type = "Text";
$oField->label = "Percorso";		
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_inizio";
$oField->base_type = "Text";
$oField->label = "Anno inizio disponibilità";		
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_fine";
$oField->base_type = "Text";		
$oField->label = "Anno fine disponibilità";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);