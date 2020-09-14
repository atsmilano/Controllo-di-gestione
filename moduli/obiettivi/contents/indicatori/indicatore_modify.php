<?php
$user = LoggedUser::Instance();
//verifica privilegi utente
if (!$user->hasPrivilege("indicatori_edit")) {
	ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione degli indicatori.");	
}

//recupero dei parametri
//anno***********
$anno = $cm->oPage->globals["anno"]["value"];

//definizione del record
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "indicatore-modify";
$oRecord->title = $ind_desc;
$oRecord->resources[] = "indicatore";
$oRecord->src_table = "indicatori_indicatore";

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_indicatore";
$oField->data_source = "ID";
$oField->base_type = "Number";
$oRecord->addkeyfield($oField);

$oRecord->addContent(null, true, "generale");
$oRecord->groups["generale"]["title"] = "Informazioni indicatore";

$oField = ffField::factory($cm->oPage);
$oField->id = "codice";
$oField->base_type = "Text";
$oField->label = "Codice";
$oRecord->addContent($oField, "generale");

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->base_type = "Text";
$oField->label = "Nome Indicatore";
$oField->required = true;
$oRecord->addContent($oField, "generale");

$oField = ffField::factory($cm->oPage);
$oField->id = "descrizione";
$oField->base_type = "Text";		
$oField->extended_type = "Text";		
$oField->label = "Descrizione";
$oRecord->addContent($oField, "generale");

$oField = ffField::factory($cm->oPage);
$oField->id = "istruzioni";
$oField->base_type = "Text";		
$oField->extended_type = "Text";
$oField->label = "Istruzioni";
$oRecord->addContent($oField, "generale");

//detail parametri**************************************************************
$oRecord->addContent(null, true, "parametri");
$oRecord->groups["parametri"]["title"] = "Parametri e calcoli";

$tipi_parametro = array();
foreach (IndicatoriTipoParametro::getAll() AS $tipo_parametro){    
    $tipi_parametro[$tipo_parametro->id] = $tipo_parametro->nome;
}
$no_param = false;
if (count ($tipi_parametro)) {    
    $parametri_select = array();
    foreach (IndicatoriParametro::getAttiviAnno($anno) AS $parametro) {    
        $parametri_select[] = array(
                            new ffData ($parametro->id, "Number"),
                            new ffData ($parametro->nome . ' (' . $tipi_parametro[$parametro->id_tipo_parametro] . ')', "Text")
                            );
    }        
    if (count($parametri_select)) {
        //detail parametri
        $oDetail = ffDetails::factory($cm->oPage);
        $oDetail->id = "DeatailParametri";
        $oDetail->title = "Parametri";
        $oDetail->src_table = "indicatori_parametro_indicatore";
        //il secondo ID è il field del record
        $oDetail->fields_relationship = array("ID_indicatore" => "ID_indicatore");
        //risulta fondamentale mantenere l'ordinamento sul campo ID ASC perchè è l'ordine che viene mantenuto nella formula
        $oDetail->order_default = "ID";

        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_parametro_indicatore";
        $oField->data_source = "ID";
        $oField->base_type = "Number";
        $oDetail->addKeyField($oField);
  
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID_parametro";
        $oField->base_type = "Number";
        $oField->label = "Parametro";
        $oField->widget = "actex";
        $oField->required = true;
        $oField->actex_update_from_db = true;
        
        //costruzione percorso record
        $path_info_parts = explode("/", $cm->path_info);	
        $path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));        
        $oField->actex_dialog_url = FF_SITE_PATH . $path_info . "parametro_modify";
        $oField->resources[] = "indicatore-parametro";
        $oField->multi_pairs = $parametri_select;
        $oDetail->addContent($oField); 
                  
        $oRecord->addContent($oDetail, "parametri");
        $cm->oPage->addContent($oDetail);
    }  
    else {
        $no_param = true;
    }
}
else {
    $no_param = true;
}
if ($no_param == true) {
    $oRecord->addContent("<label>Nessun parametro / tipo_parametro attivi per l'anno corrente<br><br></label>");
}

//******************************************************************************
$oField = ffField::factory($cm->oPage);
$oField->id = "formula_calcolo_risultato";
$oField->base_type = "Text";		
$oField->label = "Formula per il calcolo del risultato";
$oRecord->addContent($oField, "parametri");

$oField = ffField::factory($cm->oPage);
$oField->id = "formula_calcolo_raggiungimento";
$oField->base_type = "Text";		
$oField->label = "Formula per il calcolo del raggiungimento";
$oRecord->addContent($oField, "parametri");

//******************************************************************************
$oRecord->addContent(null, true, "validita");
$oRecord->groups["validita"]["title"] = "Validità indicatore";

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_introduzione";
$oField->base_type = "Number";		
$oField->label = "Anno inizio validità";
$oField->required = true;
$oRecord->addContent($oField, "validita");

$oField = ffField::factory($cm->oPage);
$oField->id = "anno_termine";
$oField->base_type = "Number";		
$oField->label = "Anno fine validità";
$oRecord->addContent($oField, "validita");

