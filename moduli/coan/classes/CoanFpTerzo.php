<?php

class CoanFpTerzo extends Entity
{

    protected static $tablename = "coan_fp_terzo";
    protected static $relations = array(
        "relation" => array("target_class" => "CoanFpQuarto",
            "keys" => array(
                "ID_fp_terzo" => "ID",
            ),
            "allow_delete" => true,
            "propagate_delete" => true,
        )
    );

}
