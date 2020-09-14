<?php
$cm->oPage->addBounceComponent("Step2");
$cm->oPage->addBounceComponent("Step3");

$transit_params = $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST) . "ret_url=" . rawurlencode($_REQUEST["ret_url"]) . "&key=" . $_REQUEST["key"];

// dati generici
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "Step1";
$oRecord->title = "Step1 - Dati Generici Nuovo Dominio";
$oRecord->buttons_options["insert"]["display"] = false;
$oRecord->buttons_options["cancel"]["display"] = false;

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->label = "Name";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "company_name";
$oField->label = "Company";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = "Status";
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
								  array( new ffData("0"),  new ffData("Disabled"))
								, array( new ffData("1"),  new ffData("Enabled"))
							);
$oField->required = true;
$oField->default_value = new ffData("1");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "expiration_date";
$oField->label = "Scadenza";
$oField->base_type = "Date";
$oField->widget = "datepicker";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "time_zone";
$oField->label = "Time Zone";
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->source_SQL = "SELECT ID, name FROM " . CM_TABLE_PREFIX . "mod_security_timezones";
$oField->default_value = new ffData(16, "Number");
$oRecord->addContent($oField);

if (MOD_SEC_PACKAGES)
{
	$oRecord->addContent(null, true, "package");
	$oRecord->groups["package"]["title"] = "Package";

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_packages";
	$oField->label = "Tipo Pacchetto";
	$oField->base_type = "Number";
	$oField->extended_type = "Selection";
	$oField->source_SQL = "SELECT
									ID
									, CASE
											WHEN `type` = '0' THEN CONCAT(`name`, ' (pubblico)')
											WHEN `type` = '1' THEN CONCAT(`name`, ' (privato)')
											ELSE `name`
									END
								FROM
									" . CM_TABLE_PREFIX . "mod_security_packages
								ORDER BY
									`order`
		";
	$oRecord->addContent($oField, "package");
}

$oBt = ffButton::factory($cm->oPage);
$oBt->id = "indietro";
$oBt->label = "<< Indietro";
$oBt->action_type = "gotourl";
$oBt->url = $_REQUEST["ret_url"];
$oBt->aspect = "link";
$oRecord->addActionButton($oBt);

$oBt = ffButton::factory($cm->oPage);
$oBt->id = "StepNext";
$oBt->label = ffTemplate::_get_word_by_code("StepNext") . " >>";
$oBt->action_type = "submit";
$oBt->form_action_url = "step2?" . $transit_params;
$oBt->frmAction = "step1";
$oBt->aspect = "link";
$oRecord->addActionButton($oBt);

$cm->oPage->addContent($oRecord);
