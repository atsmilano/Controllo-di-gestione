<?php
namespace FF\Libs\OAuth2\Apps\Standard;

class APIManager {
	static private $singleton = null;
	
	private $options = array();
	
	private $backend_sessions = null;
	private $backend_accounts = null;
	private $accounts = array();
	
	private $providers = array();
	
	private $last_provider = null;
	
	public static function getInstance($options = null)
	{
		if (self::$singleton === null)
			self::$singleton = new APIManager($options);

		return self::$singleton;
	}
	
	private function __construct($options = null) {
		if (is_array($options))
			$this->options = array_merge($this->options, $options);
		
		if (!ffIsset($this->options, "backend_accounts"))
			throw new \BadFunctionCallException("'backend_accounts' param is required");
		if (!in_array($this->options["backend_accounts"], array("ffDb_Sql", "config")))
			throw new \InvalidArgumentException("'backend_accounts' must be ffCache or ffDb_Sql");
		
		if (!ffIsset($this->options, "backend_sessions"))
			throw new \BadFunctionCallException("'backend_sessions' param is required");
		if (!in_array($this->options["backend_sessions"], array("ffDb_Sql", "ffCache")))
			throw new \InvalidArgumentException("'backend_sessions' must be ffCache or ffDb_Sql");
		
		switch ($this->options["backend_sessions"])
		{
			case "ffCache":
				if (!ffIsset($this->options, "ref_sessions")) {
					if (!CM_ENABLE_MEM_CACHING)
						throw new \BadFunctionCallException("you must pass a ffCache object in 'ref_sessions' or enable CM_ENABLE_MEM_CACHING");
					else
						$this->backend_sessions = \ffCache::getInstance(CM_CACHE_ADAPTER);
				} elseif (!is_object($this->options["ref_sessions"]) || !is_a($this->options["ref_sessions"], "ffCache")) {
					throw new \InvalidArgumentException("you must pass a ffCache object in 'ref_sessions' or enable CM_ENABLE_MEM_CACHING");
				} else {
					$this->backend_sessions = $this->options["ref_sessions"];
				}
				break;
			
			case "ffDb_Sql":
				if ($this->options["ref_sessions"] === null) {
					$this->backend_sessions = \ffDB_Sql::factory();
				} else {
					$this->backend_sessions = $this->options["ref_sessions"];
				}
				break;
		}
		
		switch ($this->options["backend_accounts"])
		{
			case "ffDb_Sql":
				if ($this->options["ref_accounts"] === null) {
					$this->backend_accounts = \ffDB_Sql::factory();
				} elseif (!is_object($this->options["ref_accounts"]) || !is_a($this->options["ref_accounts"], "ffDb_Sql")) {
					throw new \InvalidArgumentException("with backend_accounts set to 'ffDb_Sql, you must pass a ffDb_Sql object in 'ref_accounts'");
				} else {
					$this->backend_accounts = $this->options["ref_accounts"];
				}
				break;
				
			case "config":
				$this->backend_accounts = "config";
				if (!ffIsset($this->options, "accounts"))
					throw new \BadFunctionCallException("with backend_accounts set to 'config', you must pass an accounts array");
				elseif (!is_array($this->options["accounts"]) || !count($this->options["accounts"]))
					throw new \InvalidArgumentException("with backend_accounts set to 'config', you must pass an accounts array");
				else
					$this->accounts = $this->options["accounts"];
				break;
		}
	}
	
	function getProvider($type, $subtype = null, $username = null) {
		
		// before try to get from cache
		foreach ($this->providers as $key => $val) {
			if ($val["type"] === $type && $val["subtype"] === $subtype) {
				if ($username !== null && $val["username"] !== $username)
					continue;
				return $val;
			}
		}
		
		// get provider data
		$found_account = false;
		switch ($this->options["backend_accounts"]) {
			case "ffDb_Sql":
				$sSQL = "SELECT 
								`ff_oa2_accounts`.*
							FROM 
								`ff_oa2_accounts`
							WHERE 
								`ff_oa2_accounts`.`type` = " . $this->backend_accounts->toSql($type) . "
								AND `ff_oa2_accounts`.`subtype` = " . $this->backend_accounts->toSql($subtype) . "
					";
				if (strlen($username))
					$sSQL .= " AND `ff_oa2_accounts`.`username` = " . $this->backend_accounts->toSql($username) . " ";
				$this->backend_accounts->query($sSQL);
				if ($this->backend_accounts->nextRecord()) {
					$found_account = true;
					$service_base_url = $this->backend_accounts->getField("base_url", "Text", true);
					$service_user = $this->backend_accounts->getField("username", "Text", true);
					$service_password = $this->backend_accounts->getField("password", "Text", true);
					$service_apikey = $this->backend_accounts->getField("apikey", "Text", true);
				}
				break;
				
			case "config":
				foreach ($this->accounts as $key => $val) {
					if ($val["type"] === $type && $val["subtype"] === $subtype) {
						if ($username !== null && $val["username"] !== $username)
							continue;
						
						$found_account = true;
						$service_base_url = $val["base_url"];
						$service_user = $val["username"];
						$service_password = $val["password"];
						$service_apikey = $val["apikey"];
					}
				}
				break;
		}
		
		if (!$found_account)
			throw new \InvalidArgumentException("Unable to find account data");
		
		// get provider instance
		$provider = new APIProvider(array(
			"base_url"	=> $service_base_url,
			"user"		=> $service_user,
			"password"	=> $service_password,
			"apikey"	=> strlen($service_apikey) ? $service_apikey : null
		));
		
		// caching
		$this->providers[] = array(
			"type" => $type
			, "subtype" => $subtype
			, "username" => $service_user
			, "obj" => $provider
		);
		
		return end($this->providers);
	}
	
