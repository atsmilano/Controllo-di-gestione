<?php
/**
 * SQL database access through ffDB_sql
 * 
 * @package FormsFramework
 * @subpackage OAUTH2
 * @author Samuele Diella <samuele.diella@gmail.com>
 * @copyright Copyright &copy; 2000-2015, Samuele Diella
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link http://www.formsphpframework.com
 */

namespace OAuth2\Storage;

class FF implements
    AuthorizationCodeInterface,
    AccessTokenInterface,
    ClientCredentialsInterface,
	ScopeInterface,
    RefreshTokenInterface,
	UserCredentialsInterface
{
	/**
	 *
	 * @var ffDB_sql
	 */
	protected $db = null;
	protected $config = null;
	protected $userdata = array();
	protected $clientdata = null;


	public function __construct(\ffDB_Sql $connection = null, $config = array())
    {
		if ($connection === null)
			$this->db = \ffDB_Sql::factory();
		else
			$this->db = $connection;
	
        $this->config = array_merge(array(
            'client_table' => 'oauth_clients',
            'access_token_table' => 'oauth_access_tokens',
            'refresh_token_table' => 'oauth_refresh_tokens',
            'code_table' => 'oauth_authorization_codes',
            'user_table' => 'oauth_users',
            'jwt_table'  => 'oauth_jwt',
            'jti_table'  => 'oauth_jti',
            'scope_table'  => 'oauth_scopes',
            'public_key_table'  => 'oauth_public_keys',
            'grant_types_table'  => 'oauth_grant_types',
            'user_scope_from_client'  => true,
        ), $config);
	}
	
    public function getClientDetails($client_id)
	{
		if ($this->clientdata !== null)
			return $this->clientdata;
			
		$this->db->query("SELECT 
								`" . $this->config['client_table'] . "`.* 
								, `" . $this->config['grant_types_table'] . "`.`grant_types` AS `rel_grant_types`
							FROM 
								`" . $this->config['client_table'] . "`
								INNER JOIN `" . $this->config['grant_types_table'] . "` ON
									`" . $this->config['client_table'] . "`.`ID_grant_types` = `" . $this->config['grant_types_table'] . "`.`ID`
							WHERE 
								`client_id` = " . $this->db->toSql($client_id));
		if ($this->db->nextRecord())
		{
			$ret = $this->db->record;
			if (strlen($ret["rel_grant_types"]))
				$ret["grant_types"] = $ret["rel_grant_types"];
			unset($ret["rel_grant_types"]);
			unset($ret["ID_grant_types"]);
			$this->clientdata = $ret;
			return $ret;
		}
		else
			return false;
	}

    /**
     * Get the scope associated with this client
     *
     * @return
     * STRING the space-delineated scope list for the specified client_id
     */
    public function getClientScope($client_id)
	{
        if (!$clientDetails = $this->getClientDetails($client_id))
		{
            return false;
        }

        if (isset($clientDetails['scope']))
		{
            return $clientDetails['scope'];
        }

        return null;
	}

    /**
     * Check restricted grant types of corresponding client identifier.
     *
     * If you want to restrict clients to certain grant types, override this
     * function.
     *
     * @param $client_id
     * Client identifier to be check with.
     * @param $grant_type
     * Grant type to be check with
     *
     * @return
     * TRUE if the grant type is supported by this client identifier, and
     * FALSE if it isn't.
     *
     * @ingroup oauth2_section_4
     */
    public function checkRestrictedGrantType($client_id, $grant_type)
	{
        $details = $this->getClientDetails($client_id);
        if (isset($details['grant_types']))
		{
            $grant_types = explode(' ', $details['grant_types']);

            return in_array($grant_type, (array) $grant_types);
        }

        // if grant_types are not defined, then none are restricted
        return true;
	}
	
    /**
     * Look up the supplied oauth_token from storage.
     *
     * We need to retrieve access token data as we create and verify tokens.
     *
     * @param $oauth_token
     * oauth_token to be check with.
     *
     * @return
     * An associative array as below, and return NULL if the supplied oauth_token
     * is invalid:
     * - expires: Stored expiration in unix timestamp.
     * - client_id: (optional) Stored client identifier.
     * - user_id: (optional) Stored user identifier.
     * - scope: (optional) Stored scope values in space-separated string.
     * - id_token: (optional) Stored id_token (if "use_openid_connect" is true).
     *
     * @ingroup oauth2_section_7
     */
    public function getAccessToken($oauth_token)
	{
		$this->db->query("SELECT * FROM `" . $this->config['access_token_table'] . "` WHERE `access_token` = " . $this->db->toSql($oauth_token));
		if ($this->db->nextRecord())
		{
			$record = $this->db->record;
			$record['expires'] = strtotime($record['expires']);
			return $record;
		}
		else
			return null;
	}

    /**
     * Store the supplied access token values to storage.
     *
     * We need to store access token data as we create and verify tokens.
     *
     * @param $oauth_token    oauth_token to be stored.
     * @param $client_id      client identifier to be stored.
     * @param $user_id        user identifier to be stored.
     * @param int    $expires expiration to be stored as a Unix timestamp.
     * @param string $scope   OPTIONAL Scopes to be stored in space-separated string.
     *
     * @ingroup oauth2_section_4
     */
    public function setAccessToken($oauth_token, $client_id, $user_id, $expires, $scope = null)
	{
        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);

        // if it exists, update it.
        if ($this->getAccessToken($oauth_token)) 
		{
			$sSQL = "UPDATE `" . $this->config['access_token_table'] . "`
						SET
							`client_id` = " . $this->db->toSql($client_id) . "
							, `expires` = " . $this->db->toSql(new \ffData($expires, "DateTime", "ISO9075")) . "
							, `user_id` = " . $this->db->toSql($user_id) . "
							, `scope` = " . $this->db->toSql($scope) . "
						WHERE
							`access_token` = " . $this->db->toSql($oauth_token) . "
				";
        } 
		else 
		{
			$sSQL = "INSERT INTO `" . $this->config['access_token_table'] . "`
							(
								access_token
								, client_id
								, expires
								, user_id
								, scope
							) VALUES (
								" . $this->db->toSql($oauth_token) . "
								, " . $this->db->toSql($client_id) . "
								, " . $this->db->toSql(new \ffData($expires, "DateTime", "ISO9075")) . "
								, " . $this->db->toSql($user_id) . "
								, " . $this->db->toSql($scope) . "
							)
				";
        }
		
		//$this->db->execute($sSQL);
		//\ffErrorHandler::raise("TEST", E_USER_ERROR, $this, get_defined_vars());
		return $this->db->execute($sSQL);
	}
	
    /**
     * Make sure that the client credentials is valid.
     *
     * @param $client_id
     * Client identifier to be check with.
     * @param $client_secret
     * (optional) If a secret is required, check that they've given the right one.
     *
     * @return
     * TRUE if the client credentials are valid, and MUST return FALSE if it isn't.
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-3.1
     *
     * @ingroup oauth2_section_3
     */
    public function checkClientCredentials($client_id, $client_secret = null)
	{
		$this->db->query("SELECT * FROM `" . $this->config['client_table'] . "` WHERE `client_id` = " . $this->db->toSql($client_id));
		if ($this->db->nextRecord())
		{
			$tmp = $this->getClientDetails($client_id);
			return $tmp["client_secret"] == $client_secret;
		}
		else
			return false;
	}

    /**
     * Determine if the client is a "public" client, and therefore
     * does not require passing credentials for certain grant types
     *
     * @param $client_id
     * Client identifier to be check with.
     *
     * @return
     * TRUE if the client is public, and FALSE if it isn't.
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-2.3
     * @see https://github.com/bshaffer/oauth2-server-php/issues/257
     *
     * @ingroup oauth2_section_2
     */
    public function isPublicClient($client_id)
	{
		$this->db->query("SELECT * FROM `" . $this->config['client_table'] . "` WHERE `client_id` = " . $this->db->toSql($client_id));
		if ($this->db->nextRecord())
		{
			return strlen($this->db->getField("client_secret", "Text", true)) === 0;
		}
		else
			return false;
	}

	/* OAuth2\Storage\AuthorizationCodeInterface */
    public function getAuthorizationCode($code)
    {
        $sSQL = "SELECT * from `" . $this->config['code_table'] . "` WHERE `authorization_code` = " . $this->db->toSql($code);
		if (isset($_REQUEST["sso_state"]))
			$sSQL .= " AND `sso_state` = " . $this->db->toSql ($_REQUEST["sso_state"]);
			
		$this->db->query($sSQL);
			
		if ($this->db->nextRecord())
		{
			$token = $this->db->record;
			$token["expires"] = strtotime($token["expires"]);
            // convert date string back to timestamp
			return $token;
		}
		else
			return false;
    }

    public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null, $id_token = null)
    {
        if (func_num_args() > 6) {
            // we are calling with an id token
            return call_user_func_array(array($this, 'setAuthorizationCodeWithIdToken'), func_get_args());
        }

        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);
		
		if (isset($_REQUEST["sso_state"]))
		{
			$addInsert = ", `sso_state` = " . $this->db->toSql($_REQUEST["sso_state"]);
			$addUpdateFields = ", `sso_state` ";
			$addUpdateVals = ", " . $this->db->toSql($_REQUEST["sso_state"]);
		}

        // if it exists, update it.
        if ($this->getAuthorizationCode($code)) {
			$sSQL = "UPDATE `" . $this->config['code_table'] . "` SET 
							client_id = " . $this->db->toSql($client_id) . "
							, user_id = " . $this->db->toSql($user_id) . "
							, redirect_uri = " . $this->db->toSql($redirect_uri) . "
							, expires = " . $this->db->toSql($expires) . "
							, scope = " . $this->db->toSql($scope) . "
							$addInsert
						WHERE 
							authorization_code = " . $this->db->toSql($code) . "
				";
        } else {
			$sSQL = "INSERT INTO `" . $this->config['code_table'] . "` (authorization_code, client_id, user_id, redirect_uri, expires, scope $addUpdateFields) VALUES (
							" . $this->db->toSql($code) . "
							, " . $this->db->toSql($client_id) . "
							, " . $this->db->toSql($user_id) . "
							, " . $this->db->toSql($redirect_uri) . "
							, " . $this->db->toSql($expires) . "
							, " . $this->db->toSql($scope) . "
							$addUpdateVals
						)";
        }

        return $this->db->execute($sSQL);
    }

    private function setAuthorizationCodeWithIdToken($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null, $id_token = null)
    {
        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);

        // if it exists, update it.
        if ($this->getAuthorizationCode($code)) {
			$sSQL = "UPDATE `" . $this->config['code_table'] . "` SET 
							client_id = " . $this->db->toSql($client_id) . "
							, user_id = " . $this->db->toSql($user_id) . "
							, redirect_uri = " . $this->db->toSql($redirect_uri) . "
							, expires = " . $this->db->toSql($expires) . "
							, scope = " . $this->db->toSql($scope) . "
							, id_token = " . $this->db->toSql($id_token) . "
						WHERE 
							authorization_code = " . $this->db->toSql($code) . "
				";
        } else {
			$sSQL = "INSERT INTO `" . $this->config['code_table'] . "` (authorization_code, client_id, user_id, redirect_uri, expires, scope, id_token) VALUES (
							" . $this->db->toSql($code) . "
							, " . $this->db->toSql($client_id) . "
							, " . $this->db->toSql($user_id) . "
							, " . $this->db->toSql($redirect_uri) . "
							, " . $this->db->toSql($expires) . "
							, " . $this->db->toSql($scope) . "
							, " . $this->db->toSql($id_token) . "
						)";
        }

        return $this->db->execute($sSQL);
    }

    public function expireAuthorizationCode($code)
    {
        $sSQL = "DELETE FROM `" . $this->config['code_table'] . "` WHERE `authorization_code` = " . $this->db->toSql($code);

        return $this->db->execute($sSQL);
    }

    /* ScopeInterface */
    public function scopeExists($scope)
    {
        $whereIn = "";
        $scope = explode(' ', $scope);
		foreach ($scope as $val)
			$whereIn .= $this->db->toSql($val) . ",";
        $whereIn = rtrim($whereIn, ",");
		
		$sSQL = "SELECT count(scope) as count FROM `" . $this->config['scope_table'] . "` WHERE scope IN (" . $whereIn . ")";
		$this->db->query($sSQL);
		if ($this->db->nextRecord())
		{
            return $this->db->record["count"] == count($scope);
		}
		
        return false;
    }

    public function getDefaultScope($client_id = null)
    {
		$sSQL = "SELECT `scope` FROM `" . $this->config['scope_table'] . "` WHERE `is_default` = 1 ";
		$this->db->query($sSQL);
		if ($this->db->numRows())
		{
			$defaultScope = array();
			do
			{
				$defaultScope[] = $this->db->getField("scope", "Text", true);
			} while ($this->db->nextRecord());
			
            return trim(implode(' ', $defaultScope));
        }

        return null;
    }
	
    /* OAuth2\Storage\RefreshTokenInterface */
    public function getRefreshToken($refresh_token)
    {
		$token = null;
		
        $sSQL = "SELECT * FROM `" . $this->config['refresh_token_table'] . "` WHERE `refresh_token` = " . $this->db->toSql($refresh_token);
		$this->db->query($sSQL);
		if ($this->db->nextRecord())
		{
            $token = $this->db->record;
            // convert expires to epoch time
            $token['expires'] = strtotime($token['expires']);
		}

        return $token;
    }

    public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = null)
    {
        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);

        $sSQL = "INSERT INTO `" . $this->config['refresh_token_table'] . "` (`refresh_token`, `client_id`, `user_id`, `expires`, `scope`) VALUES (
			" . $this->db->toSql($refresh_token) . "
			, " . $this->db->toSql($client_id) . "
			, " . $this->db->toSql($user_id) . "
			, " . $this->db->toSql($expires) . "
			, " . $this->db->toSql($scope) . "
		)";

        return $this->db->execute($sSQL);
    }

    public function unsetRefreshToken($refresh_token)
    {
        $sSQL = "DELETE FROM `" . $this->config['refresh_token_table'] . "` WHERE 
					`refresh_token` = " . $this->db->toSql($refresh_token);
		
        return $this->db->execute($sSQL);
    }
	
	public function checkUserCredentials($username, $password)
	{
		$ret = mod_sec_check_login($username, $password);
		
		$this->userdata[$username]["user_id"] = $ret["UserNID"];
		if ($this->config["user_scope_from_client"])
		$this->userdata[$username]["scope"] = $this->clientdata["scope"];
		
		return ($ret["valid"]);
	}

	public function getUserDetails($username)
	{
		return $this->userdata[$username];
	}

}
