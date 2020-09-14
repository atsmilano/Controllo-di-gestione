<?php
$globals = ffGlobals::getInstance("mod_security");

if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && !MOD_SEC_PROFILING_MAINDB)
	$db = mod_security_get_db_by_domain();
else
	$db = mod_security_get_main_db();

$obj = ffRecord::factory($cm->oPage);
$obj->id = "cm-mod-security-profiles-modify";
$obj->title = "Profili Utenze";
$obj->resources[] = "cm_mod_security_profiles";
$obj->src_table = "cm_mod_security_profiles";
if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && mod_security_get_domain())
	$obj->insert_additional_fields["ID_domains"] = new ffData(mod_security_get_domain(), "Number");

$obj->insert_additional_fields["created_user"] = new ffData(get_session("UserNID"));
$obj->insert_additional_fields["created_time"] = new ffData(date("d/m/Y H:i:s"), "DateTime", "ITA");
$obj->update_additional_fields["modified_user"] = new ffData(get_session("UserNID"));
$obj->update_additional_fields["modified_time"] = new ffData(date("d/m/Y H:i:s"), "DateTime", "ITA");
$obj->db = array(&$db);

if (isset($_REQUEST["keys"]["ID"]))
{
	mod_sec_profiling_update_profiles();
	
	$ret = $obj->db[0]->lookup($obj->src_table, "ID", $_REQUEST["keys"]["ID"], null, array("special" => "Text", "acl" => "Text", "ID_domains" => "Number"), null, true);
	if (
			strlen($ret["special"]) ||
			(MOD_SEC_MULTIDOMAIN && !MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && mod_security_get_domain() && mod_security_get_domain() != $ret["ID_domains"])
		)
	{
		$obj->allow_delete = false;
		$obj->allow_update = false;
	}
	
	if (!mod_sec_check_acl($ret["acl"]))
		access_denied();
}

$field = ffField::factory($cm->oPage);
$field->id = "ID";
$field->base_type = "Number";
$obj->addKeyField($field);

$field = ffField::factory($cm->oPage);
$field->id = "nome";
$field->label = "Nome";
$field->required = true;
$obj->addContent($field);

$field = ffField::factory($cm->oPage);
$field->id = "enabled";
$field->label = "Abilitato";
$field->extended_type = "Boolean";
$field->checked_value = new ffData(1);
$field->unchecked_value = new ffData(0);
$field->default_value = new ffData(1);
$obj->addContent($field);

if (isset($ret["special"]) && strlen($ret["special"]))
{
	$field = ffField::factory($cm->oPage);
	$field->id = "acl";
	$field->label = "ACL";
	$field->store_in_db = false;
	$field->control_type = "label";
	$field->data_type = "";
	$field->value = new ffData(mod_sec_get_acl_desc($ret["acl"]));
	$field->default_value = new ffData(mod_sec_get_acl_desc($ret["acl"]));
	$obj->addContent($field);
}

if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && !mod_security_get_domain())
{
	$field = ffField::factory($cm->oPage);
	$field->id = "ID_domains";
	$field->base_type = "Number";
	$field->extended_type = "Selection";
	$field->source_SQL = "SELECT `ID`, `nome` FROM `cm_mod_security_domains` ORDER BY UCASE(`nome`)";
	$field->label = "Dominio";
	$field->multi_select_one_label = "Tutti";
	$obj->addContent($field);
}

$cm->oPage->addContent($obj);

// -----------------------------------------------------------------------------------------------------

$oDetail = ffDetails::factory($cm->oPage, null, null, array("name" => "ffDetails_horiz"));
$oDetail->id = "cm-mod-security-profiles-pairs";
$oDetail->title = "Privilegi";
$oDetail->src_table = "cm_mod_security_profiles_pairs";
$oDetail->fields_relationship = array("ID_profile" => "ID");
$oDetail->order_default = "ID";
$oDetail->display_new = false;
$oDetail->display_delete = false;
$oDetail->db = array(&$db);

$globals->recordset = array();

if (MOD_SEC_PROFILING_SKIPSYSTEM !== "*")
{
	$menu = $cm->modules["restricted"]["menu"];
	//echo "<pre>"; print_r($menu); exit;

	$lastpath = "";
	$level = 0;
	$record = array();

	if (strlen(MOD_SEC_PROFILING_SKIPSYSTEM))
	{
		$toskip_system = explode(",", MOD_SEC_PROFILING_SKIPSYSTEM);
	}
	
	foreach ($menu as $key => $value)
	{
		$ret = process_element($key, $value, $lastpath, $toskip_system);
		if ($ret === null)
			continue;
		
		$globals->recordset[] = $ret;
		$lastpath = $ret["path"]->getValue();
		
		if (ffArrIsset($value, "elements") && count($value["elements"]))
		{
			foreach ($value["elements"] as $subkey => $subvalue)
			{
				$ret = process_element($subkey, $subvalue, $lastpath, $toskip_system);
				if ($ret === null)
					continue;

				$globals->recordset[] = $ret;
				$lastpath = $ret["path"]->getValue();
			}
		}
	}
	reset($menu);
}

