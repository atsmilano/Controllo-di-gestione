<?php
$obj = ffGrid::factory($cm->oPage);
$obj->id = "oauth-scopes";
$obj->title = "Scopes";
$obj->resources[] = "oauth_scopes";
$obj->source_SQL = "SELECT
								`oauth_scopes`.*
								
							FROM
								`oauth_scopes`
								
							[WHERE]
							[HAVING]
							[ORDER]
		";
$obj->record_id = "oauth-scopes-modify";
$obj->record_url = FF_SITE_PATH . $cm->path_info . "/modify";
$obj->order_default = "scope";
$obj->addEvent("on_before_parse_row", function ($oComponent) {
	$db = $oComponent->db[0];
	if ($db->getField("special", "Text", true))
		$oComponent->visible_delete_bt = false;
	else
		$oComponent->visible_delete_bt = true;
});

$field = ffField::factory($cm->oPage);
$field->id = "scope";
$obj->addKeyField($field);

$field = ffField::factory($cm->oPage);
$field->id = "scope";
$field->label = "Name";
$obj->addContent($field);

$field = ffField::factory($cm->oPage);
$field->id = "is_default";
$field->label = "Default";
$field->base_type = "Number";
$field->extended_type = "Selection";
$field->multi_pairs = array(
	array(new ffData(0, "Number"), new ffData(""))
	, array(new ffData(1, "Number"), new ffData("Si"))
);
$field->multi_select_one = false;
$obj->addContent($field);

$field = ffField::factory($cm->oPage);
$field->id = "description";
$field->label = "Description";
$obj->addContent($field);

$cm->oPage->addContent($obj);
