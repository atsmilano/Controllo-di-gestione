<?php
$options = mod_security_get_settings(cm::getInstance()->path_info);

$db = ffDBConnection::factory("db_sql_mysqli", "main");

$strAddSearchFields = "";
$strKeyDescrizione = "";
if(strlen(MOD_SEC_USER_FIRSTNAME)) {
	if(strlen($strKeyDescrizione))
		$strKeyDescrizione .= ",";

	$strKeyDescrizione .= $db->toSql(MOD_SEC_USER_FIRSTNAME);
	
	$strAddSearchFields .= ", (SELECT 
									" . $options["table_dett_name"] . ".value
								FROM
									" . $options["table_dett_name"] . "
								WHERE
									" . $options["table_dett_name"] . ".ID_users = " . $options["table_name"] . ".ID
									AND " . $options["table_dett_name"] . ".field = " . $db->toSql(MOD_SEC_USER_FIRSTNAME) . "
							) AS firstname
				";
}
if(strlen(MOD_SEC_USER_LASTNAME)) {
	if(strlen($strKeyDescrizione))
		$strKeyDescrizione .= ",";

	$strKeyDescrizione .= $db->toSql(MOD_SEC_USER_LASTNAME);
	
	$strAddSearchFields .= ", (SELECT 
									" . $options["table_dett_name"] . ".value
								FROM
									" . $options["table_dett_name"] . "
								WHERE
									" . $options["table_dett_name"] . ".ID_users = " . $options["table_name"] . ".ID
									AND " . $options["table_dett_name"] . ".field = " . $db->toSql(MOD_SEC_USER_LASTNAME) . "
							) AS lastname
				";
}

if(strlen(MOD_SEC_USER_COMPANY)) {
	if(strlen($strKeyDescrizione))
		$strKeyDescrizione .= ",";

	$strKeyDescrizione .= $db->toSql(MOD_SEC_USER_COMPANY);
	
	$strAddSearchFields .= ", (SELECT 
									" . $options["table_dett_name"] . ".value
								FROM
									" . $options["table_dett_name"] . "
								WHERE
									" . $options["table_dett_name"] . ".ID_users = " . $options["table_name"] . ".ID
									AND " . $options["table_dett_name"] . ".field = " . $db->toSql(MOD_SEC_USER_COMPANY) . "
							) AS company
				";
}

if (strlen($strKeyDescrizione))
{
	$addSqlDescrizione = "			
			, (SELECT 
					GROUP_CONCAT(IF(`" . $options["table_dett_name"] . "`.value = " . $db->toSql(MOD_SEC_USER_COMPANY) . "
									, CONCAT(' / ', `" . $options["table_dett_name"] . "`.value)
									, `" . $options["table_dett_name"] . "`.value
								) 
						SEPARATOR ' '
					)
				FROM
					`" . $options["table_dett_name"] . "`
				WHERE
					`" . $options["table_dett_name"] . "`.ID_users = `" . $options["table_name"] . "`.ID
					AND `" . $options["table_dett_name"] . "`.field IN (" . $strKeyDescrizione . ")
			) AS descrizione
		";
}

/*if (MOD_SEC_CRYPT)
{
	$strEmail = ", AES_DECRYPT(`" . $options["table_name"] . "`.`email`, '[CRYPT_KU]') AS `email_clear`";
}
else
{
	$strEmail = ", `email` AS `email_clear`";
}*/

//			$strEmail
$tmp = new ffDBQuery(
	"mod_sec_users_index"
	, $db
	, "SELECT 
			`" . $options["table_name"] . "`.*
			$addSqlDescrizione
			[ADDFIELDS]
			$strAddSearchFields
			[PROFILES]
		FROM 
			`" . $options["table_name"] . "`
		WHERE
			1
			[AND] [WHERE] 
		[HAVING] 
		[ORDER]
	"
);

