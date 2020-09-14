<?php
$obj = ffRecord::factory($cm->oPage);
$obj->id = "oauth-scopes-modify";
$obj->title = "Scopes";
$obj->resources[] = "oauth_scopes";
$obj->src_table = "oauth_scopes";
if (isset($_REQUEST["keys"]["scope"]))
{
	$ret = $obj->db[0]->lookup($obj->src_table, "scope", $_REQUEST["keys"]["scope"], null, array("special" => "Number"), null, true);
	if ($ret["special"])
	{
		$obj->allow_delete = false;
	}
}

$field = ffField::factory($cm->oPage);
$field->id = "scope";
$obj->addKeyField($field);

$field = ffField::factory($cm->oPage);
$field->id = "scope";
$field->label = "Name";
if ($obj->allow_delete)
	$field->required = true;
else
{
	$field->control_type = "label";
	$field->store_in_db = false;
}
$obj->addContent($field);

$field = ffField::factory($cm->oPage);
$field->id = "description";
$field->extended_type = "Text";
$field->label = "Description";
$field->required = true;
$obj->addContent($field);

$field = ffField::factory($cm->oPage);
$field->id = "is_default";
$field->base_type = "Number";
$field->label = "Default";
$field->extended_type = "Boolean";
$field->unchecked_value = new ffData(0, "Number");
$field->checked_value = new ffData(1, "Number");
$obj->addContent($field);

$cm->oPage->addContent($obj);
