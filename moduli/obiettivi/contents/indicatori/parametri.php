<?php
//predisposizione dati per la grid
//popolamento della grid tramite array		
$grid_fields = array(
                    "ID",
                    "nome",
                    "tipo_parametro",
                    "anno_introduzione",
                    "anno_termine",
);
$grid_recordset = array();
//estrazione parametri
foreach (IndicatoriParametro::getAll() as $parametro) {				
    $tipo_parametro = new IndicatoriTipoParametro($parametro->id_tipo_parametro);
    $grid_recordset[] = array(
                            $parametro->id,
                            $parametro->nome,
                            $tipo_parametro->nome,
                            $parametro->anno_introduzione,
                            $parametro->anno_termine,
                            );            
}

//visualizzazione della grid dei parametri
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "parametri";
$oGrid->title = "Gestione parametri indicatori";
$oGrid->resources[] = "indicatore-parametro";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "indicatori_parametro");
$oGrid->order_default = "nome";
$oGrid->record_id = "parametro-modify";
//costruzione dell'url del record (viene selelezionata la directory corrente con substr (path - ultima_parte_del_path))
$path_info_parts = explode("/", $cm->path_info);	
$path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
$oGrid->record_url = FF_SITE_PATH . $path_info . "parametro_modify";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->base_type = "Text";
$oField->label = "Nome";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "tipo_parametro";
$oField->base_type = "Text";
$oField->label = "Tipo parametro";
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