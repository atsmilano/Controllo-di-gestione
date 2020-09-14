<?php
$db = mod_security_get_main_db();

$obj = ffRecord::factory($cm->oPage);
$obj->id = "oauth-clients-modify";
$obj->title = "Applications";
$obj->resources[] = "oauth_clients";
$obj->src_table = "oauth_clients";
$obj->addEvent("on_loaded_data", function ($obj) {
	switch ($obj->form_fields["ID_grant_types"]->value->getValue())
	{
		case 1: //WebAuth
			break;
			
		case 2: //ClientAuth
			array_map(function ($field) use ($obj) {
				unset($obj->form_fields[$field]);
				unset($obj->contents[$field]);
			}, array("redirect_uri", "disable_csrf", "sso"));
			break;
		
		case 3: //UserAuth
			array_map(function ($field) use ($obj) {
				unset($obj->form_fields[$field]);
				unset($obj->contents[$field]);
			}, array("redirect_uri", "disable_csrf", "sso"/*, "client_secret"*/));
			//unset($obj->insert_additional_fields["client_secret"]);
			break;
		
		case 4: //MobileAuth
			array_map(function ($field) use ($obj) {
				unset($obj->form_fields[$field]);
				unset($obj->contents[$field]);
			}, array("sso", "client_secret"));
			unset($obj->insert_additional_fields["client_secret"]);
			break;
	}
});

$obj->insert_additional_fields["client_id"] = new ffData(md5(uniqid(APPID, true)));
$obj->insert_additional_fields["client_secret"] = new ffData(sha1(uniqid(APPID, true)));

$obj->addEvent("on_do_action", function ($obj, $action) {
	if ($action == "insert")
	{
		$db = mod_security_get_main_db();
		$sSQL = "SELECT * FROM `oauth_clients` WHERE `client_id` = " . $db->toSql($obj->insert_additional_fields["client_id"]);
		$db->query($sSQL);
		while (true)
		{
			if ($db->numRows())
				$obj->insert_additional_fields["client_id"] = new ffData(md5(uniqid(APPID, true)));
			else
				break;
		}
	}
});

$obj->addEvent("on_done_action", function ($obj, $action) {
	if ($action == "update")
	{
		if (
				$obj->form_fields["scope"]->value->getValue() !== $obj->form_fields["scope"]->value_ori->getValue()
				|| (isset($obj->form_fields["redirect_uri"]) && $obj->form_fields["redirect_uri"]->value->getValue() !== $obj->form_fields["redirect_uri"]->value_ori->getValue())
			)
		{
			$db = ffDB_Sql::factory();
			$sSQL = "DELETE FROM `oauth_rel_users` WHERE `client_id` = " . $db->toSql($obj->key_fields["client_id"]->value);
			$db->execute($sSQL);
		}
	}
	else if ($action == "confirmdelete")
	{
		$db = ffDB_Sql::factory();
		$sSQL = "DELETE FROM `oauth_rel_users` WHERE `client_id` = " . $db->toSql($obj->key_fields["client_id"]->value);
		$db->execute($sSQL);
	}
	else if ($action == "insert")
	{
		cm::getInstance()->json_response["refresh"] = true;
		cm::getInstance()->json_response["resources"] = $obj->resources;
		$obj->redirect(ffCommon_url_add_param($_SERVER["REQUEST_URI"], "keys[client_id]", $obj->insert_additional_fields["client_id"]));
	}
});

$cm->oPage->addContent($obj);

$field = ffField::factory($cm->oPage);
$field->id = "client_id";
$obj->addKeyField($field);

