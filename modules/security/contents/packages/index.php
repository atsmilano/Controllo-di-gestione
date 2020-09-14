<?php
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "MainGrid";
$oGrid->title = "Packages";
$oGrid->source_SQL = "SELECT 
							" . CM_TABLE_PREFIX . "mod_security_packages.*
						FROM 
							" . CM_TABLE_PREFIX . "mod_security_packages
						[WHERE]
						[HAVING] 
						ORDER BY 
							`order` 
							[COLON] [ORDER]
	";

$oGrid->order_default = "name";
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "MainRecord";
$oGrid->use_order = false;

$oGrid->widget_deps[] = array(
		"name" => "dragsort"
		, "options" => array(
			  &$oGrid
			, array(
				  "resource_id" => "mod-security-packages"
				, "service_path" => FF_SITE_PATH . "/services/resources-json/mod-security"
			)
			, "ID"
		)
	);
$oGrid->widget_deps[] = array(
		"name" => "labelsort"
		, "options" => array(
			  &$oGrid
			, array(
				  "resource_id" => "mod-security-packages"
				, "service_path" => FF_SITE_PATH . "/services/resources-json/mod-security"
			)
		)
	);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = "Name";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "type";
$oField->label = "Tipo";
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
		array(new ffData(0), new ffData("Pubblico"))
		, array(new ffData(1), new ffData("Privato"))
	);
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid);
