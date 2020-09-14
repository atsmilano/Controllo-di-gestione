<?php
if (!mod_security_check_session(false))
	return;

$db = ffDB_Sql::factory();
$output = array();

if(isset($_REQUEST["accounts"])) {
  	if($_REQUEST["accounts"] > 0) {
  		$sSQL = "SELECT ID, nome FROM " . CM_TABLE_PREFIX . "mod_security_domains WHERE ID = " . $db->toSql($_REQUEST["accounts"]);
  		$db->query($sSQL);
  		if($db->nextRecord()) {
  			$ID_domain = $db->getField("ID", "Number", true);  		
			$domain_name = $db->getField("nome", "Text", true);  		
  		}
	} else {
		$ID_domain = 0;
		$domain_name = "";
	}
	
	set_session("Domain", $domain_name);
	set_session("DomainID", $ID_domain);
	if(MOD_SEC_GROUPS) {
		$user_permission = get_session("user_permission");
		$user_permission["domain"] = $domain_name;
		$user_permission["ID_domain"] = $ID_domain;
		
		set_session("user_permission", $user_permission);
	}
	
	$output = array(
		"id" => $ID_domain
		, "name" => $domain_name
	);
} elseif(isset($_REQUEST["lang"])) {
	if(MOD_SEC_GROUPS) {
		$user_permission = get_session("user_permission");
		if($user_permission["lang"][$_REQUEST["lang"]]) {
			$user_permission["lang"]["current"] = $user_permission["lang"][$_REQUEST["lang"]];
			$user_permission["lang"]["current"]["code"] = $_REQUEST["lang"];
		}
		
		set_session("user_permission", $user_permission);
	}
	
	$output = array(
		"id" => $user_permission["lang"]["current"]["ID"]
		, "name" => $user_permission["lang"]["current"]["code"]
	);
}
  
echo ffCommon_jsonenc($output, true);
exit;