function process_element($key, $value, $lastpath, $toskip_system)
{
	$record = array();

	$path = $value["path"];

	if (
			$value["hide"] 
			|| $value["is_heading"] 
			|| $value["profiling_skip"]
			|| (isset($value["profiling_acl"]) && !mod_sec_check_acl($value["profiling_acl"]))
		)
		return null;

	$level = count(explode("/", $path)) - 2;

	if ($lastpath == $path)
	{
		$level++;
		$path .= "/";
	}

	if (strlen(MOD_SEC_PROFILING_SKIPSYSTEM))
	{
		if (in_array($path, $toskip_system))
			return null;
	}

	$record["path"] = new ffData($path);
	$record["label"] = "";
	for ($i = 0; $i < $level; $i++)
	{
		$record["label"] .= MOD_SEC_PROFILING_INDENTSTRING;
	}
	$record["label"] .= " " . (strlen($value["label"]) ? $value["label"] : "[" . $key . "]");

	if ($value["acl"])
	{
		$acl = "";
		foreach ($value["acl"] as $acl_key => $acl_val)
		{
			if ($acl_val == 1)
				$acl .= "Utente";
			if ($acl_val == 2)
			{
				if (strlen($acl)) $acl .= ", ";
				$acl .= "Admin";
			}
			if ($acl_val == 3)
			{
				if (strlen($acl)) $acl .= ", ";
				$acl .= "Super";
			}
		}
		/*if (!strlen($acl))
			$acl = "Solo Pubblico";*/

		if (strlen($acl))
			$record["label"] .= " (ACL: " . $acl . ")";
	}

	$record["label"] = new ffData($record["label"]);

	if ($_REQUEST["keys"]["ID"])
	{
		$db = ffDB_Sql::factory();
		$record["ID_profile"] = new ffData($_REQUEST["keys"]["ID"], "Number");
		$sSQL = "SELECT * FROM cm_mod_security_profiles_pairs WHERE ID_profile = " . $db->toSql($_REQUEST["keys"]["ID"]) . " AND path = " . $db->toSql($path) . " ";
		$db->query($sSQL);
		if ($db->nextRecord())
		{
			$record["ID_detail"]		= $db->getField("ID", "Number");
			$record["view_own"]			= $db->getField("view_own");
			$record["view_others"]		= $db->getField("view_others");
			$record["modify_own"]		= $db->getField("modify_own");
			$record["modify_others"]	= $db->getField("modify_others");
		}
	}

	return $record;
}

if (isset($cm->modules["security"]["profiling"]["paths"]))
{
	foreach ($cm->modules["security"]["profiling"]["paths"] as $key => $value)
	{
		mod_sec_profiling_element_add(
			$value["path"]
			, $value["label"]
			, $_REQUEST["keys"]["ID"]
			, $value["indent"]
			, $value["acl"]
		);
	}
}

$res = $cm->modules["security"]["events"]->doEvent("profiling_populate", array($_REQUEST["keys"]["ID"]));
/*$last_res = end($res);
if (!$last_res)
{
}*/

$oDetail->auto_populate_insert = true;
$oDetail->populate_insert_array = $globals->recordset;
$oDetail->auto_populate_edit = true;
$oDetail->populate_edit_array = $globals->recordset;

//echo "<pre>"; print_r($globals->recordset); die();
//ffErrorHandler::raise("asd", E_USER_ERROR, null, get_defined_vars());


$field = ffField::factory($cm->oPage);
$field->id = "ID_detail";
$field->data_source = "ID";
$field->base_type = "Number";
$oDetail->addKeyField($field);

$field = ffField::factory($cm->oPage);
$field->id = "path";
$oDetail->addHiddenField($field);

$field = ffField::factory($cm->oPage);
$field->id = "label";
$field->label = "Menù";
$field->control_type = "label";
$oDetail->addContent($field);