//******************************************************************************
//associazione obiettivi e cdr
if (isset($_REQUEST["keys[ID_indicatore]"])) {
    $db = ffDb_Sql::factory();
    $indicatore = new IndicatoriIndicatore($_REQUEST["keys[ID_indicatore]"]);
    //********************
    //obiettivi***********
    $oRecord->addContent(null, true, "obiettivi_collegati");
    $oRecord->groups["obiettivi_collegati"]["title"] = "Obiettivi " . $anno->descrizione . " collegati all'indicatore";
        
    $grid_fields = array(
                    "ID",
                    "codice",
                    "titolo",
                    "tipo",
                    "area_risultato",
                    "area",
                    "valore_target_aziendale_obiettivo",
                );
    $grid_recordset = array();
    foreach ($indicatore->getObiettiviCollegati($anno) as $obiettivo) {
        $valore_target_aziendale = $indicatore->getValoreTargetAnno($anno);
        
        strlen($obiettivo->obiettivo_indicatore->valore_target)?
            $valore_target_indicatore_obiettivo = $obiettivo->obiettivo_indicatore->valore_target
            :$valore_target_indicatore_obiettivo = "ND";
        
        $tipo = new ObiettiviTipo($obiettivo->id_tipo);
        $area_risultato = new ObiettiviAreaRisultato($obiettivo->id_area_risultato);
        $area = new ObiettiviArea($obiettivo->id_area);
                
        $grid_recordset[] = array(
                                $obiettivo->obiettivo_indicatore->id,
                                $obiettivo->codice,
                                $obiettivo->titolo,
                                $tipo->descrizione,
                                $area_risultato->descrizione,
                                $area->descrizione,
                                $valore_target_indicatore_obiettivo,
                            );		
    }
    
   
    //visualizzazione della grid dei cdr associati all'obiettivo
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "obiettivo-indicatore";
    $oGrid->title = "Obiettivi";
    $oGrid->resources[] = "obiettivo-indicatore";
    $oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "obiettivi_obiettivo");
    $oGrid->order_default = "codice";
    $oGrid->record_id = "obiettivo-indicatore-modify";
    $oGrid->order_method = "labels";	
    $path_info_parts = explode("/", $cm->path_info);	
    $path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));
    $oGrid->record_url = FF_SITE_PATH . $path_info . "obiettivo_indicatore_modify";	
    $oGrid->use_paging = false;
    $oGrid->full_ajax = true;

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_obiettivo_indicatore";
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
    $oField->id = "titolo";
    $oField->base_type = "Text";		
    $oField->label = "Titolo";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "tipo";
    $oField->base_type = "Text";
    $oField->label = "Tipo";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "area_risultato";
    $oField->base_type = "Text";
    $oField->label = "Area Risultato";
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "area";
    $oField->base_type = "Text";
    $oField->label = "Area";
    $oGrid->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
	$oField->id = "valore_target_aziendale_obiettivo";
	$oField->base_type = "Text";		
	$oField->label = "Valore target aziendale indicatore-obiettivo " . $anno->descrizione;
	$oGrid->addContent($oField);

    $oRecord->addContent($oGrid, "obiettivi_collegati");
    $cm->oPage->addContent($oGrid);     
            
    //********************
    //valori target*******
    $oRecord->addContent(null, true, "valori_target");
    $oRecord->groups["valori_target"]["title"] = "Valori target dell'indicatore per l'anno " . $anno->descrizione;
 
    $tipo_piano_cdr = $cm->oPage->globals["tipo_piano_cdr"]["value"];
    $valori_target = $indicatore->getValoriTargetAnno($anno);
    $grid_fields = array(
                    "ID",
                    "codice_cdr",
                    "valore_target",
    );
    $grid_recordset = array();
    foreach ($valori_target as $valore_target) {        
        //recupero della descrizione del cdr
        try {            
            $piano_cdr = PianoCdr::getAttivoInData($tipo_piano_cdr, $cm->oPage->globals["data_riferimento"]["value"]->format('Y-m-d'));
            $cdr = Cdr::factoryFromCodice($valore_target->codice_cdr, $piano_cdr);
            $descrizione_cdr = $cdr->codice . " - " . $cdr->descrizione;
        } catch (Exception $ex) {
            $descrizione_cdr = "Aziendale";
        }        
        $grid_recordset[] = array(
                                $valore_target->id,
                                $descrizione_cdr,
                                $valore_target->valore_target,
                                );
    }

    //visualizzazione della grid dei cdr associati all'obiettivo
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->id = "valori-target-indicatore";
    $oGrid->title = "Valori Target (" . $tipo_piano_cdr->descrizione . ")";
    $oGrid->resources[] = "valore-target-indicatore";
    $oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "indicatori_valore_target");
    $oGrid->order_default = "codice_cdr";
    $oGrid->record_id = "valore-target-modify";
    $oGrid->order_method = "labels";	
    $path_info_parts = explode("/", $cm->path_info);	
    $path_info = substr($cm->path_info, 0, (-1*strlen(end($path_info_parts))));    
    $oGrid->record_url = FF_SITE_PATH . $path_info . "valore_target_indicatore_modify";	
    $oGrid->use_paging = false;
    $oGrid->full_ajax = true;

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_valore_target_indicatore";
    $oField->data_source = "ID";
    $oField->base_type = "Number";
    $oField->label = "id";
    $oGrid->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "codice_cdr";
    $oField->base_type = "Text";
    $oField->label = "Codice CDR";		
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "valore_target";
    $oField->base_type = "Number";		
    $oField->label = "Valore";
    $oGrid->addContent($oField);

    $oRecord->addContent($oGrid, "valori_target");
    $cm->oPage->addContent($oGrid);
}

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oRecord);