	function request($options) {
		$type = $options["type"];
		$subtype = $options["subtype"];
		$username = $options["username"];
		
		$url = $options["url"];
		$fields = $options["fields"];
		
		$provider = $this->getProvider($type, $subtype, $username);
		$this->last_provider = $provider["obj"];
		
		// try to get existent token
		$token = null;
		switch ($this->options["backend_sessions"]) {
			case "ffCache":
				$ffcache_success = false;
				$sessions = $this->backend_sessions->get("__FFOAUTH2STDAPP_SESSIONS__", $ffcache_success);
				if ($ffcache_success)
				{
					$sessions = unserialize($sessions);
					if (is_array($sessions) && count($sessions)) foreach ($sessions as $key => $val) {
						if ($val["type"] === $type && $val["subtype"] === $subtype && $val["username"] === $provider["username"]) {
							$token = $val["token"];
						}
					}
				}
				break;
			case "ffDb_Sql":
				$sSQL = "SELECT 
								`ff_oa2_tokens`.`token`
							FROM 
								`ff_oa2_tokens`
							WHERE 
								`ff_oa2_tokens`.`type` = " . $this->backend_sessions->toSql($type) . "
								AND `ff_oa2_tokens`.`subtype` = " . $this->backend_sessions->toSql($subtype) . "
								AND `ff_oa2_tokens`.`username` = " . $this->backend_sessions->toSql($provider["username"]) . "
					";
				$this->backend_sessions->query($sSQL);
				if ($this->backend_sessions->nextRecord()) {
					$token = $this->backend_sessions->getField("token", "Text", true);
				}
				break;
		}
		
		$token = strlen($token) ? $token : null;
		$ret = $provider["obj"]->request($url, $fields, $token);
		
		$token = $provider["obj"]->get_last_token();
		if ($token !== null)
			$token = $token->get_token_id();
		
		// store token
		switch ($this->options["backend_sessions"]) {
			case "ffCache":
				$found = false;
				$ffcache_success = false;
				$sessions = $this->backend_sessions->get("__FFOAUTH2STDAPP_SESSIONS__", $ffcache_success);
				if ($ffcache_success)
				{
					$sessions = unserialize($sessions);
					if (is_array($sessions) && count($sessions)) foreach ($sessions as $key => $val) {
						if ($val["type"] === $type && $val["subtype"] === $subtype && $val["username"] === $provider["username"]) {
							$found = true;
							$sessions[$key]["token"] = $token;
						}
					}
				} else
					$sessions = array();
				
				if (!$found)
					$sessions[] = array(
						"type" => $type
						, "subtype" => $subtype
						, "username" => $provider["username"]
						, "token" => $token
					);
				
				$this->backend_sessions->set("__FFOAUTH2STDAPP_SESSIONS__", null, serialize($sessions));
				break;
			case "ffDb_Sql":
				$sSQL = "SELECT 
								`ff_oa2_tokens`.`token`
							FROM 
								`ff_oa2_tokens`
							WHERE 
								`ff_oa2_tokens`.`type` = " . $this->backend_sessions->toSql($type) . "
								AND `ff_oa2_tokens`.`subtype` = " . $this->backend_sessions->toSql($subtype) . "
								AND `ff_oa2_tokens`.`username` = " . $this->backend_sessions->toSql($provider["username"]) . "
					";
				$this->backend_sessions->query($sSQL);
				if ($this->backend_sessions->nextRecord()) {
					$sSQL = "UPDATE `ff_oa2_tokens` SET 
										`token` = " . $this->backend_sessions->toSql($token) . " 
									WHERE 
										`type` = " . $this->backend_sessions->toSql($type) . "
										AND `subtype` = " . $this->backend_sessions->toSql($subtype) . "
										AND `username` = " . $this->backend_sessions->toSql($provider["username"]) . "
							";
					$this->backend_sessions->execute($sSQL);
				} else {
					$sSQL = "INSERT INTO `ff_oa2_tokens` (`type`, `subtype`, `username`, `token`) VALUES (
									" . $this->backend_sessions->toSql($type) . "
									, " . $this->backend_sessions->toSql($subtype) . "
									, " . $this->backend_sessions->toSql($provider["username"]) . "
									, " . $this->backend_sessions->toSql($token) . "
								)
						";
					$this->backend_sessions->execute($sSQL);
				}
				break;
		}
		
		return $ret;
	}
	
	function get_last_provider() {
		return $this->last_provider;
	}
	
	function get_exceptions() {
		return $this->get_last_provider()->exceptions;
	}
}
