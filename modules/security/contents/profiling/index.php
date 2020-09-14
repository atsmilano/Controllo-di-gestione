<?php
if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && !MOD_SEC_PROFILING_MAINDB)
	$db = mod_security_get_db_by_domain();
else
	$db = mod_security_get_main_db();

$obj = ffGrid::factory($cm->oPage);
$obj->id = "cm-mod-security-profiles";
$obj->title = "Profili Utenze";
$obj->resources[] = "cm_mod_security_profiles";
$obj->order_default = "ID";
$obj->source_DS = "mod_sec_profiles_index";
$obj->record_id = "cm-mod-security-profiles-modify";
$obj->record_url = FF_SITE_PATH . $cm->path_info . "/modify";
$obj->use_order = false;
$obj->db = array(&$db);
$obj->addEvent("on_before_parse_row", "MainGrid_on_before_parse_row");


$obj->widget_deps[] = array(
		"name" => "dragsort"
		, "options" => array(
			  &$obj
			, array(
				  "resource_id" => "mod-security-profiles"
				, "service_path" => FF_SITE_PATH . "/services/resources-json/mod-security"
			)
			, "ID"
		)
	);
$obj->widget_deps[] = array(
		"name" => "labelsort"
		, "options" => array(
			  &$obj
			, array(
				  "resource_id" => "mod-security-profiles"
				, "service_path" => FF_SITE_PATH . "/services/resources-json/mod-security"
			)
		)
	);


$field = ffField::factory($cm->oPage);
$field->id = "ID";
$field->base_type = "Number";
$obj->addKeyField($field);

$field = ffField::factory($cm->oPage);
$field->id = "nome";
$field->label = "Nome";
$obj->addContent($field);

$field = ffField::factory($cm->oPage);
$field->id = "enabled";
$field->label = "Abilitato";
$field->extended_type = "Selection";
$field->multi_pairs = array(
	array(new ffData("0"), new ffData("No"))
	, array(new ffData("1"), new ffData("Si"))
);
$obj->addContent($field);

if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && !mod_security_get_domain())
{
	$field = ffField::factory($cm->oPage);
	$field->id = "domainname";
	$field->label = "Dominio";
	$obj->addContent($field);
	
	$field = ffField::factory($cm->oPage);
	$field->id = "ID_domains";
	$field->base_type = "Number";
	$field->extended_type = "Selection";
	$field->source_SQL = "SELECT `ID`, `nome` FROM `cm_mod_security_domains` ORDER BY UCASE(`nome`)";
	$field->label = "Dominio";
	$field->src_operation = "(`cm_mod_security_profiles`.`ID_domains` = 0 OR `cm_mod_security_profiles`.`ID_domains` = [VALUE])";
	$field->multi_select_one_label = "Tutti";
	$obj->addSearchField($field);
}

$cm->oPage->addContent($obj);

function MainGrid_on_before_parse_row($oComponent)
{
	$db = $oComponent->db[0];
	if (
			strlen($db->getField("special", "Text", true)) ||
			(MOD_SEC_MULTIDOMAIN && !MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && mod_security_get_domain() && mod_security_get_domain() != $db->getField("ID_domains", "Number", true))
		)
		$oComponent->visible_delete_bt = false;
	else
		$oComponent->visible_delete_bt = true;
}
