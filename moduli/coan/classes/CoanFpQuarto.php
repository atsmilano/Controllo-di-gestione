<?php

class CoanFpQuarto extends Entity
{

    protected static $tablename = "coan_fp_quarto";
    protected static $relations = array(
        "relation" => array("target_class" => "CoanConto",
            "keys" => array(
                "ID_fp_quarto" => "ID",
            ),
            "allow_delete" => false,
            "propagate_delete" => false,
        )
    );

}
