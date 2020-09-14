<?php
$db = ffDB_Sql::factory();

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "MainRecord";
$oRecord->title = "Package";
$oRecord->src_table = CM_TABLE_PREFIX . "mod_security_packages";

$oRecord->addEvent("on_done_action", "MainRecord_on_done_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = "Name";
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "type";
$oField->label = "Tipo";
$oField->extended_type = "Selection";
$oField->required = true;
$oField->multi_pairs = array(
		array(new ffData(MOD_SECURITY_PACKAGE_PUBLIC), new ffData("Pubblico"))
		, array(new ffData(MOD_SECURITY_PACKAGE_PRIVATE), new ffData("Privato"))
	);
$oRecord->addContent($oField);

$db = ffDB_Sql::factory();
foreach ($cm->modules["security"]["packages"] as $key => $value)
{
	if ($_REQUEST["keys"]["ID"])
	{
		$sSQL = "
				SELECT
						cm_mod_security_packages_fields.*
					FROM
						cm_mod_security_packages_fields
					WHERE
						cm_mod_security_packages_fields.ID_packages = " . $db->toSql($_REQUEST["keys"]["ID"]) . "
						AND cm_mod_security_packages_fields.field = " . $db->toSql($key) . "
			";
		$db->query($sSQL);
		$found = $db->nextRecord();
	}
	
	$group = null;

	$oField = ffField::factory($cm->oPage);
	$oField->id = $key;
	$oField->label = $value["label"];
	$oField->store_in_db = false;
	$oField->data_type = "";

	switch (strtolower($value["type"]))
	{
		case "boolean":
			$oField->extended_type = "Boolean";
			$oField->checked_value = new ffData("1");
			$oField->unchecked_value = new ffData("");
			break;
		case "number":
			$oField->base_type = "Number";
			break;
		case "currency":
			$oField->base_type = "Number";
			$oField->app_type = "Currency";
			break;
		case "text":
			$oField->base_type = "Text";
			$oField->extended_type = "Text";
			$oField->properties["rows"] = "10";
			break;
	}

	if ($value["required"] == true)
		$oField->required = true;

	if (isset($value["group"]))
	{
		$group = $value["group"];
		if ((cm_getMainTheme () == "restricted" || $cm->oPage->getTheme() == "restricted") && !isset($oRecord->groups[$group]))
		{
				$oRecord->addContent(null, true, $group);
		}
		if (isset($cm->modules["security"]["packages_groups"][$group]["title"]))
			$oRecord->groups[$group]["title"] = $cm->modules["security"]["packages_groups"][$group]["title"];
	}

	$oRecord->addContent($oField, $group);

	if ($value["allow_undefined"] == "true")
	{
		$oField_c = ffField::factory($cm->oPage);
		$oField_c->id = $key . "_unlimited";
		$oField_c->label = $value["label"];
		$oField_c->store_in_db = false;
		$oField_c->data_type = "";
		$oField_c->extended_type = "Boolean";
		$oField_c->checked_value = new ffData("1");
		$oField_c->unchecked_value = new ffData("");
		$oField_c->parent_page = array($cm->oPage);

		if ($found)
		{
			$oField_c->value = $db->getField("unlimited");
		}
		
		$oField_c->manual_display = true;
		$oRecord->addContent($oField_c);
		$oField->fixed_post_content = $oField_c->process() . " " . $value["undefined_label"];
	}

	if ($found)
	{
		//$oField->value = $db->getField("value", $oField->base_type);
		$oField->default_value = $db->getField("value", $oField->base_type);
	}
}
reset ($cm->modules["security"]["packages"]);

$cm->oPage->addContent($oRecord);

function MainRecord_on_done_action($oRecord, $frmAction)
{
	$cm = cm::getInstance();
	$db = ffDB_Sql::factory();

	switch ($frmAction)
	{
		case "insert":
			foreach ($cm->modules["security"]["packages"] as $key => $value)
			{
				$sSQL = "INSERT INTO cm_mod_security_packages_fields (ID_packages, field, value, unlimited) VALUES (
						" . $db->toSql($oRecord->key_fields["ID"]->value) . "
						, " . $db->toSql($key) . "
						, " . $db->toSql($oRecord->form_fields[$key]->value, $oRecord->form_fields[$key]->base_type) . "
						, " . $db->toSql($oRecord->form_fields[$key . "_unlimited"]->value) . "
					)";
				$db->execute($sSQL);
			}
			reset ($cm->modules["security"]["packages"]);

			break;

		case "update":
			foreach ($cm->modules["security"]["packages"] as $key => $value)
			{
                            $sSQL = "SELECT ID
                                        FROM cm_mod_security_packages_fields
                                        WHERE field = " . $db->toSql($key) . "
                                            AND ID_packages = " . $db->toSql($oRecord->key_fields["ID"]->value);
                            $db->query($sSQL);
                            if($db->nextRecord()) {
				$sSQL = "UPDATE cm_mod_security_packages_fields SET 
							value = " . $db->toSql($oRecord->form_fields[$key]->value, $oRecord->form_fields[$key]->base_type) . "
							, unlimited = " . $db->toSql($oRecord->form_fields[$key . "_unlimited"]->value) . "
						WHERE
							field = " . $db->toSql($key) . "
							AND ID_packages = " . $db->toSql($oRecord->key_fields["ID"]->value) . "
					";
				$db->execute($sSQL);
                            } else
                            {
                                $sSQL = "INSERT INTO cm_mod_security_packages_fields (ID_packages, field, value, unlimited) VALUES (
                                                " . $db->toSql($oRecord->key_fields["ID"]->value) . "
                                                , " . $db->toSql($key) . "
                                                , " . $db->toSql($oRecord->form_fields[$key]->value, $oRecord->form_fields[$key]->base_type) . "
                                                , " . $db->toSql($oRecord->form_fields[$key . "_unlimited"]->value) . "
                                        )";
                                $db->execute($sSQL);
                            }
			}
			break;

		case "confirmdelete":
			break;
	}
}