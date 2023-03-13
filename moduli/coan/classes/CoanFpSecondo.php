<?php

class CoanFpSecondo extends Entity
{

    protected static $tablename = "coan_fp_secondo";
    protected static $relations = array(
        "relation" => array("target_class" => "CoanFpTerzo",
            "keys" => array(
                "ID_fp_secondo" => "ID",
            ),
            "allow_delete" => true,
            "propagate_delete" => true,
        )
    );

}
