<?php
$resources = array(
						  "mod-security-packages" => array(
												"tbl_name" => CM_TABLE_PREFIX . "mod_security_packages"
												//, "order_field" => "ordinamento"
											)
						  , "mod-security-profiles" => array(
												"tbl_name" => CM_TABLE_PREFIX . "mod_security_profiles"
												//, "order_field" => "ordinamento"
											)
					);

if (
		!isset($_REQUEST["resource"]) 
		|| !isset($_REQUEST["positions"]) 
		|| !is_array($_REQUEST["positions"]) 
		|| !count($_REQUEST["positions"]) 
		|| !isset($resources[$_REQUEST["resource"]])
	)
{
	$cm->responseCode(400);
}

$resource = $resources[$_REQUEST["resource"]];
$positions = $_REQUEST["positions"];
$order_field = ($resource["order_field"] ? $resource["order_field"] : "order");

switch ($_REQUEST["resource"])
{
	case "mod-security-profiles":
		if (MOD_SEC_MULTIDOMAIN && MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && !MOD_SEC_PROFILING_MAINDB)
			$db = mod_security_get_db_by_domain();
		else
			$db = mod_security_get_main_db();
		break;
	
	default:
		$db = ffDB_Sql::factory();
}

switch (strtoupper($_SERVER['REQUEST_METHOD']))
{
	case "POST":
		$sSQL = "UPDATE " . $resource["tbl_name"] . " SET `$order_field` = " . $db->toSql(new ffData(count($positions)));
		$db->execute($sSQL);

		foreach ($positions as $key => $value)
		{
			$sSQL = "UPDATE " . $resource["tbl_name"] . " SET `$order_field` = $key WHERE ID = " . $db->toSql($value);
			$db->execute($sSQL);
		}
		reset($positions);
		break;

	case "GET":
		$tpl = ffTemplate::factory($cm->oPage->getThemeDir() . "/contents/services");
		$tpl->load_file("resources.xml", "main");
		$tpl->set_var("nomerisorsa", $resource);

		$sSQL = "SELECT * FROM " . $resource["tbl_name"] . " ORDER BY `$order_field`";
		$db->query($sSQL);
		if ($db->nextRecord())
		{
			$i = 0;
			do
			{
				$tpl->set_var("id", ffCommon_specialchars($db->getField("ID", "Number")->getValue()));
				$tpl->set_var("name", ffCommon_specialchars($db->getField("name")->getValue()));
				$tpl->set_var("order", ffCommon_specialchars($i));
				$tpl->parse("SectResource", true);
				$i++;
			} while ($db->nextRecord());
		}

		header("Content-type: text/xml");
		echo $tpl->rpparse("main", false);
		$cm->responseCode(200);
		break;

	default:
		$cm->responseCode(400);
}

cm::jsonParse($cm->json_response);
exit;
