<?php   
$sezioni = CmsHomeSezione::getAll(
    array(), 
    [
        ["fieldname" => "anno_inizio", "direction" => "ASC"],
        ["fieldname" => "ordinamento", "direction" => "ASC"]
    ]
);

//recupero delle sezioni
$grid_fields = array(
    "ID",
    "ordinamento",
    "tipo",
    "testo",
    "anno_inizio",
    "anno_fine",
);
$grid_recordset = array();
foreach ($sezioni as $sezione) {
    $testo = strip_tags(html_entity_decode($sezione->testo, ENT_QUOTES, 'UTF-8'));

    if ($sezione->isAllegato()) {
        $allegati = CmsHomeSezioneAllegato::getAll(["ID_sezione" => $sezione->id]);
        
        $testo .= " (allegato: ";
        if (empty($allegati)) {
            $testo .= "Nessun allegato";
        }
        else {
            $txt = null;
            foreach ($allegati as $allegato) {
                if (strlen($txt) > 0) {
                    $txt .= "\n";
                }
                
                $txt .= $allegato->filename_plain;
            }
            $testo .= $txt;
        }
        $testo .= ")";
    }
    
    $grid_recordset[] = array(                                
        $sezione->id,
        $sezione->ordinamento,
        $sezione->getTipoDescrizione(),
        $testo,
        $sezione->anno_inizio,
        $sezione->anno_fine,
    );
}

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "sezioni";
$oGrid->title = "Sezioni homepage";
$oGrid->resources[] = "sezioni-home";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "cms_home_sezione");
$oGrid->order_default = "ordinamento";
$oGrid->record_id = "sezioni-home-modify";
$oGrid->record_url = FF_SITE_PATH . $cm->path_info . "/dettaglio_sezione";
$oGrid->order_method = "labels";
$oGrid->full_ajax = true;
$oGrid->display_new = true;
$oGrid->display_edit_url = true;
$oGrid->display_delete_bt = true;
$oGrid->fixed_post_content = '<script>jQuery("#'.$oGrid->id.'").jTableFullClick();</script>';

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ordinamento";
$oField->base_type = "Number";
$oField->label = "Ordinamento";		
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "tipo";
$oField->base_type = "Text";
$oField->label = "Tipo";		
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "testo";
$oField->base_type = "Text";
$oField->extended_type = "HTML";
$oField->label = "Contenuto";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_inizio";
$oField->base_type = "Number";
$oField->label = "Anno inizio disponibilità";		
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_fine";
$oField->base_type = "Number";		
$oField->label = "Anno fine disponibilità";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);