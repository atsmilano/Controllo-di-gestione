<?php
$obj = ffGrid::factory($cm->oPage);
$obj->id = "oauth-clients";
$obj->title = "Applications";
$obj->resources[] = "oauth_clients";
$obj->source_SQL = "SELECT
								`oauth_clients`.*
							FROM
								`oauth_clients`
							[WHERE]
							[HAVING]
							[ORDER]
		";
$obj->record_id = "oauth-clients-modify";
$obj->record_url = FF_SITE_PATH . $cm->path_info . "/modify";
$obj->order_default = "client_id";

$field = ffField::factory($cm->oPage);
$field->id = "client_id";
$obj->addKeyField($field);

$field = ffField::factory($cm->oPage);
$field->id = "description";
$field->label = "Description";
$obj->addContent($field);

$field = ffField::factory($cm->oPage);
$field->id = "client_id";
$field->label = "Client ID";
$obj->addContent($field);

$field = ffField::factory($cm->oPage);
$field->id = "ID_grant_types";
$field->base_type = "Number";
$field->label = "Access Type";
$field->extended_type = "Selection";
$field->source_SQL = "SELECT `ID`, `name` FROM `oauth_grant_types` ORDER BY `ID`";
$obj->addContent($field);

$field = ffField::factory($cm->oPage);
$field->id = "scope";
$field->label = "Scope";
$obj->addContent($field);

$field = ffField::factory($cm->oPage);
$field->id = "sso";
$field->label = "SSO Network";
$field->base_type = "Number";
$field->extended_type = "Selection";
$field->multi_pairs = array(
	array(new ffData(1, "Number"), new ffData("Yes"))
);
$field->multi_select_one_label = "";
$obj->addContent($field);

/*$field = ffField::factory($cm->oPage);
$field->id = "json_only";
$field->label = "JSON";
$field->base_type = "Number";
$field->extended_type = "Selection";
$field->multi_pairs = array(
	array(new ffData(1, "Number"), new ffData("Yes"))
);
$field->multi_select_one_label = "";
$obj->addContent($field);*/

$field = ffField::factory($cm->oPage);
$field->id = "url_site";
$field->label = "Website";
$obj->addContent($field);

$cm->oPage->addContent($obj);
