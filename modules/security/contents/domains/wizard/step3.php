<?php
$cm->oPage->addBounceComponent("Step1");
$cm->oPage->addBounceComponent("Step2");

$globals = ffGlobals::getInstance("mod_security");
$globals->transit_params = $cm->oPage->get_globals(GET_GLOBALS_EXCLUDE_LIST) . "ret_url=" . rawurlencode($_REQUEST["ret_url"]) . "&key=" . $_REQUEST["key"];

// TEST CONNESSIONE DB
$db = ffDB_Sql::factory();
$db->halt_on_connect_error = false;
$db->on_error = "ignore";
$rc = $db->connect($_REQUEST["Step2_db_name"], $_REQUEST["Step2_db_host"], $_REQUEST["Step2_db_user"], $_REQUEST["Step2_db_pass"]);
$db->halt_on_connect_error = true;
$db->on_error = "halt";

if ($rc !== false)
	create_domain();

$filename = cm_cascadeFindTemplate("/contents/domains/wizard/step3.html", "security");
//$filename = cm_moduleCascadeFindTemplateByPath("security", "/contents/domains/wizard/step3.html", $cm->oPage->getTheme());
$tpl = ffTemplate::factory(ffCommon_dirname($filename));
$tpl->load_file("step3.html", "main");
$cm->oPage->addContent($tpl, null, "testo");

// dati generici
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "Step3";
$oRecord->title = "Step3 - Creazione Database";
$oRecord->buttons_options["insert"]["display"] = false;
$oRecord->buttons_options["cancel"]["display"] = false;
$oRecord->addEvent("on_do_action", "Step3_on_do_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "root_username";
$oField->label = "root username";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "root_password";
$oField->label = "root password";
$oRecord->addContent($oField);

$oBt = ffButton::factory($cm->oPage);
$oBt->id = "StepPrev";
$oBt->label = "<< " . ffTemplate::_get_word_by_code("StepPrev");
$oBt->action_type = "submit";
$oBt->form_action_url = "step2?" . $globals->transit_params;
$oBt->frmAction = "step3";
$oBt->aspect = "link";
$oRecord->addActionButton($oBt);

$oBt = ffButton::factory($cm->oPage);
$oBt->id = "StepRetry";
$oBt->label = ffTemplate::_get_word_by_code("Retry");
$oBt->action_type = "submit";
$oBt->form_action_url = "step3?" . $globals->transit_params;
$oBt->frmAction = "step3";
$oBt->aspect = "link";
$oRecord->addActionButton($oBt);

$oBt = ffButton::factory($cm->oPage);
$oBt->id = "MakeDB";
$oBt->label = ffTemplate::_get_word_by_code("MakeDB");
$oBt->action_type = "submit";
$oBt->form_action_url = "step3?" . $globals->transit_params;
$oBt->frmAction = "MakeDB";
$oBt->aspect = "link";
$oRecord->addActionButton($oBt);

$cm->oPage->addContent($oRecord);

function Step3_on_do_action (ffRecord_html $oRecord, $frmAction)
{
	switch ($frmAction)
	{
		case "MakeDB":
			$db = mod_security_get_main_db();
			$link_id = @mysql_connect($_REQUEST["Step2_db_host"], $oRecord->form_fields["root_username"]->value->getValue(), $oRecord->form_fields["root_password"]->value->getValue());
			
			if ($link_id === false)
			{
				$oRecord->tplDisplayError("Username e/o Password errate");
				return;
			}

			$query_id = @mysql_query("CREATE DATABASE `" . $db->toSql($_REQUEST["Step2_db_name"], null, false) . "`", $link_id);
			$query_id = @mysql_query("CREATE USER " . $db->toSql($_REQUEST["Step2_db_user"]) . "@'%' IDENTIFIED BY " . $db->toSql($_REQUEST["Step2_db_pass"]), $link_id);
			$query_id = @mysql_query("GRANT ALL PRIVILEGES ON `" . $db->toSql($_REQUEST["Step2_db_name"], null, false) . "` . * TO " . $db->toSql($_REQUEST["Step2_db_user"]) . "@'%' WITH GRANT OPTION", $link_id);

			create_domain();
	}
}

function create_domain()
{
	$globals = ffGlobals::getInstance("mod_security");
	$dbmain = mod_security_get_main_db();
	
	$sSQL = "
			INSERT INTO
				" . CM_TABLE_PREFIX . "mod_security_domains
						(
							nome
							, company_name
							, creation_date
							, expiration_date
							, time_zone
							, status
							, ID_packages
							, db_host
							, db_name
							, db_user
							, db_pass
						)
					VALUES
						(
							" . $dbmain->toSql($_REQUEST["Step1_nome"]) . "
							, " . $dbmain->toSql($_REQUEST["Step1_company_name"]) . "
							, " . $dbmain->toSql(new ffData(date("d/m/Y H:i:s"), "DateTime", "ITA")) . "
							, " . $dbmain->toSql($_REQUEST["Step1_expiration_date"]) . "
							, " . $dbmain->toSql($_REQUEST["Step1_time_zone"]) . "
							, " . $dbmain->toSql($_REQUEST["Step1_status"]) . "
							, " . $dbmain->toSql($_REQUEST["Step1_ID_packages"]) . "
							, " . $dbmain->toSql($_REQUEST["Step2_db_host"]) . "
							, " . $dbmain->toSql($_REQUEST["Step2_db_name"]) . "
							, " . $dbmain->toSql($_REQUEST["Step2_db_user"]) . "
							, " . $dbmain->toSql($_REQUEST["Step2_db_pass"]) . "
						)
		";
	$dbmain->execute($sSQL);
	$ID = $dbmain->getInsertID();
	if ($ID->getValue())
	{
		if (is_file(FF_DISK_PATH . "/dbstruct.sql"))
		{
			$dbstruct = file_get_contents(FF_DISK_PATH . "/dbstruct.sql");
			$subqueries = explode(";\n", $dbstruct);

			$db = mod_security_get_db_by_domain($ID->getValue());
	//		ffErrorHandler::raise("asd", E_USER_ERROR, null, get_defined_vars());
			foreach ($subqueries as $key => $value)
			{
				if (strlen(trim($value)))
					$db->execute($value);
			}
		}
		
		cm::getInstance()->modules["security"]["events"]->doEvent("domain_insert", array($ID));
		
		ffRedirect("report?" . $globals->transit_params . "&keys[ID]=" . $ID->getValue());
	}
}

