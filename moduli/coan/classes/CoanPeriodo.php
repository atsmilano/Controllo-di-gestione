<?php

class CoanPeriodo extends Entity
{

    protected static $tablename = "coan_periodo";
    protected static $relations = array(
        "relation" => array("target_class" => "CoanConsuntivoPeriodo",
            "keys" => array(
                "ID_periodo_coan" => "ID",
            ),
            "allow_delete" => false,
            "propagate_delete" => false,
        )
    );

}
