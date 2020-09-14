<?php

if (isset($cm->modules["security"]["auth_bypath"]) && count($cm->modules["security"]["auth_bypath"]))
{
	foreach ($cm->modules["security"]["auth_bypath"] as $key => $value)
	{
		if  (strpos($cm->path_info, $key) === 0)
		{
			if ($value == "noauth")
			{
				reset ($cm->modules["security"]["auth_bypath"]);
				return;
			}
		}
	}
	reset ($cm->modules["security"]["auth_bypath"]);
}

mod_security_check_session();

if (isset($cm->processed_rule["rule"]->options->minlevel))
{
	if (get_session("UserLevel") < (string)$cm->processed_rule["rule"]->options->minlevel) {
        mod_security_destroy_session(true);
    }
}
