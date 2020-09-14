<?php
$tmp = new ffDBQuery(
	"mod_sec_user_profile"
	, ffDBConnection::factory("db_sql_mysqli", "main")
	, "SELECT
				`cm_mod_security_profiles`.`ID`
				, `cm_mod_security_profiles`.`nome`
			FROM 
				`cm_mod_security_profiles`
			WHERE 
				`cm_mod_security_profiles`.`enabled` = '1' 
				AND ( `cm_mod_security_profiles`.`acl` = '' OR [UserLevel] IN(`cm_mod_security_profiles`.`acl`) )
			ORDER BY
				`cm_mod_security_profiles`.`order`
		"
);

$tmp->addEvent("on_getSql", function (ffDBQuery $source, $sql, $context, $last_event_res) {
	if ($last_event_res)
		$sql = $last_event_res;
	
	$sql = str_replace("[UserLevel]", get_session("UserLevel"), $sql);
	
	return $sql;
});
