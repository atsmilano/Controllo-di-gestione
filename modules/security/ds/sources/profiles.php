<?php
$tmp = new ffDBQuery(
	"mod_sec_profiles_index"
	, ffDBConnection::factory("db_sql_mysqli", "main")
	, "SELECT
				`cm_mod_security_profiles`.*
				[addSQLDomainField]
			FROM
				`cm_mod_security_profiles`
				[addSQLDomainJoin]
			WHERE
				(`cm_mod_security_profiles`.`acl` = '' OR [UserLevel] IN(`cm_mod_security_profiles`.`acl`))
				[addSQLDomainWhere]
				[AND] [WHERE]
			[HAVING]
			ORDER BY 
				`cm_mod_security_profiles`.`order` 
				[COLON] [ORDER]
		"
);
$tmp->addEvent("on_getSql", "mod_sec_sources_profiles_on_getSql");

$tmp2 = new ffDBQuery(
	"user_multi_profiles_edit"
	, ffDBConnection::factory("db_sql_mysqli", "main")
	, "SELECT
				cm_mod_security_rel_profiles_users.ID AS ID
				, cm_mod_security_profiles.ID AS ID_profile
				, cm_mod_security_rel_profiles_users.enabled AS enabled
				, cm_mod_security_profiles.`order` AS `order`
			FROM
				cm_mod_security_profiles
				LEFT JOIN cm_mod_security_rel_profiles_users ON
					cm_mod_security_rel_profiles_users.ID_profile = cm_mod_security_profiles.ID
					AND cm_mod_security_rel_profiles_users.ID_user = [ID_FATHER]
			WHERE
				cm_mod_security_profiles.enabled = '1' 
				AND (cm_mod_security_profiles.acl = '' OR [UserLevel] IN(cm_mod_security_profiles.acl))
			ORDER BY
				cm_mod_security_profiles.`order`
		"
);
$tmp2->addEvent("on_getSql", "mod_sec_sources_profiles_on_getSql");

$tmp3 = new ffDBQuery(
	"user_multi_profiles_insert"
	, ffDBConnection::factory("db_sql_mysqli", "main")
	, "SELECT
				cm_mod_security_profiles.ID AS ID_profile
				, cm_mod_security_profiles.`order`
			FROM
				cm_mod_security_profiles
			WHERE
				cm_mod_security_profiles.enabled = '1' 
				AND (cm_mod_security_profiles.acl = '' OR [UserLevel] IN(cm_mod_security_profiles.acl))
			ORDER BY
				cm_mod_security_profiles.`order`
		"
);
$tmp3->addEvent("on_getSql", "mod_sec_sources_profiles_on_getSql");

function mod_sec_sources_profiles_on_getSql(ffDBQuery $source, $sql, $context, $last_event_res)
{
	if ($last_event_res)
		$sql = $last_event_res;
	
	$db = $oComponent->db[0];

	mod_sec_profiling_update_profiles();

	if (MOD_SEC_MULTIDOMAIN && !MOD_SEC_MULTIDOMAIN_EXTERNAL_DB && mod_security_get_domain())
	{
		$addSQLDomainField = ", `cm_mod_security_domains`.`nome` AS `domainname`";
		$addSQLDomainJoin = "							LEFT JOIN `cm_mod_security_domains` ON
									`cm_mod_security_domains`.`ID` = `cm_mod_security_profiles`.`ID_domains`
							";
		$addSQLDomainWhere = " AND (`cm_mod_security_profiles`.`ID_domains` = 0 OR `cm_mod_security_profiles`.`ID_domains` = " . $db->toSql(mod_security_get_domain()) . ")";
	}
	
	$sql = str_replace("[addSQLDomainField]", $addSQLDomainField, $sql);
	$sql = str_replace("[addSQLDomainJoin]", $addSQLDomainJoin, $sql);
	$sql = str_replace("[addSQLDomainWhere]", $addSQLDomainWhere, $sql);
	$sql = str_replace("[UserLevel]", get_session("UserLevel"), $sql);
	
	return $sql;
}
