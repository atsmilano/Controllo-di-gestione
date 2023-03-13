<?php

class CoanFpPrimo extends Entity
{

    protected static $tablename = "coan_fp_primo";
    protected static $relations = array(
        "relation" => array("target_class" => "CoanFpSecondo",
            "keys" => array(
                "ID_fp_primo" => "ID",
            ),
            "allow_delete" => true,
            "propagate_delete" => true,
        )
    );

}
