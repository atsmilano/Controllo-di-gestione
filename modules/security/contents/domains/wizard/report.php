<?php
$filename = cm_cascadeFindTemplate("/contents/domains/wizard/report.html", "security");
//$filename = cm_moduleCascadeFindTemplateByPath("security", "/contents/domains/wizard/report.html", $cm->oPage->getTheme());
$tpl = ffTemplate::factory(ffCommon_dirname($filename));
$tpl->load_file("report.html", "main");
$cm->oPage->addContent($tpl, null, "testo");

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "ModSecDomains";
$oRecord->title = "Riepilogo Creazione Nuovo Dominio";
$oRecord->src_table = CM_TABLE_PREFIX . "mod_security_domains";
$oRecord->display_values = true;
$oRecord->buttons_options["cancel"]["display"] = false;
$oRecord->buttons_options["update"]["display"] = false;
$oRecord->buttons_options["delete"]["display"] = false;


$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->label = "Name";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "company_name";
$oField->label = "Company";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = "Status";
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
								  array( new ffData("0"),  new ffData("Disabled"))
								, array( new ffData("1"),  new ffData("Enabled"))
							);
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "creation_date";
$oField->label = "Creazione";
$oField->base_type = "Date";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "expiration_date";
$oField->label = "Scadenza";
$oField->base_type = "Date";
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

if (MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
{
	$oRecord->addContent(null, true, "db");
	$oRecord->groups["db"]["title"] = "Database";

	$oField = ffField::factory($cm->oPage);
	$oField->id = "db_host";
	$oField->label = "Host";
	$oRecord->addContent($oField, "db");

	$oField = ffField::factory($cm->oPage);
	$oField->id = "db_name";
	$oField->label = "Name";
	$oRecord->addContent($oField, "db");

}

$cm->oPage->addContent($oRecord);

$oBt = ffButton::factory($cm->oPage);
$oBt->id = "customize";
$oBt->label = "Personalizza";
$oBt->action_type = "gotourl";
$oBt->url = "../modify?keys[ID]=" . $_REQUEST["keys"]["ID"] . "&ret_url=" . rawurlencode($_REQUEST["ret_url"]);
$oBt->aspect = "link";
$oRecord->addActionButton($oBt);

$oBt = ffButton::factory($cm->oPage);
$oBt->id = "indietro";
$oBt->label = "Fine";
$oBt->action_type = "gotourl";
$oBt->url = $_REQUEST["ret_url"];
$oBt->aspect = "link";
$oRecord->addActionButton($oBt);

$oRecord->addActionButton($oBt);
