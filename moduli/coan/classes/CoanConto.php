<?php

class CoanConto extends Entity
{

    protected static $tablename = "coan_conto";
    protected static $relations = array(
        "relation" => array("target_class" => "CoanConsuntivoPeriodo",
            "keys" => array(
                "ID_conto" => "ID",
            ),
            "allow_delete" => false,
            "propagate_delete" => false,
        )
    );

}