$field = ffField::factory($cm->oPage);
$field->id = "ID_grant_types";
$field->base_type = "Number";
$field->label = "Access Type";
$field->extended_type = "Selection";
$field->extended_type = "Selection";
$field->source_SQL = "SELECT `ID`, `name` FROM `oauth_grant_types` ORDER BY `ID`";
$field->multi_select_one = false;
if (isset($_REQUEST["keys"]["client_id"]))
{
	$field->control_type = "label";
	$field->store_in_db = false;
}
else
{
	$field->required = true;
	if ($cm->oPage->isXHR())
		$field->properties["onchange"] = "ff.ajax.ctxDoAction('" . $cm->oPage->getXHRCtx() . "', '', '" . $obj->id  . "_');";
	else
		$field->properties["onchange"] = "jQuery('#frmAction').val('" . $obj->id  . "_'); jQuery('#frmMain').submit()";
}
$obj->addContent($field);

$field = ffField::factory($cm->oPage);
$field->id = "description";
$field->label = "Description";
$field->required = true;
$obj->addContent($field);

if (isset($_REQUEST["keys"]["client_id"]))
{
	$field = ffField::factory($cm->oPage);
	$field->id = "client_id";
	$field->label = "Client ID";
	$field->control_type = "label";
	$field->store_in_db = false;
	$obj->addContent($field);

	$field = ffField::factory($cm->oPage);
	$field->id = "client_secret";
	$field->label = "Secret";
	$field->control_type = "label";
	//$field->default_value = new ffData(sha1(uniqid(time(), true)));
	$field->store_in_db = false;
	$field->fixed_post_content .= "<a href='javascript:void(0);' onclick='ff.modules.security.oauth2.refreshSecret(\"" . rawurlencode($_REQUEST["keys"]["client_id"]) . "\")'>" . ffTheme_restricted_icon("NewIcon_refresh") . "</a>"; 
	$field->fixed_post_content .= "&nbsp;<a href='javascript:void(0);' onclick='ff.modules.security.oauth2.emptySecret(\"" . rawurlencode($_REQUEST["keys"]["client_id"]) . "\")'>" . ffTheme_restricted_icon("NewIcon_delete") . "</a>";
	$obj->addContent($field);
}

$field = ffField::factory($cm->oPage);
$field->id = "url_site";
$field->label = "Website URL";
$obj->addContent($field);

$field = ffField::factory($cm->oPage);
$field->id = "url_privacy";
$field->label = "Privacy URL";
$obj->addContent($field);

$field = ffField::factory($cm->oPage);
$field->id = "redirect_uri";
$field->label = "API Callback URL";
$field->required = true;
//$field->addValidator("url");
$field->description = "Changing this will cause reset of granted users";
$obj->addContent($field);

$field = ffField::factory($cm->oPage);
$field->id = "disable_csrf";
$field->base_type = "Number";
$field->label = "Disable CSRF Protection";
$field->extended_type = "Boolean";
$field->unchecked_value = new ffData(0, "Number");
$field->checked_value = new ffData(1, "Number");
$obj->addContent($field);

$field = ffField::factory($cm->oPage);
$field->id = "scope";
$field->label = "Scope";
//$field->required = true;
$field->extended_type = "Selection";
$field->source_SQL = "SELECT `scope`, `scope` FROM `oauth_scopes`";
//$field->source_SQL = "SELECT `scope`, CONCAT(`scope`, ' - ', `description`) FROM `oauth_scopes`";
$field->widget = "checkgroup";
$field->description = "Changing this will cause reset of granted users";
$field->grouping_separator = " ";
$field->default_value = $db->lookup("SELECT GROUP_CONCAT(`scope` SEPARATOR ' ') FROM `oauth_scopes` WHERE `is_default` = 1", null, null, new ffData(), null, null, false);
$obj->addContent($field);

$field = ffField::factory($cm->oPage);
$field->id = "sso";
$field->base_type = "Number";
$field->label = "Part of SSO Network";
$field->extended_type = "Boolean";
$field->unchecked_value = new ffData(0, "Number");
$field->checked_value = new ffData(1, "Number");
$obj->addContent($field);

/*$field = ffField::factory($cm->oPage);
$field->id = "json_only";
$field->base_type = "Number";
$field->label = "Only JSON";
$field->extended_type = "Boolean";
$field->unchecked_value = new ffData(0, "Number");
$field->checked_value = new ffData(1, "Number");
$obj->addContent($field);*/
