<?php
$user = LoggedUser::Instance();
if (!$user->hasPrivilege("obiettivi_aziendali_edit")) {
    ffErrorHandler::raise("Errore: l'utente non ha i privilegi per poter accedere alla gestione degli obiettivi aziendali.");
}

//recupero dei parametri
$anno = $cm->oPage->globals["anno"]["value"];
$date = $cm->oPage->globals["data_riferimento"]["value"];

//popolamento della grid tramite array		
$grid_fields = array(
    "ID",
    "codice",
    "titolo",
    "indicatori",
    "origine",
    "tipo",
    "area_risultato",
    "area",
    "cdr_assegnazione",
);
$grid_recordset = array();
//vengono estratti tutti gli obiettivi dell'anno
foreach (ObiettiviObiettivo::getAll(array("ID_anno_budget" => $anno->id)) as $obiettivo) {
    if ($obiettivo->data_eliminazione == null) {
        //vengono recuperate le descrizioni delle entità collegate
        $origine = new ObiettiviOrigine($obiettivo->id_origine);
        $tipo = new ObiettiviTipo($obiettivo->id_tipo);
        $area_risultato = new ObiettiviAreaRisultato($obiettivo->id_area_risultato);
        $area = new ObiettiviArea($obiettivo->id_area);
        $obiettivi_cdr_associati = "";
        foreach ($obiettivo->getObiettivoCdrAssociati() as $obiettivo_cdr_associato) {
            if ($obiettivo_cdr_associato->data_eliminazione == null && $obiettivo_cdr_associato->isObiettivoCdrAziendale()) {
                try {
                    $cdr = AnagraficaCdrObiettivi::factoryFromCodice($obiettivo_cdr_associato->codice_cdr, $date);
                    $cdr_desc = $cdr->codice . " - " . $cdr->descrizione;
                } catch (Exception $ex) {
                    $cdr_desc = $obiettivo_cdr_associato->codice_cdr . " (codice cdr non valido / obsoleto)";
                }
                $obiettivi_cdr_associati .= $cdr_desc . "\r\n";
            }
        }
        $grid_recordset[] = array(
            $obiettivo->id,
            $obiettivo->codice,
            $obiettivo->titolo,
            CoreHelper::cutText($obiettivo->indicatori, 30),
            $origine->descrizione,
            $tipo->descrizione,
            $area_risultato->descrizione,
            $area->descrizione,
            $obiettivi_cdr_associati,
        );
    }
}
//visualizzazione link estrazioni dati
$cm->oPage->addContent("<a class='estrazione link_estrazione' href='estrazioni\obiettivi_cdr_azienda.php?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST) . "'>Estrazione obiettivi-cdr .xls</a>");
$cm->oPage->addContent("<a class='estrazione link_estrazione' href='estrazioni\obiettivi_cdr_personale.php?" . $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST) . "'>Estrazione obiettivi-cdr-personale .xls</a>");

//visualizzazione della grid (nel caso in cui ci siano obiettivi per l'anno)
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "obiettivi";
$oGrid->title = "Obiettivi aziendali anno " . $anno->descrizione;
$oGrid->resources[] = "obiettivo";
$oGrid->source_SQL = CoreHelper::GetGridSqlFromArray($grid_fields, $grid_recordset, "obiettivi_obiettivo");
$oGrid->order_default = "codice";
$oGrid->record_id = "obiettivo-aziendale-modify";
$oGrid->order_method = "labels";
//costruzione dell'url del record (viene selelezionata la directory corrente con substr (path - ultima_parte_del_path))
$path_info_parts = explode("/", $cm->path_info);
$path_info = substr($cm->path_info, 0, (-1 * strlen(end($path_info_parts))));
$oGrid->record_url = FF_SITE_PATH . $path_info . "obiettivo_aziendale_modify";
$oGrid->display_navigator = false;
$oGrid->use_paging = false;

$oGrid->addEvent("on_before_parse_row", "initGrid");

// *********** FIELDS ****************
$oField = ffField::factory($cm->oPage);
$oField->id = "ID_obiettivo";
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
$oField->id = "indicatori";
$oField->base_type = "Text";
$oField->label = "Indicatori";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "origine";
$oField->base_type = "Text";
$oField->label = "Origine";
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
$oField->id = "cdr_assegnazione";
$oField->base_type = "Text";
$oField->label = "Cdr di assegnazione";
$oGrid->addContent($oField);

// *********** ADDING TO PAGE ****************
$cm->oPage->addContent($oGrid);

//inizializzazione grid
function initGrid($oGrid) {
    $cm = cm::getInstance();    
    $obiettivo = new ObiettiviObiettivo($oGrid->key_fields["ID_obiettivo"]->value->getValue());
    $tipo_obiettivo = new ObiettiviTipo($obiettivo->id_tipo);
    if ($tipo_obiettivo->class !== null) {
        $class = "row_obiettivo_cdr_".$obiettivo->id;
        $oGrid->row_class = $class;
        $cm->oPage->addContent("<script>$('.".$class."').css('background-color','#".$tipo_obiettivo->class."');</script>");
    } else {
        $oGrid->row_class = "";
    }
}
