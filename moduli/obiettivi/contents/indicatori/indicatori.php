<?php
$user = LoggedUser::Instance();
//verifica privilegi utente
if (!$user->hasPrivilege("indicatori_edit")) {
	ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione degli indicatori.");	
}

//recupero dei parametri
$anno = $cm->oPage->globals["anno"]["value"];

//popolamento della grid tramite array		
$dateTimeObject = $cm->oPage->globals["data_riferimento"]["value"];
$date = $dateTimeObject->format("Y-m-d");

//vengono estratti tutti gli obiettivi dell'anno
//popolamento della grid tramite array		
$grid_fields = array(
                    "ID",
                    "codice",
                    "nome",
                    "descrizione",
                    "obiettivi_collegati",
                    "anno_introduzione",
                    "anno_termine",
);
$grid_recordset = array();
foreach (IndicatoriIndicatore::getIndicatoriAnno($anno) as $indicatore_anno) {
    //recupero degli obiettivi eventualmente associati all'indicatore
    $obiettivi_collegati = "";
    foreach ($indicatore_anno->getObiettiviCollegati($anno) as $obiettivo_collegato) {
        if (strlen($obiettivi_collegati)>0) {
            $obiettivi_collegati .= "\n";
        }
        $obiettivi_collegati .= $obiettivo_collegato->codice;
    }
    $grid_recordset[] = array(
                                $indicatore_anno->id,
                                $indicatore_anno->codice, 
                                $indicatore_anno->nome,
                                $indicatore_anno->descrizione,
                                $obiettivi_collegati,
                                $indicatore_anno->anno_introduzione,
                                $indicatore_anno->anno_termine,
                            );
}

//visualizzazione della grid (nel caso in cui ci siano obiettivi per l'anno)
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "indicatori";
$oGrid->title = "Indicatori anno ".$anno->descrizione;
$oGrid->resources[] = "indicatore";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "indicatori_indicatore");
$oGrid->order_default = "nome";
$oGrid->record_id = "indicatore-modify";
$oGrid->order_method = "labels";	
//costruzione dell'url del record (viene selelezionata la directory corrente con substr (path - ultima_parte_del_path))
$path_info_parts = explode("/", $cm->path_info);	
$path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
$oGrid->record_url = FF_SITE_PATH . $path_info . "indicatore_modify";	
$oGrid->full_ajax = true;

$oGrid->display_navigator = false;
$oGrid->use_paging = false;

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_indicatore";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oField->label = "id";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "codice";
$oField->base_type = "Text";
$oField->label = "Codice";		
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->base_type = "Text";		
$oField->label = "Nome";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";
$oField->label = "Descrizione";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "obiettivi_collegati";
$oField->base_type = "Text";
$oField->label = "Obiettivi ".$anno->descrizione." collegati";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_introduzione";
$oField->base_type = "Number";		
$oField->label = "Anno inizio validità";
$oGrid->addContent($oField, "validita");

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_termine";
$oField->base_type = "Number";		
$oField->label = "Anno fine validità";
$oGrid->addContent($oField, "validita");

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);