$tmp->addEvent("on_getSql", function (ffDBQuery $source, $sql, $context, $last_event_res) {
	$cm = cm::getInstance();
	$options = mod_security_get_settings(cm::getInstance()->path_info);
	
	if ($last_event_res)
		$sql = $last_event_res;
	
	$db = $context->db[0];
	
	$addSql = "";

	if (get_session("UserLevel") < 3 && !MOD_SECURITY_USERS_SHOW_LEVELS_ALL)
	{
		if (MOD_SECURITY_USERS_SHOW_LEVELS_ACL)
			$addSql .= "
										AND (`" . $options["table_name"] . "`.level IN (" . MOD_SECURITY_USERS_SHOW_LEVELS_ACL . ")
								";
		else if (MOD_SECURITY_USERS_SHOW_SAME_LEVEL)
			$addSql .= "
										AND (`" . $options["table_name"] . "`.level <= " . $db->toSql(get_session("UserLevel")) . "
				";
		else
			$addSql .= "
										AND (`" . $options["table_name"] . "`.level < " . $db->toSql(get_session("UserLevel")) . "
				";
		$addSql .= "
									OR `" . $options["table_name"] . "`.ID = " . $db->toSql(get_session("UserNID"), "Number") . "
								)
			";
	}

	$res = $cm->modules["security"]["events"]->doEvent("get_domain");
	$rc_domain = end($res);
	if ($rc_domain)
		$addSql .= " AND `" . $options["table_name"] . "`.ID_domains = " . $db->toSql($rc_domain, "Number") . "";
	else if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_MULTIDOMAIN_EXTERNAL_DB)
		$addSql .= " AND `" . $options["table_name"] . "`.ID_domains = " . $db->toSql(mod_security_get_domain(), "Number") . "";

	if (MOD_SEC_EXCLUDE_SQL)
		$addSql .= " AND `" . $options["table_name"] . "`.ID " . MOD_SEC_EXCLUDE_SQL;
	
	$sql = str_replace("[AND]", $addSql . " [AND]", $sql);
	
	if (MOD_SEC_PROFILING && get_session("UserLevel") >= MOD_SEC_SHOW_PROFILE_LEVEL && MOD_SEC_PROFILING_MULTI)
	{
		$profSql = ", (SELECT 
							GROUP_CONCAT(`cm_mod_security_profiles`.`nome` SEPARATOR ', ')
						FROM 
							`cm_mod_security_rel_profiles_users`
							INNER JOIN `cm_mod_security_profiles` ON
								`cm_mod_security_rel_profiles_users`.`ID_profile` = `cm_mod_security_profiles`.`ID`
						WHERE 
							`cm_mod_security_rel_profiles_users`.`ID_user` = `" . $options["table_name"] . "`.`ID`
							AND `cm_mod_security_rel_profiles_users`.`enabled` = '1'
					) AS `profiles_list`";
		$sql = str_replace("[PROFILES]", $profSql, $sql);
	}
	else
		$sql = str_replace("[PROFILES]", "", $sql);
	
	$addFieldsSql = "";
	
	/*if (isset($cm->modules["security"]["fields"]))
	{
		$strKeyTelCell = "";
		$key_tel = "tel";
		$key_cell = "cell";
		if(strlen($key_tel) && isset($cm->modules["security"]["fields"][$key_tel])) {
			if(strlen($strKeyTelCell))
				$strKeyTelCell .= ",";

			$strKeyTelCell .= $db->toSql($key_tel);
		}
		if(strlen($key_cell) && isset($cm->modules["security"]["fields"][$key_cell])) {
			if(strlen($strKeyTelCell))
				$strKeyTelCell .= ",";

			$strKeyTelCell .= $db->toSql($key_cell);
		}
		if (strlen($strKeyTelCell))
			$addFieldsSql .= ", (SELECT 
												GROUP_CONCAT(`" . $options["table_dett_name"] . "`.value SEPARATOR ' / ')
											FROM
												`" . $options["table_dett_name"] . "`
											WHERE
												`" . $options["table_dett_name"] . "`.ID_users = " . $options["table_name"] . ".ID
												AND `" . $options["table_dett_name"] . "`.field IN (" . $strKeyTelCell . ")
										) AS telcell
						";
	}*/
	
	if (isset($cm->modules["security"]["fields"]) && count($cm->modules["security"]["fields"]))
	{
		foreach ($cm->modules["security"]["fields"] as $key => $value)
		{
			if (!ffIsset($value, "show_into_grid") || strtolower($value["show_into_grid"]) !== "true")
				continue;

			$addFieldsSql .= ", (SELECT 
										`" . $options["table_dett_name"] . "`.`value`
									FROM
										`" . $options["table_dett_name"] . "`
									WHERE
										`" . $options["table_dett_name"] . "`.`ID_users` = `" . $options["table_name"] . "`.`ID`
										AND `" . $options["table_dett_name"] . "`.`field` = " . $db->toSql($key) . "
								) AS `" . $key . "`
				";
		}
	}
	
	$sql = str_replace("[ADDFIELDS]", $addFieldsSql, $sql);
	
	//$sql = str_replace("[CRYPT_KU]", ffGlobals::getInstance("__mod_sec_crypt__")->_crypt_Ku_, $sql);
	return $sql;
});