if (!MOD_SEC_PROFILING_EXTENDED)
{
	$field = ffField::factory($cm->oPage);
	$field->id = "view_own";
	$field->label = "Abilita";
	$field->extended_type = "Boolean";
	$field->checked_value = new ffData(1);
	$field->unchecked_value = new ffData(0);
	$oDetail->addContent($field);
}
else
{
	$field = ffField::factory($cm->oPage);
	$field->id = "view_own";
	$field->label = "Vedi Propri";
	$field->extended_type = "Boolean";
	$field->checked_value = new ffData(1);
	$field->unchecked_value = new ffData(0);
	$oDetail->addContent($field);

	$field = ffField::factory($cm->oPage);
	$field->id = "modify_own";
	$field->label = "Modifica propri";
	$field->extended_type = "Boolean";
	$field->checked_value = new ffData(1);
	$field->unchecked_value = new ffData(0);
	$oDetail->addContent($field);
	
	if (MOD_SEC_PROFILING_ADDITIONAL_PRIVS)
	{
		$field = ffField::factory($cm->oPage);
		$field->id = "insert_own";
		$field->label = "Aggiungi propri";
		$field->extended_type = "Boolean";
		$field->checked_value = new ffData(1);
		$field->unchecked_value = new ffData(0);
		$oDetail->addContent($field);

		$field = ffField::factory($cm->oPage);
		$field->id = "delete_own";
		$field->label = "Elimina propri";
		$field->extended_type = "Boolean";
		$field->checked_value = new ffData(1);
		$field->unchecked_value = new ffData(0);
		$oDetail->addContent($field);
	}

	$field = ffField::factory($cm->oPage);
	$field->id = "view_others";
	$field->label = "Vedi altrui";
	$field->extended_type = "Boolean";
	$field->checked_value = new ffData(1);
	$field->unchecked_value = new ffData(0);
	$oDetail->addContent($field);

	$field = ffField::factory($cm->oPage);
	$field->id = "modify_others";
	$field->label = "Modifica altrui";
	$field->extended_type = "Boolean";
	$field->checked_value = new ffData(1);
	$field->unchecked_value = new ffData(0);
	$oDetail->addContent($field);
	
	if (MOD_SEC_PROFILING_ADDITIONAL_PRIVS)
	{
		$field = ffField::factory($cm->oPage);
		$field->id = "insert_others";
		$field->label = "Aggiungi altrui";
		$field->extended_type = "Boolean";
		$field->checked_value = new ffData(1);
		$field->unchecked_value = new ffData(0);
		$oDetail->addContent($field);

		$field = ffField::factory($cm->oPage);
		$field->id = "delete_others";
		$field->label = "Elimina altrui";
		$field->extended_type = "Boolean";
		$field->checked_value = new ffData(1);
		$field->unchecked_value = new ffData(0);
		$oDetail->addContent($field);
	}
}

$cm->oPage->addContent($oDetail);
$obj->addContent($oDetail);

$script = <<<EOD

	jQuery(function() {
		jQuery("#cm-mod-security-profiles-pairs_discl_sect table:first tr").each(function(index, obj) {
			if (index > 0) {
				jQuery(obj).bind("click", function() {ff.ffDetails.turnToggleRow("cm-mod-security-profiles-pairs", index - 1)});	
			}
		});

		jQuery("#cm-mod-security-profiles-pairs_discl_sect table:first th:first").bind("click", function() {ff.ffDetails.turnToggleAll("cm-mod-security-profiles-pairs")});	
		jQuery("#cm-mod-security-profiles-pairs_discl_sect table:first th:eq(1)").bind("click", function() {ff.ffDetails.turnToggle("cm-mod-security-profiles-pairs", "view_own")});	
EOD;

if (MOD_SEC_PROFILING_EXTENDED && !MOD_SEC_PROFILING_ADDITIONAL_PRIVS)
$script .= <<<EOD
		jQuery("#cm-mod-security-profiles-pairs_discl_sect table:first th:eq(2)").bind("click", function() {ff.ffDetails.turnToggle("cm-mod-security-profiles-pairs", "modify_own")});	
		jQuery("#cm-mod-security-profiles-pairs_discl_sect table:first th:eq(3)").bind("click", function() {ff.ffDetails.turnToggle("cm-mod-security-profiles-pairs", "view_others")});	
		jQuery("#cm-mod-security-profiles-pairs_discl_sect table:first th:eq(4)").bind("click", function() {ff.ffDetails.turnToggle("cm-mod-security-profiles-pairs", "modify_others")});	
EOD;

if (MOD_SEC_PROFILING_EXTENDED && MOD_SEC_PROFILING_ADDITIONAL_PRIVS)
$script .= <<<EOD
		jQuery("#cm-mod-security-profiles-pairs_discl_sect table:first th:eq(2)").bind("click", function() {ff.ffDetails.turnToggle("cm-mod-security-profiles-pairs", "modify_own")});	
		jQuery("#cm-mod-security-profiles-pairs_discl_sect table:first th:eq(3)").bind("click", function() {ff.ffDetails.turnToggle("cm-mod-security-profiles-pairs", "insert_own")});	
		jQuery("#cm-mod-security-profiles-pairs_discl_sect table:first th:eq(4)").bind("click", function() {ff.ffDetails.turnToggle("cm-mod-security-profiles-pairs", "delete_own")});	
		jQuery("#cm-mod-security-profiles-pairs_discl_sect table:first th:eq(5)").bind("click", function() {ff.ffDetails.turnToggle("cm-mod-security-profiles-pairs", "view_others")});	
		jQuery("#cm-mod-security-profiles-pairs_discl_sect table:first th:eq(6)").bind("click", function() {ff.ffDetails.turnToggle("cm-mod-security-profiles-pairs", "modify_others")});	
		jQuery("#cm-mod-security-profiles-pairs_discl_sect table:first th:eq(7)").bind("click", function() {ff.ffDetails.turnToggle("cm-mod-security-profiles-pairs", "insert_others")});	
		jQuery("#cm-mod-security-profiles-pairs_discl_sect table:first th:eq(8)").bind("click", function() {ff.ffDetails.turnToggle("cm-mod-security-profiles-pairs", "delete_others")});	
EOD;

$script .= <<<EOD
	});
EOD;

$cm->oPage->tplAddJs("profiling", array(
	"embed" => $script, 
	"priority" => cm::LAYOUT_PRIORITY_LOW
));

